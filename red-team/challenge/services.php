<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'services');
?>

<div class="section-padding text-center">
    <div class="container-custom">
        <h1 class="display-text mb-4">Our Services</h1>
        <p class="text-secondary" style="font-size: 24px;">Comprehensive IT Security Solutions for Modern Enterprises
        </p>
    </div>
</div>

<div class="section-padding" style="background: var(--surface-color);">
    <div class="container-custom">

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="bento-card">
                    <i class="fas fa-shield-alt bento-icon"></i>
                    <h3>Cybersecurity Solutions</h3>
                    <p class="text-secondary mt-3">Comprehensive security assessment and implementation services to
                        protect your organization from evolving cyber threats.</p>
                    <ul class="list-unstyled mt-4 text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Vulnerability Assessment &
                            Pentesting</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Security Incident Response</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Compliance & Audit (ISO 27001)
                        </li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-4">Request Quote</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bento-card">
                    <i class="fas fa-cloud bento-icon"></i>
                    <h3>Cloud Infrastructure</h3>
                    <p class="text-secondary mt-3">Scalable and secure cloud solutions designed to accelerate your
                        digital transformation journey.</p>
                    <ul class="list-unstyled mt-4 text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cloud Migration (AWS, Azure)
                        </li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> DevSecOps Implementation</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Container Orchestration</li>
                    </ul>
                    <a href="contact.php" class="btn btn-outline mt-4">Learn More</a>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="bento-card">
                    <i class="fas fa-network-wired bento-icon"></i>
                    <h3>Network Security</h3>
                    <p class="text-secondary mt-3">Enterprise-grade network protection and monitoring solutions to
                        secure your critical infrastructure.</p>
                    <ul class="list-unstyled mt-4 text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Firewall & IDS/IPS</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Zero Trust Architecture</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> DDoS Protection</li>
                    </ul>
                    <a href="contact.php" class="btn btn-outline mt-4">Get Started</a>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bento-card">
                    <i class="fas fa-user-shield bento-icon"></i>
                    <h3>Identity Management</h3>
                    <p class="text-secondary mt-3">Advanced authentication and authorization solutions to protect your
                        digital identities.</p>
                    <ul class="list-unstyled mt-4 text-secondary">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Single Sign-On (SSO)</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Multi-Factor Authentication</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Privileged Access (PAM)</li>
                    </ul>
                    <a href="contact.php" class="btn btn-outline mt-4">Explore</a>
                </div>
            </div>
        </div>

        <!-- Service Search (Vulnerable) -->
        <div class="row pt-5">
            <div class="col-md-12">
                <div class="bento-card p-5 text-center">
                    <h2 class="mb-3 text-white"><i class="fas fa-search me-2"></i> Documentation Search</h2>
                    <p class="text-secondary mb-4">Search through our technical service manuals and security guidelines.
                    </p>

                    <form action="" method="GET" class="w-100" style="max-width: 600px; margin: 0 auto;">
                        <div class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search docs..."
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>

                    <?php
                    if (isset($_GET['search'])) {
                        $search_term = $_GET['search'];
                        // Vulnerable
                        echo "<div class='mt-4 text-start bg-black p-3 rounded border border-secondary'>";
                        echo "<h5 class='text-white'>Results:</h5>";
                        $command = "grep -i '$search_term' /var/www/html/data/services.txt 2>&1";
                        $output = shell_exec($command);

                        if ($output) {
                            echo "<pre class='text-success mb-0'>" . htmlspecialchars($output) . "</pre>";
                        } else {
                            echo "<p class='text-secondary mb-0'>No results found.</p>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>