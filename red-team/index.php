<?php
session_start();
if (isset($_SESSION['team_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CyberTech CTF | Registration</title>
    <link href="challenge/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-black text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="text-center" style="max-width: 500px; width: 100%; padding: 20px;">
        <h1 class="display-4 fw-bold mb-4">CyberTech CTF</h1>
        <p class="text-secondary mb-5">Welcome to the Red Team Challenge. Register your team to begin the assessment.
        </p>

        <div class="d-grid gap-3">
            <a href="login.php" class="btn btn-primary btn-lg rounded-pill">Team Login</a>
            <a href="register.php" class="btn btn-outline-light btn-lg rounded-pill">Register Team</a>
        </div>

        <div class="mt-5 pt-5 border-top border-secondary">
            <a href="challenge/index.php" target="_blank" class="text-secondary text-decoration-none small">Preview
                Vulnerable Site <i class="fas fa-external-link-alt"></i></a>
        </div>
    </div>

</body>

</html>