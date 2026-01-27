<?php
include 'includes/config.php';

// VULNERABLE LOGIN ENDPOINT - SQL INJECTION
// This is intentionally vulnerable for the CTF challenge

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABLE: Direct string concatenation in SQL query
    // Try: admin' OR '1'='1' -- 
    // Or: ' UNION SELECT 1,2,3,4,5,6,7 -- 
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        $success = "Login successful! Welcome " . $row['username'] . ". Here's your reward: FLAG{sql_1nj3ct10n_m4st3r}";

        if ($row['role'] == 'admin') {
            $success .= " <br><a href='admin.php' class='btn btn-primary mt-3'>Go to Admin Panel</a>";
        } else {
            $success .= " <br><a href='dashboard.php' class='btn btn-primary mt-3'>Go to Dashboard</a>";
        }
    } else {
        $error = "Invalid credentials. SQL Error: " . mysqli_error($conn);
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 500px;">
        <div class="bento-card p-5">
            <h2 class="text-white mb-4 text-center">Legacy Login Portal</h2>
            <p class="text-secondary small text-center mb-4">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                This is an older authentication system scheduled for deprecation.
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger bg-opacity-10 border-danger text-danger mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success bg-opacity-10 border-success text-success mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required
                        style="background: #1d1d1f; border-color: #424245;">
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required
                        style="background: #1d1d1f; border-color: #424245;">
                </div>

                <button type="submit" class="btn btn-primary w-100" style="border-radius: 12px;">Sign In</button>
            </form>

            <div class="text-center mt-4">
                <a href="login.php" class="text-accent small">Use Modern Login →</a>
            </div>
        </div>

        <p class="text-secondary small text-center mt-4">
            Hint: This legacy system might have some... vulnerabilities.
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>