<?php
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$result_msg = "";
$result_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report_url'])) {
    $url = trim($_POST['report_url']);

    if (empty($url)) {
        $result_msg = "Please enter a URL to report.";
        $result_type = "danger";
    } else {
        logActivity('csrf_report', "URL reported to admin bot: $url");

        // Rewrite external URLs to internal (Docker: port 8000 outside = port 80 inside)
        $internal_url = preg_replace('#localhost:\d+#', 'localhost', $url);

        // Fetch the reported page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $internal_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html === false || $http_code == 0) {
            $result_msg = "Admin bot could not reach the URL. Make sure the URL is accessible.";
            $result_type = "danger";
        } else {
            // Parse forms and simulate admin auto-submitting them
            $forms_found = 0;
            $forms_submitted = 0;

            if (preg_match_all('/<form[^>]*>(.*?)<\/form>/si', $html, $form_matches)) {
                $forms_found = count($form_matches[0]);

                foreach ($form_matches[0] as $idx => $full_form) {
                    $form_body = $form_matches[1][$idx];

                    if (!preg_match('/method=["\']post["\']/i', $full_form))
                        continue;

                    // Get action URL
                    $action = '';
                    if (preg_match('/action=["\']([^"\']*)["\']/', $full_form, $act)) {
                        $action = $act[1];
                    }

                    // Extract all input name=value pairs
                    $post_data = [];
                    preg_match_all('/<input[^>]+>/i', $form_body, $inputs);
                    foreach ($inputs[0] as $input_tag) {
                        $name = $value = '';
                        if (preg_match('/name=["\']([^"\']+)["\']/', $input_tag, $m))
                            $name = $m[1];
                        if (preg_match('/value=["\']([^"\']*)["\']/', $input_tag, $m))
                            $value = $m[1];
                        if ($name)
                            $post_data[$name] = $value;
                    }

                    if (empty($post_data))
                        continue;

                    // Simulate the admin being CSRF'd:
                    // If the form targets profile.php and has a new_password field,
                    // directly apply the change to the admin account (user_id=1)
                    if (strpos($action, 'profile.php') !== false && isset($post_data['new_password']) && !empty($post_data['new_password'])) {
                        $new_pass = $post_data['new_password'];
                        $admin_id = 1; // admin is always user_id 1
                        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "si", $new_pass, $admin_id);
                        if (mysqli_stmt_execute($stmt)) {
                            $forms_submitted++;
                            logActivity('csrf_bot', "CSRF attack succeeded! Admin password changed via forged form.");
                        }
                    }

                    // If the form targets profile.php and has email change
                    if (strpos($action, 'profile.php') !== false && isset($post_data['email'])) {
                        $new_email = $post_data['email'];
                        $admin_id = 1;
                        $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, "si", $new_email, $admin_id);
                        if (mysqli_stmt_execute($stmt)) {
                            if ($forms_submitted == 0)
                                $forms_submitted++;
                            logActivity('csrf_bot', "CSRF: Admin email changed via forged form.");
                        }
                    }

                    // If the form targets profile.php with credit transfer
                    if (strpos($action, 'profile.php') !== false && isset($post_data['transfer_to']) && isset($post_data['transfer_amount'])) {
                        $forms_submitted++;
                        logActivity('csrf_bot', "CSRF: Credit transfer form detected.");
                    }
                }
            }

            if ($forms_submitted > 0) {
                $result_msg = "✅ Admin reviewed your report. The admin bot visited the URL and interacted with $forms_submitted form(s).";
                $result_type = "success";
            } elseif ($forms_found > 0) {
                $result_msg = "⚠️ Admin visited the URL. Found $forms_found form(s) but none had actionable data.";
                $result_type = "warning";
            } else {
                $result_msg = "ℹ️ Admin visited the URL (HTTP $http_code). No forms were found on the page.";
                $result_type = "info";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom" style="max-width: 800px;">
        <h1 class="display-text mb-3">Report Suspicious URL</h1>
        <p class="text-secondary mb-5" style="font-size: 20px;">
            Submit a suspicious URL for our admin security team to review.
            An admin will visit the page to investigate.
        </p>

        <?php if ($result_msg): ?>
            <div class="alert alert-<?php echo $result_type; ?> mb-4">
                <?php echo $result_msg; ?>
            </div>
        <?php endif; ?>

        <div class="bento-card p-5 mb-4">
            <h4 class="text-white mb-4"><i class="fas fa-flag me-2"></i>Report a URL</h4>
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label text-secondary">Suspicious URL</label>
                    <input type="url" name="report_url" class="form-control bg-dark text-white border-secondary"
                        placeholder="http://localhost:8000/challenge/exploits/csrf_exploit.html" required>
                    <small class="text-muted">The admin bot will visit this URL and interact with any forms it
                        finds.</small>
                </div>
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fas fa-paper-plane me-2"></i>Submit Report (Admin Bot Will Visit)
                </button>
            </form>
        </div>

        <div class="bento-card p-4">
            <h5 class="text-white mb-3"><i class="fas fa-info-circle me-2"></i>How It Works</h5>
            <ul class="text-secondary mb-0">
                <li>An <strong>admin user</strong> will visit the URL you submit</li>
                <li>The admin is <strong>logged in</strong> while browsing your page</li>
                <li>Any forms on the page will be <strong>auto-submitted</strong> with the admin's session</li>
                <li>Use this to report phishing pages, suspicious links, or security concerns</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>