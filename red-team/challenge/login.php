<?php
include 'includes/config.php';

// Vulnerability: PHP Object Injection
class UserSession
{
    public $username;
    public $role;
    public $isValid = false;

    function __wakeup()
    {
        if ($this->role === 'admin') {
            $this->isValid = true;
        }
    }
}

$error = "";

if (isset($_COOKIE['session_token'])) {
    try {
        $session = unserialize(base64_decode($_COOKIE['session_token']));
        if ($session && $session->isValid && $session->role === 'admin') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $session->username;
            $_SESSION['role'] = 'admin';
            header('Location: admin.php');
            exit();
        }
    } catch (Exception $e) {
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($password === $row['password']) {

            $session = new UserSession();
            $session->username = $row['username'];
            $session->role = $row['role'];
            $session->isValid = true;

            setcookie('session_token', base64_encode(serialize($session)), time() + 3600, "/");

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin')
                header('Location: admin.php');
            else
                header('Location: dashboard.php');
            exit();
        }
    }
    $error = "Invalid credentials.";
}

include 'includes/header.php';
?>

<div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div style="width: 100%; max-width: 400px; text-align: center;">
        <h2 class="mb-5 fw-bold">Sign in to CyberTech</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 text-start">
                <input type="text" name="username" class="form-control" placeholder="ID or Email" required
                    style="background: #1d1d1f; border-color: #424245;">
            </div>
            <div class="mb-4 text-start">
                <input type="password" name="password" class="form-control" placeholder="Password" required
                    style="background: #1d1d1f; border-color: #424245;">
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-4" style="border-radius: 12px;">Sign In</button>
        </form>

        <p class="text-secondary small">Hint: Check the robots.txt or look for backup files (.bak). Also try LFI with
            php://filter wrappers.</p>
    </div>
</div>

<!-- 
    Developer Notes (TODO: Remove before production)
    - Legacy login at login_legacy.php has known SQL injection issues
    - config.php.bak was supposed to be deleted
    - Search API at api/search.php needs sanitization
    - Check dashboard headers with curl -I
-->

<?php include 'includes/footer.php'; ?>