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
    // Or: ' OR 1=1 --
    // Or: admin'--
    // Or: ' UNION SELECT 1,2,3,4,5,6,7 -- 
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

    // Suppress errors for cleaner CTF experience (errors still shown in response)
    try {
        $result = @mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // Handle case where SQLi returns valid user data
            if (isset($row['id']) && isset($row['username'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = isset($row['role']) ? $row['role'] : 'user';

                $success = "Login successful! Welcome " . htmlspecialchars($row['username']) . ". <br><small class='text-muted'>Check your <a href='internal_docs.php' class='text-accent'>internal documents</a> for important notices.</small>";

                if (isset($row['role']) && $row['role'] == 'admin') {
                    $success .= " <br><a href='admin.php' class='btn btn-primary mt-3'>Go to Admin Panel</a>";
                } else {
                    $success .= " <br><a href='dashboard.php' class='btn btn-primary mt-3'>Go to Dashboard</a>";
                }
            } else {
                // UNION-based or other SQLi that returns rows but not proper user structure
                // Still grant access since they successfully exploited it
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'sqli_bypass';
                $_SESSION['role'] = 'user';

                $success = "Authentication bypassed! <br><small class='text-muted'>Check your <a href='internal_docs.php' class='text-accent'>internal documents</a> for important notices.</small>";
                $success .= " <br><a href='dashboard.php' class='btn btn-primary mt-3'>Go to Dashboard</a>";
            }

            logActivity('sqli_success', "SQLi bypass detected. Query: " . substr($query, 0, 200));
        } else {
            // Show error for debugging (intentionally verbose for CTF)
            $sql_error = mysqli_error($conn);
            if ($sql_error) {
                $error = "Invalid credentials. <br><small class='text-danger'>SQL Error: " . htmlspecialchars($sql_error) . "</small>";
            } else {
                $error = "Invalid credentials.";
            }
        }
    } catch (Exception $e) {
        $error = "Invalid credentials. <br><small class='text-danger'>SQL Error: " . htmlspecialchars($e->getMessage()) . "</small>";
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