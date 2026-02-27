<?php
include 'includes/config.php';

$success = "";

// Handle form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO messages (name, email, message, created_at, is_private) 
              VALUES ('$name', '$email', '$message', NOW(), 0)";
    if (mysqli_query($conn, $query)) {
        $success = "Message received! We'll get back to you soon.";
    } else {
        $success = "Error: " . mysqli_error($conn);
    }
}

// Fetch public messages and check for XSS BEFORE any output
$result = mysqli_query($conn, "SELECT * FROM messages WHERE is_private = 0 ORDER BY created_at DESC LIMIT 6");
$messages = [];
$xss_detected = false;

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
    if (preg_match('/<script|onerror|onload|onclick|javascript:/i', $row['message'])) {
        $xss_detected = true;
    }
}

// Set cookie BEFORE header.php sends any output
if ($xss_detected) {
    setcookie('stored_xss_reward', 'CCEE{st0r3d_xss_1n_c0nt4ct}', time() + 3600, '/');
}

include 'includes/header.php';
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
            <?php foreach ($messages as $row): ?>
                <a href="view_message.php?id=<?php echo $row['id']; ?>" class="bento-card p-4 text-decoration-none"
                    style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="text-white"><?php echo htmlspecialchars($row['name']); ?></strong>
                        <small class="text-secondary"><?php echo date('M d', strtotime($row['created_at'])); ?></small>
                    </div>
                    <!-- XSS Vulnerability: Message is not sanitized! -->
                    <p class="text-secondary mb-0"><?php echo $row['message']; ?></p>
                </a>
            <?php endforeach; ?>

            <?php if ($xss_detected): ?>
                <div class="bento-card p-4 border-success mt-4" style="border: 2px solid #28a745 !important;">
                    <div class="text-success">
                        <strong>🎉 XSS Detected!</strong><br>
                        Your reward has been set — check your <code>browser cookies</code> (DevTools → Application →
                        Cookies)
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>