<?php
include 'includes/config.php';

// Admin Vault - Crown Jewel accessible via multiple exploit paths
// Can be reached via: PHP Object Injection OR JWT Manipulation

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Track the exploit path used to get here for logging
$exploit_path = 'Direct Admin Login';
if (isset($_COOKIE['session_token'])) {
    // Check if they used PHP Object Injection
    try {
        $decoded = base64_decode($_COOKIE['session_token']);
        if (strpos($decoded, 'UserSession') !== false) {
            $exploit_path = 'PHP Object Injection';
            logActivity('admin_vault_access', 'Accessed via PHP Object Injection');
        }
    } catch (Exception $e) {
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 700px;">
        <div class="text-center mb-5">
            <i class="fas fa-vault text-danger mb-4" style="font-size: 80px;"></i>
            <h1 class="display-text">Admin Vault</h1>
            <p class="text-secondary">Highest Security Clearance Area</p>
        </div>

        <div class="bento-card p-5"
            style="border: 2px solid rgba(255,0,0,0.5); background: linear-gradient(135deg, rgba(255,0,0,0.1), transparent);">
            <div class="text-center">
                <span class="badge bg-danger mb-3">CLASSIFIED</span>
                <h3 class="text-white mb-4">Crown Jewel Access Granted</h3>

                <div class="p-4 rounded mb-4" style="background: rgba(0,0,0,0.3);">
                    <p class="text-secondary mb-2">Access Method Detected:</p>
                    <code class="text-warning fs-5"><?php echo htmlspecialchars($exploit_path); ?></code>
                </div>

                <div class="p-4 rounded" style="background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.3);">
                    <p class="text-success mb-2"><i class="fas fa-trophy me-2"></i>ADMIN VAULT FLAG</p>
                    <h2 class="text-success font-monospace mb-3">CCEE{4dm1n_v4ult_0wn3d}</h2>
                    <p class="text-muted small mb-0">
                        400 Bonus Points - This flag represents complete administrative compromise.
                    </p>
                </div>
            </div>
        </div>

        <div class="bento-card p-4 mt-4 h-auto">
            <h5 class="text-white mb-3"><i class="fas fa-info-circle me-2"></i>Vault Access Log</h5>
            <ul class="text-secondary small mb-0">
                <li>User:
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </li>
                <li>Role:
                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                </li>
                <li>Access Time:
                    <?php echo date('Y-m-d H:i:s'); ?>
                </li>
                <li>Exploit Path:
                    <?php echo htmlspecialchars($exploit_path); ?>
                </li>
                <li>Session ID:
                    <?php echo session_id(); ?>
                </li>
            </ul>
        </div>

        <div class="text-center mt-4">
            <a href="admin.php" class="btn btn-outline me-2">← Back to Admin Panel</a>
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>