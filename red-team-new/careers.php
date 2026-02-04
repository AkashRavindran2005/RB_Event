<?php 
include 'includes/config.php';
include 'includes/header.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    
    // Vulnerable file upload - no validation
    if (isset($_FILES['resume'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["resume"]["name"]);
        
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $success = "Application submitted successfully! Resume uploaded: " . htmlspecialchars(basename($_FILES["resume"]["name"]));
            logActivity('file_upload', "File: " . basename($_FILES["resume"]["name"]));
            
            // Store application
            $query = "INSERT INTO applications (name, email, position, resume_path, created_at) 
                      VALUES ('$name', '$email', '$position', '$target_file', NOW())";
            mysqli_query($conn, $query);
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">Career Opportunities</h2>
    
    <div class="row">
        <div class="col-md-8">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4>Open Positions</h4>
                </div>
                <div class="card-body">
                    <div class="job-listing mb-3 pb-3 border-bottom">
                        <h5>Senior Security Engineer</h5>
                        <p><i class="fas fa-map-marker-alt"></i> Remote | <i class="fas fa-clock"></i> Full-time</p>
                    </div>
                    <div class="job-listing mb-3 pb-3 border-bottom">
                        <h5>Cloud Solutions Architect</h5>
                        <p><i class="fas fa-map-marker-alt"></i> New York | <i class="fas fa-clock"></i> Full-time</p>
                    </div>
                    <div class="job-listing">
                        <h5>Penetration Tester</h5>
                        <p><i class="fas fa-map-marker-alt"></i> London | <i class="fas fa-clock"></i> Contract</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4>Apply Now</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <select name="position" class="form-control" required>
                                <option>Senior Security Engineer</option>
                                <option>Cloud Solutions Architect</option>
                                <option>Penetration Tester</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resume (Upload any file!)</label>
                            <input type="file" name="resume" class="form-control" required>
                            <!-- Hint: No file type validation - upload a PHP shell! -->
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Why Work With Us?</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Competitive Salary</li>
                        <li><i class="fas fa-check text-success"></i> Remote Work Options</li>
                        <li><i class="fas fa-check text-success"></i> Health Benefits</li>
                        <li><i class="fas fa-check text-success"></i> Professional Development</li>
                        <li><i class="fas fa-check text-success"></i> Work-Life Balance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
