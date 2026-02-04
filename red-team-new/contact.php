<?php 
include 'includes/config.php';
include 'includes/header.php';

$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // No CSRF protection
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Vulnerable to stored XSS
    $query = "INSERT INTO messages (name, email, message, created_at) 
              VALUES ('$name', '$email', '$message', NOW())";
    
    mysqli_query($conn, $query);
    logActivity('contact_form', "Name: $name, Email: $email");
    
    $success = "Thank you for your message!";
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h2 class="mb-4">Contact Us</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
            
            <!-- Recent Messages (Stored XSS vulnerability) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Recent Inquiries</h5>
                </div>
                <div class="card-body">
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <strong><?php echo $row['name']; ?></strong>
                        <p><?php echo $row['message']; ?></p>
                        <small class="text-muted"><?php echo $row['created_at']; ?></small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
