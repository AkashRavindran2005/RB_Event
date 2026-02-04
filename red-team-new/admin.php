<?php 
include 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Local File Inclusion vulnerability
$page = isset($_GET['file']) ? $_GET['file'] : 'dashboard';

include 'includes/header.php';
?>

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-md-3 bg-light">
            <h4 class="mt-3">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="?file=dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?file=users">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?file=logs">Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?file=settings">Settings</a>
                </li>
            </ul>
            <!-- LFI Hint: Try ?file=../../../etc/passwd or ?file=includes/config -->
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4>Admin Dashboard</h4>
                </div>
                <div class="card-body">
                    <?php
                    // Intentionally vulnerable to LFI
                    if (file_exists($page . '.php')) {
                        include($page . '.php');
                    } else {
                        // Try to include any file
                        @include($page);
                        echo "<p>Welcome to the admin panel. Select an option from the sidebar.</p>";
                    }
                    ?>
                    
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="alert alert-info text-center">
                                <h4>245</h4>
                                <p>Total Users</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success text-center">
                                <h4>1,234</h4>
                                <p>Requests Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning text-center">
                                <h4>23</h4>
                                <p>Failed Logins</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger text-center">
                                <h4>7</h4>
                                <p>Security Alerts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
