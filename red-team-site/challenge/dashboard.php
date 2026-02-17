<?php
include 'includes/config.php';

header('X-Custom-Flag: CCEE{h34d3r5_t3ll_s3cr3ts}');
header('X-Powered-By: CyberTech-Legacy-v1.0');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

$name = isset($_GET['name']) ? $_GET['name'] : $_SESSION['username'];

$sqli_flag = '';
if (isset($_SESSION['sqli_bypassed']) && $_SESSION['sqli_bypassed'] === true) {
    $sqli_flag = '<div class="alert alert-success mb-4" style="background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.3);">'
        . '<strong>🎉 SQL Injection Successful!</strong><br>'
        . 'You bypassed authentication via SQL Injection.<br>'
        . 'Flag: <code>CCEE{sql_1nj3ct10n_m4st3r}</code></div>';
    unset($_SESSION['sqli_bypassed']);
}
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-5">Welcome, <?php echo $name; ?>.</h1>

        <?php if ($sqli_flag): ?>
            <?php echo $sqli_flag; ?>
        <?php endif; ?>

        <h2 class="mb-4">Dashboard Overview</h2>

        <div class="bento-grid mb-5">
            <div class="bento-card p-4">
                <h3 class="display-4 fw-bold text-white mb-2">12</h3>
                <p class="text-secondary">Active Projects</p>
            </div>

            <div class="bento-card p-4">
                <h3 class="display-4 fw-bold text-white mb-2">5</h3>
                <p class="text-secondary">Pending Tickets</p>
            </div>

            <div class="bento-card p-4">
                <h3 class="display-4 fw-bold text-white mb-2">3</h3>
                <p class="text-secondary">Unread Messages</p>
            </div>
        </div>

        <div class="bento-card p-5">
            <h3>System Status</h3>
            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 10px; height: 10px; background: #30d158; border-radius: 50%;"></div>
                    <span class="text-secondary">Database: Online</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 10px; height: 10px; background: #30d158; border-radius: 50%;"></div>
                    <span class="text-secondary">API: Online</span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>