<?php
include 'includes/config.php';
include 'includes/header.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $position = $_POST['position'];

    if (isset($_FILES['resume'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["resume"]["name"]);

        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $success = "Application submitted! Resume: " . htmlspecialchars(basename($_FILES["resume"]["name"]));
            $query = "INSERT INTO applications (name, email, position, resume_path, created_at) 
                      VALUES ('$name', '$email', '$position', '$target_file', NOW())";
            mysqli_query($conn, $query);
        } else {
            $error = "Error uploading file.";
        }
    }
}
?>

<div class="section-padding">
    <div class="container-custom">
        <div class="text-center mb-5">
            <h1 class="display-text mb-3">Join Us</h1>
            <p class="text-secondary" style="font-size: 24px;">Build the future of secure technology.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success bg-opacity-10 border-success text-success mb-4"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger bg-opacity-10 border-danger text-danger mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row g-5">
            <!-- Open Positions -->
            <div class="col-md-7">
                <h3 class="mb-4">Open Roles</h3>

                <div class="bento-card mb-4 p-4">
                    <h4 class="text-white">Senior Security Engineer</h4>
                    <p class="text-secondary mb-2"><i class="fas fa-map-marker-alt me-2"></i>Remote</p>
                    <p class="text-secondary">Lead red team engagements and advanced penetration testing operations.</p>
                </div>

                <div class="bento-card mb-4 p-4">
                    <h4 class="text-white">Cloud Solutions Architect</h4>
                    <p class="text-secondary mb-2"><i class="fas fa-map-marker-alt me-2"></i>New York</p>
                    <p class="text-secondary">Design secure, scalable cloud infrastructure for Fortune 500 clients.</p>
                </div>

                <div class="bento-card mb-4 p-4">
                    <h4 class="text-white">Penetration Tester</h4>
                    <p class="text-secondary mb-2"><i class="fas fa-map-marker-alt me-2"></i>London</p>
                    <p class="text-secondary">Identify vulnerabilities in web, mobile, and network systems.</p>
                </div>
            </div>

            <!-- Application Form -->
            <div class="col-md-5">
                <div class="bento-card sticky-top" style="top: 100px;">
                    <h3 class="mb-4">Apply Now</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label text-secondary small">FULL NAME</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">EMAIL</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small">POSITION</label>
                            <select name="position" class="form-control" required>
                                <option>Senior Security Engineer</option>
                                <option>Cloud Solutions Architect</option>
                                <option>Penetration Tester</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary small">RESUME</label>
                            <input type="file" name="resume" class="form-control" required>
                            <div class="form-text text-secondary small mt-1">Accepts PDF, DOCX, or any file type.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>