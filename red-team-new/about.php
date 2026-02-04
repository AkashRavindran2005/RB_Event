<?php 
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'about');

// Reflected XSS vulnerability in team member profile
$member = isset($_GET['member']) ? $_GET['member'] : '';
?>

<div class="bg-gradient text-white py-5">
    <div class="container text-center">
        <h1 class="display-4">About CyberTech Solutions</h1>
        <p class="lead">Leading the Future of Enterprise Cybersecurity Since 2011</p>
    </div>
</div>

<div class="container my-5">
    <!-- Company Overview -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="mb-4">Our Story</h2>
            <p class="lead text-muted">Founded in 2011, CyberTech Solutions has grown from a small security consultancy to a globally recognized leader in enterprise cybersecurity and IT infrastructure solutions.</p>
            <p>With over 15 years of experience protecting Fortune 500 companies, government agencies, and critical infrastructure providers, we have developed deep expertise across all aspects of information security. Our team of certified security professionals brings together decades of combined experience in penetration testing, security architecture, incident response, and compliance.</p>
            <p>Today, we serve over 500 enterprise clients across 35 countries, protecting billions of dollars in digital assets and maintaining a 99.7% client retention rate. Our commitment to excellence, innovation, and client success has made us the trusted security partner for organizations that cannot afford to compromise on security.</p>
        </div>
    </div>
    
    <!-- Mission, Vision, Values -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-bullseye fa-4x text-primary mb-3"></i>
                    <h4>Our Mission</h4>
                    <p class="text-muted">To empower organizations with enterprise-grade security solutions that protect their digital assets, ensure business continuity, and build trust with their customers.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-4x text-primary mb-3"></i>
                    <h4>Our Vision</h4>
                    <p class="text-muted">To be the world's most trusted cybersecurity partner, setting the standard for excellence in security innovation and client service.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-heart fa-4x text-primary mb-3"></i>
                    <h4>Our Values</h4>
                    <p class="text-muted">Integrity, Excellence, Innovation, Client-First Approach, Continuous Learning, and Collaborative Partnership.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="row mb-5 bg-light py-5">
        <div class="col-md-3 text-center">
            <h2 class="text-primary display-4">500+</h2>
            <p class="text-muted">Enterprise Clients</p>
        </div>
        <div class="col-md-3 text-center">
            <h2 class="text-primary display-4">15</h2>
            <p class="text-muted">Years of Excellence</p>
        </div>
        <div class="col-md-3 text-center">
            <h2 class="text-primary display-4">200+</h2>
            <p class="text-muted">Security Experts</p>
        </div>
        <div class="col-md-3 text-center">
            <h2 class="text-primary display-4">35</h2>
            <p class="text-muted">Countries Served</p>
        </div>
    </div>
    
    <!-- Leadership Team -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h2 class="mb-4 text-center">Leadership Team</h2>
            
            <?php if ($member): ?>
                <!-- XSS Vulnerability - No sanitization -->
                <div class="alert alert-info">
                    <h5>Viewing profile: <?php echo $member; ?></h5>
                    <!-- Try: ?member=<img src=x onerror=alert('XSS')> -->
                    <!-- Or: ?member=<script>alert(document.cookie)</script> -->
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <img src="https://ui-avatars.com/api/?name=Robert+Anderson&size=150&background=0D6EFD&color=fff" class="rounded-circle mb-3" alt="CEO">
                    <h5>Robert Anderson</h5>
                    <p class="text-muted">Chief Executive Officer</p>
                    <p class="small">Former CISO at Fortune 100 company. 20+ years in cybersecurity leadership.</p>
                    <a href="?member=Robert%20Anderson" class="btn btn-sm btn-outline-primary">View Profile</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <img src="https://ui-avatars.com/api/?name=Jennifer+Chen&size=150&background=0D6EFD&color=fff" class="rounded-circle mb-3"
