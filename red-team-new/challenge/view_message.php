<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message_data = null;
$error = "";

if (isset($_GET['id'])) {
    $msg_id = $_GET['id'];
    $query = "SELECT * FROM messages WHERE id = $msg_id";
    $result = mysqli_query($conn, $query);
    $message_data = mysqli_fetch_assoc($result);

    if (!$message_data) {
        $error = "Message not found.";
    } elseif ($message_data['is_private'] && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
        // IDOR vulnerability: Non-admins can see that private messages exist, but not content
        // However, they CAN see some metadata which hints at admin-only data
        $error = "Access Denied: This message is marked as private and requires admin privileges.";
        $error .= "<br><small class='text-muted'>Message ID: " . $msg_id . " | Owner: Admin | Classification: CONFIDENTIAL</small>";
        $message_data = null;
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2>Message Details</h2>
            <a href="contact.php" class="btn btn-outline">Back</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger bg-opacity-10 border-danger text-danger mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($message_data): ?>
            <div class="bento-card p-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-4">
                    <div>
                        <h3 class="mb-1 text-white"><?php echo htmlspecialchars($message_data['name']); ?></h3>
                        <p class="text-accent mb-0"><?php echo htmlspecialchars($message_data['email']); ?></p>
                    </div>
                    <span class="text-secondary small"><?php echo $message_data['created_at']; ?></span>
                </div>

                <div class="p-4 rounded" style="background: rgba(255,255,255,0.05);">
                    <p class="mb-0 text-white" style="font-family: monospace; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($message_data['message'])); ?>
                    </p>
                </div>

                <?php if ($message_data['is_private']): ?>
                    <div class="mt-4 p-3 rounded border border-warning text-warning bg-warning bg-opacity-10">
                        <i class="fas fa-lock me-2"></i> Private Message (Admin Only)
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-secondary">Please select a message to view.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>