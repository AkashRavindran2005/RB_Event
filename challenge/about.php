<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'about');

$member = isset($_GET['member']) ? $_GET['member'] : '';
?>

<div class="section-padding text-center">
    <div class="container-custom">
        <h1 class="display-text mb-4">About CyberTech Solutions</h1>
        <p class="text-secondary" style="font-size: 24px;">Leading the Future of Enterprise Cybersecurity Since 2011</p>
    </div>
</div>

<div class="section-padding" style="background: var(--surface-color);">
    <div class="container-custom">
        <div class="row mb-5">
            <div class="col-md-12">
                <h2 class="mb-4">Our Story</h2>
                <p class="lead text-secondary">Founded in 2011, CyberTech Solutions has grown from a small security
                    consultancy
                    to a globally recognized leader in enterprise cybersecurity and IT infrastructure solutions.</p>
                <p class="text-secondary">With over 15 years of experience protecting Fortune 500 companies, government
                    agencies, and critical
                    infrastructure providers, we have developed deep expertise across all aspects of information
                    security.
                    Our team of certified security professionals brings together decades of combined experience in
                    penetration testing, security architecture, incident response, and compliance.</p>
                <p class="text-secondary">Today, we serve over 500 enterprise clients across 35 countries, protecting
                    billions of dollars in
                    digital assets and maintaining a 99.7% client retention rate. Our commitment to excellence,
                    innovation,
                    and client success has made us the trusted security partner for organizations that cannot afford to
                    compromise on security.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="bento-card text-center p-5 h-100">
                    <i class="fas fa-bullseye fa-3x text-accent mb-4"></i>
                    <h4 class="text-white">Our Mission</h4>
                    <p class="text-secondary">To empower organizations with enterprise-grade security solutions that
                        protect
                        their digital assets, ensure business continuity, and build trust with their customers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bento-card text-center p-5 h-100">
                    <i class="fas fa-eye fa-3x text-accent mb-4"></i>
                    <h4 class="text-white">Our Vision</h4>
                    <p class="text-secondary">To be the world's most trusted cybersecurity partner, setting the standard
                        for
                        excellence in security innovation and client service.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bento-card text-center p-5 h-100">
                    <i class="fas fa-heart fa-3x text-accent mb-4"></i>
                    <h4 class="text-white">Our Values</h4>
                    <p class="text-secondary">Integrity, Excellence, Innovation, Client-First Approach, Continuous
                        Learning,
                        and Collaborative Partnership.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5 py-5">
            <div class="col-md-3 text-center">
                <h2 class="text-accent display-4">500+</h2>
                <p class="text-secondary">Enterprise Clients</p>
            </div>
            <div class="col-md-3 text-center">
                <h2 class="text-accent display-4">15</h2>
                <p class="text-secondary">Years of Excellence</p>
            </div>
            <div class="col-md-3 text-center">
                <h2 class="text-accent display-4">200+</h2>
                <p class="text-secondary">Security Experts</p>
            </div>
            <div class="col-md-3 text-center">
                <h2 class="text-accent display-4">35</h2>
                <p class="text-secondary">Countries Served</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-12">
                <h2 class="mb-4 text-center">Leadership Team</h2>

                <?php if ($member): ?>
                    <div class="bento-card p-4 mb-4">
                        <?php
                        // Check if XSS was triggered (script tag in member param) - show flag FIRST
                        if (stripos($member, '<script') !== false || stripos($member, 'onerror') !== false || stripos($member, 'onload') !== false || stripos($member, 'javascript:') !== false) {
                            echo '<div class="mb-3 p-3 rounded" style="background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.3);">';
                            echo '<p class="text-success mb-0"><strong>🎉 XSS Detected!</strong> Flag: <code>CCEE{xss_r3fl3ct3d_4tt4ck}</code></p>';
                            echo '</div>';
                            logActivity('xss_success', 'XSS payload executed: ' . substr($member, 0, 100));
                        }
                        ?>
                        <h5 class="text-white">Viewing profile: <?php echo $member; ?></h5>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-3">
                <div class="bento-card text-center p-4 h-100 d-flex flex-column">
                    <img src="https://ui-avatars.com/api/?name=Robert+Anderson&size=100&background=2997ff&color=fff"
                        class="rounded-circle mb-3 mx-auto" style="width: 100px; height: 100px;" alt="CEO">
                    <h5 class="text-white">Robert Anderson</h5>
                    <p class="text-secondary small mb-1">Chief Executive Officer</p>
                    <p class="text-secondary small flex-grow-1">Former CISO at Fortune 100 company. 20+ years in
                        cybersecurity leadership.</p>
                    <a href="?member=Robert%20Anderson" class="btn btn-sm btn-outline mt-auto">View Profile</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="bento-card text-center p-4 h-100 d-flex flex-column">
                    <img src="https://ui-avatars.com/api/?name=Jennifer+Chen&size=100&background=2997ff&color=fff"
                        class="rounded-circle mb-3 mx-auto" style="width: 100px; height: 100px;" alt="CTO">
                    <h5 class="text-white">Jennifer Chen</h5>
                    <p class="text-secondary small mb-1">Chief Technology Officer</p>
                    <p class="text-secondary small flex-grow-1">PhD in Computer Science. Former Google security
                        researcher.</p>
                    <a href="?member=Jennifer%20Chen" class="btn btn-sm btn-outline mt-auto">View Profile</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="bento-card text-center p-4 h-100 d-flex flex-column">
                    <img src="https://ui-avatars.com/api/?name=Marcus+Williams&size=100&background=2997ff&color=fff"
                        class="rounded-circle mb-3 mx-auto" style="width: 100px; height: 100px;" alt="CISO">
                    <h5 class="text-white">Marcus Williams</h5>
                    <p class="text-secondary small mb-1">Chief Information Security Officer</p>
                    <p class="text-secondary small flex-grow-1">Former NSA cybersecurity analyst. Expert in threat
                        intelligence.</p>
                    <a href="?member=Marcus%20Williams" class="btn btn-sm btn-outline mt-auto">View Profile</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="bento-card text-center p-4 h-100 d-flex flex-column">
                    <img src="https://ui-avatars.com/api/?name=Sarah+Martinez&size=100&background=2997ff&color=fff"
                        class="rounded-circle mb-3 mx-auto" style="width: 100px; height: 100px;" alt="VP Engineering">
                    <h5 class="text-white">Sarah Martinez</h5>
                    <p class="text-secondary small mb-1">VP of Engineering</p>
                    <p class="text-secondary small flex-grow-1">15+ years building security products. Led engineering at
                        major startups.</p>
                    <a href="?member=Sarah%20Martinez" class="btn btn-sm btn-outline mt-auto">View Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>