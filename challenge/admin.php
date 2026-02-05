<?php
include 'includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$page = isset($_GET['file']) ? $_GET['file'] : 'admin_welcome';

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-5">Admin Control Panel</h1>

        <?php
        // Check if user got here via PHP Object Injection (cookie exploit)
        if (isset($_COOKIE['session_token'])) {
            echo '<div class="alert alert-success mb-4">';
            echo '<strong>🎉 Congratulations!</strong> You accessed the admin panel via cookie manipulation!<br>';
            echo 'Flag: <code>CCEE{c00k13_m0nst3r_4dm1n}</code>';
            echo '</div>';
            logActivity('php_object_injection', 'Admin access via serialized cookie exploit');
        }
        ?>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="bento-card p-4 h-auto">
                    <h4 class="text-white mb-4">Navigation</h4>
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item">
                            <a class="nav-link text-white ps-0" href="?file=admin_welcome"><i
                                    class="fas fa-chart-line me-2"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary ps-0" href="?file=admin_users"><i
                                    class="fas fa-users me-2"></i> Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary ps-0" href="?file=admin_logs"><i
                                    class="fas fa-list me-2"></i> System Logs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-secondary ps-0" href="?file=admin_settings"><i
                                    class="fas fa-cog me-2"></i> Settings</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-9">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4">Console Output</h3>

                    <div class="bg-black p-4 rounded border border-secondary" style="min-height: 400px;">
                        <?php
                        if (file_exists($page . '.php')) {
                            ob_start();
                            include($page . '.php');
                            $content = ob_get_clean();
                            echo $content;
                        } else {
                            echo "<pre class='text-secondary mb-0'>";
                            // Check if it's a PHP stream wrapper (like php://filter)
                            if (strpos($page, '://') !== false) {
                                // Use readfile for stream wrappers to ensure output
                                @readfile($page);
                            } else {
                                @include($page);
                            }
                            echo "</pre>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>