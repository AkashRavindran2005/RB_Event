<?php
include 'includes/db_ctf.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT id FROM ctf_users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already taken.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO ctf_users (username, password) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $username, $hashed);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;
            $_SESSION['team_id'] = null;

            header("Location: setup_team.php");
            exit();
        } else {
            $error = "Registration failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration</title>
    <link href="challenge/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-black text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="p-5 rounded" style="background: #111; border: 1px solid #333; max-width: 400px; width: 100%;">
        <h3 class="fw-bold mb-4">Register Account</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-secondary small">USERNAME</label>
                <input type="text" name="username" class="form-control bg-dark text-white border-secondary" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-secondary small">PASSWORD</label>
                <input type="password" name="password" class="form-control bg-dark text-white border-secondary"
                    required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Account</button>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="text-secondary small text-decoration-none">Already have an account? Login</a>
        </div>
    </div>

</body>

</html>