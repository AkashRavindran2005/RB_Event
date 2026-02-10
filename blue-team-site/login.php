<?php
include 'includes/config.php';

// Using standard PHP sessions

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin.php');
        exit();
    }
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcoded credentials for Demo Environment (DB might be inaccessible/readonly)
    if (($username === 'analyst' || $username === 'admin') && $password === 'kali') {
         $_SESSION['user_id'] = 1;
         $_SESSION['username'] = $username;
         $_SESSION['role'] = ($username === 'admin') ? 'admin' : 'analyst';
         
         if ($_SESSION['role'] == 'admin')
            header('Location: admin.php');
         else
            header('Location: dashboard.php');
         exit();
    }

    // Fallback to DB check (Original Logic)
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($password === $row['password']) {
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
    $error = "Invalid credentials. Try analyst:kali";
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


    </div>
</div>



<?php include 'includes/footer.php'; ?>