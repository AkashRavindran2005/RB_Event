<?php 
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

// Vulnerable to XSS - no sanitization
$name = isset($_GET['name']) ? $_GET['name'] : $_SESSION['username'];
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h2>Welcome, <?php echo $name; ?>!</h2>
            <!-- XSS Vulnerability: Try ?name=<script>alert('XSS')</script> -->
            
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h4>Your Dashboard</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h3>12</h3>
                                    <p>Active Projects</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h3>5</h3>
                                    <p>Pending Tickets</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <h3>3</h3>
                                    <p>Messages</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Flag hidden in response header -->
                    <?php header('X-Custom-Flag: FLAG{h34d3r5_t3ll_s3cr3ts}'); ?>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
