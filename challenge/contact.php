<?php
include 'includes/config.php';
include 'includes/header.php';

$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $query = "INSERT INTO messages (name, email, message, created_at) 
              VALUES ('$name', '$email', '$message', NOW())";
    mysqli_query($conn, $query);

    $success = "Message received! We'll get back to you soon.";
}
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 800px;">
        <div class="text-center mb-5">
            <h1 class="display-text mb-3">Contact Support</h1>
            <p class="text-secondary" style="font-size: 20px;">We're here to help with your security needs.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success bg-opacity-10 border-success text-success mb-4 text-center">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="bento-card p-5 mb-5">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary small">NAME</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-secondary small">EMAIL</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small">MESSAGE</label>
                    <textarea name="message" class="form-control" rows="6" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary px-5">Send Message</button>
            </form>
        </div>

        <h3 class="mb-4 border-top border-secondary pt-5">Public Feedback</h3>
        <div class="bento-grid">
            <?php
            $result = mysqli_query($conn, "SELECT * FROM messages WHERE is_private = 0 ORDER BY created_at DESC LIMIT 6");
            $xss_detected = false;
            while ($row = mysqli_fetch_assoc($result)):
                // Check if this message contains XSS payload
                if (preg_match('/<script|onerror|onload|onclick|javascript:/i', $row['message'])) {
                    $xss_detected = true;
                }
                ?>
                <a href="view_message.php?id=<?php echo $row['id']; ?>" class="bento-card p-4 text-decoration-none"
                    style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-white"><?php echo htmlspecialchars($row['name']); ?></strong>
                        <small class="text-secondary"><?php echo date('M d', strtotime($row['created_at'])); ?></small>
                    </div>
                    <!-- XSS Vulnerability: Message is not sanitized! -->
                    <p class="text-secondary mb-0"><?php echo $row['message']; ?></p>
                </a>
            <?php endwhile; ?>

            <?php if ($xss_detected): ?>
                <div class="bento-card p-4 border-success" style="border: 2px solid #28a745 !important;">
                    <div class="text-success">
                        <strong>🎉 XSS Detected!</strong><br>
                        Flag: <code>CCEE{st0r3d_xss_1n_c0nt4ct}</code>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>