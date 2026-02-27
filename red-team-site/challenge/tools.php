<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$output = "";
$tool = isset($_POST['tool']) ? $_POST['tool'] : '';
$target = isset($_POST['target']) ? $_POST['target'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $target) {
    logActivity('network_tool', "Tool: $tool, Target: $target");

    switch ($tool) {
        case 'ping':
            // VULNERABLE: No input sanitization!
            $output = shell_exec("ping -c 2 " . $target . " 2>&1");
            break;
        case 'nslookup':
            // VULNERABLE: No input sanitization!
            $output = shell_exec("nslookup " . $target . " 2>&1");
            break;
        case 'whois':
            // VULNERABLE: No input sanitization!
            $output = shell_exec("whois " . $target . " 2>&1");
            break;
        default:
            $output = "Invalid tool selected.";
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 900px;">
        <h1 class="display-text mb-3">Network Diagnostics</h1>
        <p class="text-secondary mb-5" style="font-size: 20px;">Internal tools for network troubleshooting and analysis.
        </p>

        <div class="row g-4">
            <div class="col-md-5">
                <div class="bento-card p-4 h-100">
                    <h4 class="text-white mb-4"><i class="fas fa-tools me-2"></i>Run Diagnostic</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">TOOL</label>
                            <select name="tool" class="form-select bg-dark text-white border-secondary">
                                <option value="ping" <?php echo $tool === 'ping' ? 'selected' : ''; ?>>Ping</option>
                                <option value="nslookup" <?php echo $tool === 'nslookup' ? 'selected' : ''; ?>>DNS Lookup
                                </option>
                                <option value="whois" <?php echo $tool === 'whois' ? 'selected' : ''; ?>>WHOIS</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary small">TARGET HOST / IP</label>
                            <input type="text" name="target" class="form-control bg-dark text-white border-secondary"
                                placeholder="e.g. 8.8.8.8 or google.com"
                                value="<?php echo htmlspecialchars($target); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-play me-2"></i>Run
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <div class="bento-card p-4 h-100">
                    <h4 class="text-white mb-3"><i class="fas fa-terminal me-2"></i>Output</h4>
                    <div class="bg-black p-3 rounded border border-secondary"
                        style="min-height: 300px; font-family: monospace; font-size: 13px;">
                        <?php if ($output): ?>
                            <pre class="text-success mb-0"
                                style="white-space: pre-wrap; word-break: break-all;"><?php echo htmlspecialchars($output); ?></pre>
                        <?php else: ?>
                            <p class="text-secondary mb-0">Select a tool and enter a target to begin.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bento-card p-4 mt-4">
            <h5 class="text-white mb-3"><i class="fas fa-info-circle me-2"></i>Usage Examples</h5>
            <div class="row">
                <div class="col-md-4">
                    <p class="text-secondary small mb-1"><strong>Ping:</strong></p>
                    <code class="text-accent">8.8.8.8</code>
                </div>
                <div class="col-md-4">
                    <p class="text-secondary small mb-1"><strong>DNS Lookup:</strong></p>
                    <code class="text-accent">google.com</code>
                </div>
                <div class="col-md-4">
                    <p class="text-secondary small mb-1"><strong>WHOIS:</strong></p>
                    <code class="text-accent">example.com</code>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>