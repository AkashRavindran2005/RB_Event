<?php
include 'includes/db_ctf.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, password, team_id FROM ctf_users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $username;
            $_SESSION['team_id'] = $row['team_id'];

            if ($row['team_id']) {
                // Fetch team name
                $t_res = mysqli_query($conn, "SELECT name FROM ctf_teams WHERE id = " . $row['team_id']);
                $t_row = mysqli_fetch_assoc($t_res);
                $_SESSION['team_name'] = $t_row['name'];
                header("Location: dashboard.php");
            } else {
                header("Location: setup_team.php");
            }
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="challenge/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-black text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="p-5 rounded" style="background: #111; border: 1px solid #333; max-width: 400px; width: 100%;">
        <h3 class="fw-bold mb-4">Login</h3>

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
            <button type="submit" class="btn btn-primary w-100 rounded-pill">Login</button>
        </form>

        <div class="text-center mt-4">
            <a href="register.php" class="text-secondary small text-decoration-none">Register New Account</a>
        </div>
    </div>

</body>

</html>