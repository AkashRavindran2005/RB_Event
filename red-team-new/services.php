<?php 
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'services');
?>

<div class="bg-primary text-white py-5">
    <div class="container text-center">
        <h1 class="display-4">Our Services</h1>
        <p class="lead">Comprehensive IT Security Solutions for Modern Enterprises</p>
    </div>
</div>

<div class="container my-5">
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Cybersecurity Solutions</h3>
                    </div>
                    <p class="text-muted">Comprehensive security assessment and implementation services to protect your organization from evolving cyber threats.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Vulnerability Assessment & Penetration Testing</li>
                        <li><i class="fas fa-check text-success"></i> Security Incident Response</li>
                        <li><i class="fas fa-check text-success"></i> Compliance & Audit (ISO 27001, SOC 2)</li>
                        <li><i class="fas fa-check text-success"></i> Security Architecture Design</li>
                        <li><i class="fas fa-check text-success"></i> 24/7 Security Monitoring (SOC)</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Request Quote</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-cloud fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Cloud Infrastructure</h3>
                    </div>
                    <p class="text-muted">Scalable and secure cloud solutions designed to accelerate your digital transformation journey.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Cloud Migration & Strategy (AWS, Azure, GCP)</li>
                        <li><i class="fas fa-check text-success"></i> Multi-Cloud Architecture</li>
                        <li><i class="fas fa-check text-success"></i> DevSecOps Implementation</li>
                        <li><i class="fas fa-check text-success"></i> Container Orchestration (Kubernetes)</li>
                        <li><i class="fas fa-check text-success"></i> Cloud Cost Optimization</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-network-wired fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Network Security</h3>
                    </div>
                    <p class="text-muted">Enterprise-grade network protection and monitoring solutions to secure your critical infrastructure.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Firewall & IDS/IPS Implementation</li>
                        <li><i class="fas fa-check text-success"></i> Zero Trust Architecture</li>
                        <li><i class="fas fa-check text-success"></i> VPN & Secure Remote Access</li>
                        <li><i class="fas fa-check text-success"></i> Network Segmentation</li>
                        <li><i class="fas fa-check text-success"></i> DDoS Protection</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Get Started</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-shield fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Identity & Access Management</h3>
                    </div>
                    <p class="text-muted">Advanced authentication and authorization solutions to protect your digital identities.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Single Sign-On (SSO) Solutions</li>
                        <li><i class="fas fa-check text-success"></i> Multi-Factor Authentication (MFA)</li>
                        <li><i class="fas fa-check text-success"></i> Privileged Access Management (PAM)</li>
                        <li><i class="fas fa-check text-success"></i> Identity Governance</li>
                        <li><i class="fas fa-check text-success"></i> Directory Services Integration</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Explore Solutions</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-laptop-code fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Application Security</h3>
                    </div>
                    <p class="text-muted">Comprehensive security testing and code review services for web and mobile applications.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Web Application Penetration Testing</li>
                        <li><i class="fas fa-check text-success"></i> Mobile App Security Assessment</li>
                        <li><i class="fas fa-check text-success"></i> Secure Code Review</li>
                        <li><i class="fas fa-check text-success"></i> API Security Testing</li>
                        <li><i class="fas fa-check text-success"></i> SAST/DAST Implementation</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">Schedule Assessment</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-graduation-cap fa-3x text-primary me-3"></i>
                        <h3 class="mb-0">Security Training</h3>
                    </div>
                    <p class="text-muted">Expert-led training programs to build security awareness and technical skills across your organization.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Security Awareness Training</li>
                        <li><i class="fas fa-check text-success"></i> Secure Coding Workshops</li>
                        <li><i class="fas fa-check text-success"></i> Incident Response Training</li>
                        <li><i class="fas fa-check text-success"></i> Phishing Simulation Campaigns</li>
                        <li><i class="fas fa-check text-success"></i> Certification Preparation (CEH, CISSP)</li>
                    </ul>
                    <a href="contact.php" class="btn btn-primary mt-3">View Courses</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Service Search Feature - Vulnerable to Command Injection -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-search"></i> Search Our Service Documentation</h4>
                </div>
                <div class="card-body">
                    <form action="" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search for services, solutions, or documentation..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </form>
                    
                    <?php
                    if (isset($_GET['search'])) {
                        $search_term = $_GET['search'];
                        logActivity('service_search', "Search term: $search_term");
                        
                        // Vulnerable to command injection via exec
                        echo "<div class='mt-3'><h5>Search Results:</h5>";
                        echo "<div class='alert alert-info'>";
                        
                        // Intentionally vulnerable - no input sanitization
                        $command = "grep -i '$search_term' /var/www/html/data/services.txt 2>&1";
                        $output = shell_exec($command);
                        
                        if ($output) {
                            echo "<pre>" . htmlspecialchars($output) . "</pre>";
                        } else {
                            echo "<p>No results found for: " . htmlspecialchars($search_term) . "</p>";
                        }
                        
                        echo "</div></div>";
                        
                        // Flag hint in comment
                        // Try: search=test; cat /etc/passwd
                        // Or: search=test && echo FLAG{s3rv1c3_s34rch_vuln}
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Client Testimonials Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4">What Our Clients Say</h2>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted">"CyberTech Solutions transformed our security posture completely. Their penetration testing revealed critical vulnerabilities we never knew existed."</p>
                    <footer class="blockquote-footer">John Smith, <cite title="Source Title">CISO at TechCorp</cite></footer>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted">"The cloud migration was seamless. Their team's expertise in AWS and security best practices was invaluable."</p>
                    <footer class="blockquote-footer">Sarah Johnson, <cite title="Source Title">CTO at DataFlow Inc</cite></footer>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted">"Outstanding 24/7 SOC services. We've seen a 95% reduction in security incidents since partnering with CyberTech."</p>
                    <footer class="blockquote-footer">Michael Chen, <cite title="Source Title">VP of IT at SecureBank</cite></footer>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card bg-primary text-white text-center">
                <div class="card-body py-5">
                    <h2>Ready to Secure Your Infrastructure?</h2>
                    <p class="lead">Contact our security experts today for a free consultation</p>
                    <a href="contact.php" class="btn btn-light btn-lg mt-3">Get Free Assessment</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
