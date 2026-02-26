<?php
include 'includes/config.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// --- SIEM DATA LOGIC ---
$logFile = 'fulllogs.log';
$logs = [];
$stats = [
    'SSH' => 0, 'SQLi' => 0, 'XSS' => 0, 'RCE' => 0, 'SSTI' => 0, 'LFI' => 0, 'Other' => 0
];
$total_attacks = 0;

// Read Logs if available
if (file_exists($logFile)) {
    // Read all lines
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_reverse($lines); // Newest first by default
    
    foreach ($lines as $line) {
        $entry = [];
        $type = 'Other';
        $severity = 'INFO';
        
        // Parse "Date Host Process: Message" roughly
        if (preg_match('/^([A-Z][a-z]{2}\s+\d+\s\d+:\d+:\d+)\s(\S+)\s([^:]+):\s(.*)$/', $line, $matches)) {
            // SIMULATE DATES: Distribute logs over the last 30 days
            // We use a hash of the line content to make the random date persistent/deterministic per refresh
            $hash = crc32($line);
            $days_ago = $hash % 30; // 0 to 29 days ago
            // Make some very recent (last 24h) for "real-time" feel
            if ($days_ago < 2) $days_ago = 0; 
            
            $simulated_timestamp = time() - ($days_ago * 86400) - (rand(0, 86000));
            $entry['date'] = date('M d H:i:s', $simulated_timestamp);
            
            $entry['host'] = $matches[2];
            $entry['process'] = $matches[3];
            $entry['message'] = $matches[4];
            
            // Classification Logic
            if (stripos($line, 'authentication failure') !== false || stripos($line, 'check pass; user unknown') !== false) {
                $type = 'SSH';
                $severity = 'HIGH';
                $stats['SSH']++;
            } elseif (stripos($line, 'UNION SELECT') !== false || stripos($line, 'OR \'1\'=\'1') !== false) {
                $type = 'SQLi';
                $severity = 'CRITICAL';
                $stats['SQLi']++;
            } elseif (stripos($line, '<script>') !== false || stripos($line, 'onerror=') !== false) {
                $type = 'XSS';
                $severity = 'HIGH';
                $stats['XSS']++;
            } elseif (stripos($line, 'cmd=') !== false || stripos($line, 'uname -a') !== false) {
                $type = 'RCE';
                $severity = 'CRITICAL';
                $stats['RCE']++;
            } elseif (stripos($line, '{{7*7}}') !== false) {
                $type = 'SSTI';
                $severity = 'CRITICAL';
                $stats['SSTI']++;
            } elseif (stripos($line, '/etc/passwd') !== false || stripos($line, '..\\..\\') !== false) {
                $type = 'LFI';
                $severity = 'HIGH';
                $stats['LFI']++;
            } elseif (stripos($line, '[error]') !== false || stripos($line, 'failed password') !== false) {
                $type = 'Error';
                $severity = 'WARNING';
                $stats['Other']++; // Count as other or new category? Keep other for now.
            } elseif (stripos($line, 'session opened') !== false || stripos($line, 'session closed') !== false) {
                $type = 'System';
                $severity = 'LOW';
                $stats['Other']++;
            } else {
                $stats['Other']++;
            }
            
            if ($type !== 'Other') $total_attacks++;

            $entry['type'] = $type;
            $entry['severity'] = $severity;
            
            // Extract IP
            if (preg_match('/rhost=(\S+)/', $line, $ip_match)) {
                $entry['ip'] = $ip_match[1];
            } elseif (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $line, $ip_match)) {
                $entry['ip'] = $ip_match[1];
            } else {
                $entry['ip'] = 'UNKNOWN';
            }

            $logs[] = $entry;
        }
    }
}

// Log View Logic
$show_all = isset($_GET['all_logs']) && $_GET['all_logs'] == 'true';
$current_sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Sorting Logic (Applied to full dataset)
if ($current_sort == 'severity_asc' || $current_sort == 'severity_desc') {
    usort($logs, function($a, $b) use ($current_sort) {
        $levels = ['CRITICAL' => 3, 'HIGH' => 2, 'INFO' => 1];
        $a_val = $levels[$a['severity']] ?? 0;
        $b_val = $levels[$b['severity']] ?? 0;
        
        if ($a_val == $b_val) {
            // Secondary sort by Date (Newest first)
            $a_time = strtotime($a['date']);
            $b_time = strtotime($b['date']);
            return ($a_time < $b_time) ? 1 : -1;
        }
        
        if ($current_sort == 'severity_desc') {
            return ($a_val < $b_val) ? 1 : -1; // Descending (High to Low -> 3 before 1)
        } else {
            return ($a_val > $b_val) ? 1 : -1; // Ascending (Low to High -> 1 before 3)
        }
    });
}

// --- TIME FILTER LOGIC ---
$time_filter = isset($_GET['time_filter']) ? $_GET['time_filter'] : 'all';
$filtered_logs = [];

// 1. Newest time is now just "now" since we are simulating current dates
$newest_time = time();

// 2. Filter logs based on selection
if ($time_filter === '24h') {
    $cutoff = $newest_time - (24 * 3600);
    foreach ($logs as $log) {
        if (strtotime($log['date']) >= $cutoff) $filtered_logs[] = $log;
    }
} elseif ($time_filter === '7d') {
    $cutoff = $newest_time - (7 * 24 * 3600);
     foreach ($logs as $log) {
        if (strtotime($log['date']) >= $cutoff) $filtered_logs[] = $log;
    }
} else {
    // All time
    $filtered_logs = $logs;
}

// 3. SEPARATE LOGIC:
// We need two datasets:
// A. $logs_for_stats: Filtered ONLY by time (to populate charts/top attackers list)
// B. $logs_for_feed: Filtered by time AND IP (to show in the feed)

$logs_for_stats = !empty($filtered_logs) ? $filtered_logs : ($time_filter == 'all' ? $logs : []);

// 4. Apply Filters (IP and/or Category) (for feed only)
$ip_filter = isset($_GET['ip_filter']) ? $_GET['ip_filter'] : null;
$category_filter = isset($_GET['category_filter']) ? $_GET['category_filter'] : null;
$logs_for_feed = [];

// Iterate through stats-logs and apply feed filters
foreach ($logs_for_stats as $log) {
    // Check IP
    if ($ip_filter && $log['ip'] !== $ip_filter) continue;
    // Check Category
    if ($category_filter && $log['type'] !== $category_filter) continue;
    
    $logs_for_feed[] = $log;
}

// 5. Calculate Stats based on TIME-FILTERED logs (ignore IP filter so lists stay populated)
$stats = ['SSH' => 0, 'SQLi' => 0, 'XSS' => 0, 'RCE' => 0, 'SSTI' => 0, 'LFI' => 0, 'Other' => 0];
$total_attacks = 0;
$unique_ips = [];
$alerts_by_ip = [];

foreach ($logs_for_stats as $log) {
    // Count per type
    if (isset($stats[$log['type']])) {
        $stats[$log['type']]++;
    } else {
        $stats['Other']++;
    }
    if ($log['type'] !== 'Other') $total_attacks++;
    
    // Count unique IPs
    $unique_hosts[$log['ip']] = true;
    
    // Count alerts by IP
    if (!isset($alerts_by_ip[$log['ip']])) {
        $alerts_by_ip[$log['ip']] = 0;
    }
    $alerts_by_ip[$log['ip']]++;
}

// Sort alerts by IP desc
arsort($alerts_by_ip);

// Update logs variable for the feed display loop below
$display_logs = $logs_for_feed; 

if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    $max_logs = $show_all ? 5000 : 2000;
    $count = 0;
    foreach($display_logs as $log): 
        if($count >= $max_logs) break;
        
        $sev_color = match($log['severity']) {
            'CRITICAL' => '#ff5555',
            'HIGH' => '#ffb86c',
            'WARNING' => '#f1c40f',
            'INFO' => '#8be9fd',
            'LOW' => '#dcdde1',
            default => '#f8f8f2'
        };
    ?>
    <div class="log-entry d-flex gap-2 log-row" onclick="window.open('terminal.php?target=<?php echo $log['ip']; ?>', 'terminal_window')" title="Investigate IP: <?php echo $log['ip']; ?>" data-message="<?php echo htmlspecialchars($log['message']); ?>" style="font-family: 'Fira Code', monospace; font-size: 0.8em; border-bottom: 1px solid #44475a; padding: 4px 0; cursor: pointer;">
        <span class="text-secondary" style="min-width: 140px;"><?php echo htmlspecialchars($log['date']); ?></span>
        <span style="min-width: 80px; color: <?php echo $sev_color; ?>"><?php echo $log['severity']; ?></span>
        <span style="color: #bd93f9; min-width: 100px;"><?php echo $log['type']; ?></span>
        <span class="text-white text-truncate flex-grow-1"><?php echo htmlspecialchars($log['message']); ?></span>
    </div>
    <?php $count++; endforeach; 
    exit(); // Stop execution after sending rows
}

// --- CALC DATA FOR DASHBOARD ---
// 1. Alert Count
$agent_count = count($unique_hosts);
// arsort($alerts_by_agent); // Already sorted above as alerts_by_ip

// --- CALC DATA FOR DASHBOARD ---
// 1. Alert Count
$alert_count = $total_attacks;

// 2. Risk Score (0-15 scale visual - Cumulative based on volume/severity in filter)
$max_score = 0;
$risk_points = 0;

// Scoring: Critical=3, High=2, Warning=1
$risk_points += ($stats['SQLi'] + $stats['RCE'] + $stats['SSTI']) * 3;
$risk_points += ($stats['SSH'] + $stats['XSS'] + $stats['LFI']) * 2;
$risk_points += ($stats['Error'] ?? 0) * 1;

// Normalize to 0-15 scale
// Factor: Scale points to max 15. The previous divisor /2 was too sensitive.
// All Time might have 5000 points. 24h might have 100 points.
// We need a log scale or a much higher divisor to make it move.

if ($time_filter == '24h') {
    // For 24h, be more sensitive but still require volume
    // e.g. 50 points -> score 5. 150 points -> score 15.
    $max_score = min(15, ceil($risk_points / 10));
} else {
    // For All Time / 7d, require massive volume to hit 15
    // e.g. 500 points -> score 5. 1500 points -> score 15.
    $max_score = min(15, ceil($risk_points / 100));
}
 
if ($max_score < 1) $max_score = 1; // Minimum score

$max_sev_color = '#2ed573'; // Low Green
if ($max_score >= 10) {
    $max_sev_color = '#ff4757'; // Critical Red
} elseif ($max_score >= 5) {
    $max_sev_color = '#ffa502'; // Warning Orange
}

// 3. Unique Attackers (Source IPs)
$unique_ips = [];
$alerts_by_ip = [];
foreach ($logs as $log) {
    if (isset($log['ip']) && $log['ip'] !== 'UNKNOWN') {
        $unique_ips[$log['ip']] = true;
        if (!isset($alerts_by_ip[$log['ip']])) $alerts_by_ip[$log['ip']] = 0;
        $alerts_by_ip[$log['ip']]++;
    }
}
$attacker_count = count($unique_ips);
arsort($alerts_by_ip);

// 4. Total Events
$total_events = count($lines);

include 'includes/header.php';
?>

<style>
    :root {
        --bg-dark: #0f111a;
        --card-bg: #1a1c29;
        --text-color: #a0a0a0;
        --accent-red: #ff4757;
        --accent-green: #2ed573;
        --accent-blue: #3742fa;
    }
    body { background-color: var(--bg-dark) !important; color: var(--text-color); }
    .edr-card {
        background: var(--card-bg);
        border: 1px solid #2f3542;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .edr-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #747d8c;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }
    .big-stat {
        font-size: 3rem;
        font-weight: 300;
        color: var(--accent-red);
        line-height: 1;
    }
    .metric-gauge { height: 120px; position: relative; }
    .metric-value {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2rem; font-weight: bold; color: #ffa502;
    }
    .agent-row {
        display: flex; justify-content: space-between;
        margin-bottom: 6px; font-size: 0.8rem;
    }
    .agent-bar { height: 24px; background: #e0e0e0; display: flex; align-items: center; width: 100%; border-radius: 2px; overflow: hidden; }
    .agent-name { background: var(--accent-red); color: white; padding: 0 8px; height: 100%; display: flex; align-items: center; min-width: 120px; }
    .agent-val { margin-left: auto; padding-right: 8px; font-weight: bold; color: #333; }
    
    .log-feed {
        background: #000;
        font-family: 'Fira Code', monospace;
        font-size: 0.8rem;
        height: 380px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #333;
    }
    .log-feed::-webkit-scrollbar { width: 6px; }
    .log-feed::-webkit-scrollbar-thumb { background: #444; }

    /* Red Bar List Style */
    .red-bar-row {
        background-color: var(--accent-red); /* #ff4757 */
        color: white;
        padding: 8px 12px; /* Slightly more padding */
        margin-bottom: 2px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: 'Fira Code', monospace; /* Tech feel */
        font-size: 1.0rem; /* Increased from 0.8rem for readability */
        font-weight: 600; /* Bolder text */
        border-radius: 0; /* Boxy look per image */
    }
    .red-bar-row:hover { opacity: 0.9; }
    
    .list-header {
        background-color: #1e272e; /* Dark header row */
        color: #3867d6; /* Blueish text per image/theme */
        padding: 5px 10px;
        font-size: 0.7rem;
        font-weight: bold;
        text-transform: uppercase;
        display: flex;
        justify-content: space-between;
        margin-bottom: 0;
        border-bottom: 1px solid #2f3542;
    }
</style>

<div class="container-fluid mt-3">
    <!-- TOP STATS ROW -->
    <div class="row mb-3">
        <!-- ALERTS -->
        <div class="col-md-3">
            <div class="edr-card text-center" style="height: 160px;">
                <div class="edr-title">Alerts</div>
                <div class="big-stat" style="line-height: 100px;"><?php echo $alert_count; ?></div>
            </div>
        </div>
        <!-- MAX SEVERITY -->
        <div class="col-md-3">
            <div class="edr-card text-center" style="height: 160px;">
                <div class="edr-title">Threat Level (0-15)</div>
                <div class="metric-gauge" style="height: 100px;">
                     <canvas id="gaugeChart"></canvas>
                     <div class="metric-value" style="font-size: 2.5rem;"><?php echo $max_score; ?></div>
                </div>
            </div>
        </div>
        <!-- ATTACKERS -->
        <div class="col-md-3">
             <div class="edr-card text-center" style="height: 160px;">
                <div class="edr-title">Unique Attackers</div>
                <div class="big-stat" style="color: var(--accent-red); line-height: 100px;"><?php echo $attacker_count; ?></div>
            </div>
        </div>
        <!-- EVENTS -->
        <div class="col-md-3">
            <div class="edr-card text-center" style="height: 160px;">
                <div class="edr-title">Events (Total)</div>
                <div class="big-stat" style="color: var(--accent-blue); font-size: 3rem; line-height: 100px;"><?php echo number_format($total_events); ?></div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT ROW -->
    <div class="row">
        <!-- ALERT DETAILS (FEED) - LEFT MAIN -->
        <div class="col-md-8">
            <div class="edr-card" style="height: 600px; padding: 0;">
                 <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <span class="edr-title mb-0">
                        Alerts - Details 
                        <?php if($ip_filter): ?>
                            <a href="dashboard.php?time_filter=<?php echo $time_filter; ?>&sort=<?php echo $current_sort; ?>&category_filter=<?php echo $category_filter; ?>" class="badge bg-danger text-white text-decoration-none custom-badge-reset" style="font-size: 0.7rem; margin-left: 10px;">
                                IP: <?php echo htmlspecialchars($ip_filter); ?> <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                        <?php if($category_filter): ?>
                            <a href="dashboard.php?time_filter=<?php echo $time_filter; ?>&sort=<?php echo $current_sort; ?>&ip_filter=<?php echo $ip_filter; ?>" class="badge bg-warning text-dark text-decoration-none custom-badge-reset" style="font-size: 0.7rem; margin-left: 5px;">
                                Type: <?php echo htmlspecialchars($category_filter); ?> <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </span>
                    <div class="d-flex gap-2">
                        <!-- TIME FILTER -->
                        <select name="time_filter" class="form-select form-select-sm bg-dark text-white border-secondary py-0" style="width: auto; height: 24px; font-size: 0.75rem;" onchange="applyTimeFilter(this.value)">
                            <option value="all" <?php echo $time_filter == 'all' ? 'selected' : ''; ?>>All Time</option>
                            <option value="24h" <?php echo $time_filter == '24h' ? 'selected' : ''; ?>>Last 24h</option>
                            <option value="7d" <?php echo $time_filter == '7d' ? 'selected' : ''; ?>>Last 7 Days</option>
                        </select>
                        <!-- SORT -->
                         <select name="sort_val" class="form-select form-select-sm bg-dark text-white border-0 py-0" style="width: auto; height: 24px; font-size: 0.75rem;" onchange="updateSort(this.value)">
                            <option value="default" <?php echo $current_sort == 'default' ? 'selected' : ''; ?>>Time</option>
                            <option value="severity_desc" <?php echo $current_sort == 'severity_desc' ? 'selected' : ''; ?>>Sev</option>
                        </select>
                    </div>
                 </div>
                 <div class="log-feed" id="logFeed" style="height: 550px;">
                    <?php 
                    $max_logs = 2000; // Increase visible logs to show full history
                    $count = 0;
                    foreach($display_logs as $log): 
                        if($count >= $max_logs) break;
                        
                        $sev_color = match($log['severity']) {
                            'CRITICAL' => '#ff5555',
                            'HIGH' => '#ffb86c',
                            'WARNING' => '#f1c40f',
                            'INFO' => '#8be9fd',
                            'LOW' => '#dcdde1',
                            default => '#f8f8f2'
                        };
                    ?>
                    <div class="log-entry d-flex gap-2 log-row" onclick="window.open('terminal.php?target=<?php echo $log['ip']; ?>', '_blank')" title="Investigate IP: <?php echo $log['ip']; ?>" data-message="<?php echo htmlspecialchars($log['message']); ?>" style="border-bottom: 1px solid #44475a; padding: 4px 0; cursor: pointer;">
                        <span class="text-secondary" style="min-width: 140px;"><?php echo htmlspecialchars($log['date']); ?></span>
                        <span style="min-width: 50px; color: <?php echo $sev_color; ?>"><?php echo $log['severity']; ?></span>
                        <span class="text-white text-truncate flex-grow-1"><?php echo htmlspecialchars($log['message']); ?></span>
                    </div>
                    <?php $count++; endforeach; ?>
                 </div>
            </div>
        </div>

        <!-- RIGHT COLUMN (LISTS) -->
        <div class="col-md-4 d-flex flex-column">
            
            <!-- TOP ATTACKERS -->
             <div class="edr-card p-0" style="height: 290px; overflow: hidden; display: flex; flex-direction: column; margin-bottom: 20px;">
                <div class="edr-title p-2 mb-0 border-bottom border-dark">Top Attackers</div>
                <div class="list-header">
                    <span>IP Address</span>
                    <span>Alerts</span>
                </div>
                <div style="overflow-y: auto; flex: 1;">
                    <?php foreach($alerts_by_ip as $ip => $count): 
                        $isActive = ($ip === $ip_filter);
                        $rowStyle = $isActive ? 'background-color: #44475a; border-left: 3px solid #ff5555;' : '';
                        // Toggle Logic: If active, link removes filter. If inactive, link sets filter.
                        $targetUrl = "dashboard.php?time_filter={$time_filter}&sort={$current_sort}&category_filter={$category_filter}"; // Preserve category
                        if (!$isActive) {
                            $targetUrl .= "&ip_filter={$ip}";
                        } else {
                            // Removing IP filter, keep category
                             $targetUrl = "dashboard.php?time_filter={$time_filter}&sort={$current_sort}&category_filter={$category_filter}";
                        }
                    ?>
                    <div class="red-bar-row" onclick="window.location.href='<?php echo $targetUrl; ?>'" style="cursor: pointer; <?php echo $rowStyle; ?>">
                        <span class="text-truncate" style="max-width: 140px;" title="<?php echo $ip; ?>">
                            <?php echo $ip; ?> 
                            <?php if($isActive): ?><i class="bi bi-x text-danger" title="Remove Filter"></i><?php endif; ?>
                        </span>
                        <span class="red-bar-count"><?php echo $count; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
             </div>

            <!-- ALERTS BY CATEGORY -->
             <div class="edr-card p-0" style="height: 290px; overflow: hidden; display: flex; flex-direction: column;">
                <div class="edr-title p-2 mb-0 border-bottom border-dark">Alerts by Category</div>
                <div class="list-header">
                    <span>Category</span>
                    <span>Events</span>
                </div>
                <div style="overflow-y: auto; flex: 1;">
                    <?php foreach($stats as $type => $count): 
                        if($count == 0) continue; 
                        $isActive = ($type === $category_filter);
                        $rowStyle = $isActive ? 'background-color: #44475a; border-left: 3px solid #f1c40f;' : '';
                        
                        // Toggle Logic
                        $targetUrl = "dashboard.php?time_filter={$time_filter}&sort={$current_sort}&ip_filter={$ip_filter}"; // Preserve IP
                        if (!$isActive) {
                            $targetUrl .= "&category_filter={$type}";
                        }
                    ?>
                    <div class="red-bar-row" onclick="window.location.href='<?php echo $targetUrl; ?>'" style="cursor: pointer; <?php echo $rowStyle; ?>">
                        <span>
                            <?php echo $type; ?>
                            <?php if($isActive): ?><i class="bi bi-x text-warning" title="Remove Filter"></i><?php endif; ?>
                        </span>
                        <span class="red-bar-count"><?php echo $count; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
             </div>

        </div>
    </div>

            <!-- BOTTOM CHART (Attack Vectors - Restored) -->
            <div class="row">
                <div class="col-12">
                    <div class="edr-card">
                        <div class="edr-title">Attack Vectors</div>
                        <div style="height: 250px; width: 100%;">
                            <canvas id="attackChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gauge Chart
new Chart(document.getElementById('gaugeChart'), {
    type: 'doughnut',
    data: {
        labels: ['Scale', 'Target'],
        datasets: [
            // Outer Ring (Scale)
            {
                data: [5, 5, 5], 
                backgroundColor: ['#2ed573', '#ffa502', '#ff4757'], 
                borderWidth: 0, 
                circumference: 180, 
                rotation: 270, 
                cutout: '90%',  // Thin Outer Ring
                weight: 0.2
            },
            // Inner Ring (Value)
            {
                data: [<?php echo $max_score; ?>, <?php echo 15 - $max_score; ?>],
                backgroundColor: ['<?php echo $max_sev_color; ?>', '#2f3640'], 
                borderWidth: 0, 
                circumference: 180, 
                rotation: 270, 
                cutout: '70%',  // Thicker Inner Ring
                weight: 0.8
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } }
    }
});

// Attack Vectors Chart (Restored)
new Chart(document.getElementById('attackChart'), {
    type: 'bar',
    data: {
        labels: ['SSH', 'SQLi', 'XSS', 'RCE', 'SSTI', 'LFI', 'Other'],
        datasets: [{
            label: 'Attempts',
            data: [
                <?php echo $stats['SSH']; ?>, 
                <?php echo $stats['SQLi']; ?>, 
                <?php echo $stats['XSS']; ?>, 
                <?php echo $stats['RCE']; ?>, 
                <?php echo $stats['SSTI']; ?>, 
                <?php echo $stats['LFI']; ?>, 
                <?php echo $stats['Other']; ?>
            ],
            backgroundColor: 'rgba(129, 236, 236, 0.4)', /* Subtle Cyan */
            borderColor: '#81ecec',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#2f3640' }, ticks: { color: '#a0a0a0' }, beginAtZero: true },
            x: { grid: { display: false }, ticks: { color: '#a0a0a0' } }
        }
    }
});

// AJAX Sort Function (Updated for Feed)
// Unified Filter Handler
function updateDashboard() {
    const timeValue = document.querySelector('select[onchange^="updateDashboard"]').value;
    // We need to find the Sort select. It might not have an ID, so we query by its onchange handler or relative position.
    // Based on the HTML structure, it's the second select in the div.
    // Let's add IDs to the selects in the HTML for reliability, but for now we'll query by name/structure if needed.
    // Actually, let's just use the known onchange attributes as selectors, it's brittle but works without HTML changes.
    const sortSelect = document.querySelector('select[onchange*="updateSort"]') || document.querySelector('select[onchange*="updateDashboard"]');
    
    // Wait, the previous code had separate functions. Let's stick to that but fix the logic.
}

function updateSort(sortValue) {
    const timeFilter = document.querySelector('select[name="time_filter"]').value;
    const urlParams = new URLSearchParams(window.location.search);
    const ipFilter = urlParams.get('ip_filter') || '';
    const catFilter = urlParams.get('category_filter') || '';
    
    window.location.href = `dashboard.php?time_filter=${timeFilter}&sort=${sortValue}&ip_filter=${ipFilter}&category_filter=${catFilter}`;
}

function applyTimeFilter(timeValue) {
    const sortValue = document.querySelector('select[name="sort_val"]').value;
    const urlParams = new URLSearchParams(window.location.search);
    const ipFilter = urlParams.get('ip_filter') || '';
    const catFilter = urlParams.get('category_filter') || '';

    window.location.href = `dashboard.php?time_filter=${timeValue}&sort=${sortValue}&ip_filter=${ipFilter}&category_filter=${catFilter}`;
}
</script>

<?php include 'includes/footer.php'; ?>
