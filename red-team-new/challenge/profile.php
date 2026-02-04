<?php
include 'includes/config.php';

// CSRF Vulnerability - No CSRF token validation!
// The attacker can create a malicious page that submits this form

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Vulnerable to CSRF - no token validation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = isset($_POST['email']) ? $_POST['email'] : $user['email'];

    // Check for password change request (CSRF target)
    if (isset($_POST['new_password']) && !empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];

        // No CSRF token check! Attacker can change victim's password
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $new_password, $user_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $success = "Password updated successfully!";
            $_SESSION['csrf_state_changed'] = true;
            $_SESSION['csrf_action'] = 'password_change';
            logActivity('password_change', "User ID: $user_id password changed via CSRF-vulnerable form");
        } else {
            $error = "Failed to update password.";
        }
    }

    // Email update (also CSRF vulnerable)
    if (isset($_POST['email'])) {
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $new_email, $user_id);
        if (mysqli_stmt_execute($update_stmt)) {
            $success = $success ? $success . " Email also updated!" : "Email updated successfully!";
            $user['email'] = $new_email;
            $_SESSION['csrf_state_changed'] = true;
            $_SESSION['csrf_action'] = isset($_SESSION['csrf_action']) ? $_SESSION['csrf_action'] . '_email' : 'email_change';
        }
    }

    // Transfer credits to another user (High impact CSRF)
    if (isset($_POST['transfer_to']) && isset($_POST['transfer_amount'])) {
        $transfer_to = $_POST['transfer_to'];
        $transfer_amount = intval($_POST['transfer_amount']);

        if ($transfer_amount > 0 && $transfer_amount <= $user['credits']) {
            // Deduct from sender
            $deduct = mysqli_prepare($conn, "UPDATE users SET credits = credits - ? WHERE id = ?");
            mysqli_stmt_bind_param($deduct, "ii", $transfer_amount, $user_id);
            mysqli_stmt_execute($deduct);

            // Add to receiver
            $add = mysqli_prepare($conn, "UPDATE users SET credits = credits + ? WHERE username = ?");
            mysqli_stmt_bind_param($add, "is", $transfer_amount, $transfer_to);
            mysqli_stmt_execute($add);

            $success = "Transferred $transfer_amount credits to $transfer_to!";
            $_SESSION['csrf_state_changed'] = true;
            $_SESSION['csrf_action'] = 'credit_transfer';
            logActivity('credit_transfer', "CSRF-vulnerable transfer: $transfer_amount credits to $transfer_to");

            // Refresh user data
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Invalid transfer amount.";
        }
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-5">Profile Settings</h1>

        <?php if ($success): ?>
            <div class="alert alert-success mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Profile Update Form (CSRF Vulnerable) -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-user me-2"></i>Update Profile</h3>

                    <!-- No CSRF token! This form can be submitted by an attacker -->
                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Username</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                                style="background: #1d1d1f; border-color: #424245;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                style="background: #1d1d1f; border-color: #424245;">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary">New Password</label>
                            <input type="password" name="new_password" class="form-control"
                                placeholder="Enter new password" style="background: #1d1d1f; border-color: #424245;">
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Credit Transfer Form (High Impact CSRF) -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-coins me-2"></i>Transfer Credits</h3>

                    <div class="mb-4">
                        <p class="text-secondary">Current Balance: <span class="text-success fw-bold">$
                                <?php echo $user['credits']; ?>
                            </span></p>
                    </div>

                    <!-- No CSRF protection - attacker can transfer victim's credits! -->
                    <form method="POST" action="profile.php">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Recipient Username</label>
                            <input type="text" name="transfer_to" class="form-control" placeholder="Enter username"
                                style="background: #1d1d1f; border-color: #424245;" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary">Amount</label>
                            <input type="number" name="transfer_amount" class="form-control"
                                placeholder="Amount to transfer" min="1"
                                style="background: #1d1d1f; border-color: #424245;" required>
                        </div>

                        <button type="submit" class="btn btn-success">Transfer Credits</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['csrf_state_changed']) && $_SESSION['csrf_state_changed']): ?>
            <!-- CSRF Flag - Only shown after successful state change -->
            <div class="bento-card p-4 mt-4 border-success" style="border: 2px solid #30d158;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-shield-alt text-success" style="font-size: 32px;"></i>
                    <div>
                        <h5 class="text-success mb-1">Account State Modified</h5>
                        <p class="text-white mb-1">
                            Action: <code
                                class="text-warning"><?php echo htmlspecialchars($_SESSION['csrf_action']); ?></code>
                        </p>
                        <p class="text-muted small mb-0">
                            Security Token: <code class="text-success">CCEE{csrf_st4t3_ch4ng3_d3t3ct3d}</code>
                        </p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['csrf_state_changed']);
            unset($_SESSION['csrf_action']); ?>
        <?php endif; ?>

        <!-- Hint for CTF players -->
        <div class="bento-card p-4 mt-5">
            <p class="text-secondary mb-0">
                <i class="fas fa-lightbulb me-2 text-warning"></i>
                <strong>Security Note:</strong> This page handles sensitive operations. What protection mechanism might
                be missing?
            </p>
        </div>
    </div>
</div>

<!--
    CTF Challenge: CSRF Vulnerability
    
    This page is vulnerable to Cross-Site Request Forgery (CSRF).
    The flag appears only AFTER a successful state change.
    
    To get the flag:
    1. Host a malicious page that auto-submits a form
    2. Have a logged-in victim visit the page
    3. The state change will trigger the flag display on next profile load
-->

<?php include 'includes/footer.php'; ?>