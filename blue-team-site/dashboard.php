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
            $entry['date'] = $matches[1];
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

// --- AJAX HANDLER ---
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    $max_logs = $show_all ? 5000 : 2000;
    $count = 0;
    foreach($logs as $log): 
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
    <div class="log-entry d-flex gap-2 log-row" onclick="window.open('terminal.php?target=<?php echo $log['ip']; ?>', '_blank')" title="Investigate IP: <?php echo $log['ip']; ?>" data-message="<?php echo htmlspecialchars($log['message']); ?>" style="font-family: 'Fira Code', monospace; font-size: 0.8em; border-bottom: 1px solid #44475a; padding: 4px 0; cursor: pointer;">
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
$alert_count = $total_attacks;

// 2. Max Severity (0-15 scale visual)
$max_score = 0;
$max_sev_color = '#2ed573'; // Default Green (Low)

if ($stats['SQLi'] > 0 || $stats['RCE'] > 0 || $stats['SSTI'] > 0) {
    $max_score = 15;
    $max_sev_color = '#ff4757'; // Critical Red
} elseif ($stats['SSH'] > 0 || $stats['XSS'] > 0 || $stats['LFI'] > 0) {
    $max_score = 10;
    $max_sev_color = '#ffa502'; // Warning Orange
} else {
    $max_score = 2;
    $max_sev_color = '#2ed573'; // Low Green
}

// 3. Active Agents
$unique_hosts = [];
$alerts_by_agent = [];
foreach ($logs as $log) {
    if (isset($log['host']) && $log['host'] !== 'UNKNOWN') {
        $unique_hosts[$log['host']] = true;
        if (!isset($alerts_by_agent[$log['host']])) $alerts_by_agent[$log['host']] = 0;
        $alerts_by_agent[$log['host']]++;
    }
}
$agent_count = count($unique_hosts);
arsort($alerts_by_agent);

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
                <div class="edr-title">Max Severity (0-15)</div>
                <div class="metric-gauge" style="height: 100px;">
                     <canvas id="gaugeChart"></canvas>
                     <div class="metric-value" style="font-size: 2.5rem;"><?php echo $max_score; ?></div>
                </div>
            </div>
        </div>
        <!-- AGENTS -->
        <div class="col-md-3">
             <div class="edr-card text-center" style="height: 160px;">
                <div class="edr-title">Agents</div>
                <div class="big-stat" style="color: var(--accent-green); line-height: 100px;"><?php echo $agent_count; ?></div>
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
                    <span class="edr-title mb-0">Alerts - Details</span>
                     <select class="form-select form-select-sm bg-dark text-white border-0 py-0" style="width: auto; height: 20px; font-size: 0.7rem;" onchange="updateSort(this.value)">
                        <option value="default">Time</option>
                        <option value="severity_desc">Sev</option>
                    </select>
                 </div>
                 <div class="log-feed" id="logFeed" style="height: 550px;">
                    <?php 
                    $max_logs = 2000; // Increase visible logs to show full history
                    $count = 0;
                    foreach($logs as $log): 
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
            
            <!-- ALERTS BY AGENT -->
             <div class="edr-card p-0" style="height: 290px; overflow: hidden; display: flex; flex-direction: column; margin-bottom: 20px;">
                <div class="edr-title p-2 mb-0 border-bottom border-dark">Alerts by Agent</div>
                <div class="list-header">
                    <span>Agent</span>
                    <span>Alerts</span>
                </div>
                <div style="overflow-y: auto; flex: 1;">
                    <?php foreach($alerts_by_agent as $agent => $count): ?>
                    <div class="red-bar-row">
                        <span class="text-truncate" style="max-width: 140px;" title="<?php echo $agent; ?>"><?php echo $agent; ?></span>
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
                    <?php foreach($stats as $type => $count): if($count == 0) continue; ?>
                    <div class="red-bar-row">
                        <span><?php echo $type; ?></span>
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
function updateSort(sortValue) {
    const feed = document.getElementById('logFeed');
    feed.style.opacity = '0.5';
    
    fetch(`dashboard.php?ajax=true&sort=${sortValue}`)
        .then(response => response.text())
        .then(html => {
            feed.innerHTML = html;
            feed.style.opacity = '1';
        })
        .catch(err => {
            console.error('Sort failed:', err);
            feed.style.opacity = '1';
        });
}
</script>

<?php include 'includes/footer.php'; ?>
