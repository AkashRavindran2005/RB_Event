<?php
include 'includes/config.php';

if (isset($_GET['page'])) {
    $file = $_GET['page'];

    $file = str_replace("/etc/", "", $file);

    if ($file == 'services')
        include('services.php');
    else if ($file == 'careers')
        include('careers.php');
    else if ($file == 'contact')
        include('contact.php');
    else {
        @include($file . ".php");

        if ($file != 'services' && $file != 'careers' && $file != 'contact') {
            @include($file);
        }
    }
    exit();
}
include 'includes/header.php';
?>

<div class="section-padding text-center">
    <div class="container-custom">
        <h1 class="display-text mb-4">Secure. Scalable.<br>Unbreakable.</h1>
        <p class="text-secondary mb-5"
            style="font-size: 24px; max-width: 700px; margin-left: auto; margin-right: auto;">
            Enterprise-grade cybersecurity solutions designed for the modern era.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="services.php" class="btn btn-primary">Our Services</a>
            <a href="contact.php" class="btn btn-outline">Contact Sales</a>
        </div>
    </div>
</div>

<div class="section-padding" style="background-color: #050505;">
    <div class="container-custom">
        <h2 class="mb-5">Why CyberTech?</h2>
        <div class="bento-grid">
            <div class="bento-card">
                <i class="fas fa-shield-alt bento-icon"></i>
                <h3>Red Teaming</h3>
                <p class="text-secondary mt-2 flex-grow-1">Advanced adversary simulation to test your defenses against
                    real-world attack vectors.</p>
                <a href="services.php" class="mt-4 text-accent">Learn more <i class="fas fa-arrow-right small"></i></a>
            </div>

            <div class="bento-card">
                <i class="fas fa-server bento-icon"></i>
                <h3>Blue Teaming</h3>
                <p class="text-secondary mt-2 flex-grow-1">24/7 Security Operations Center (SOC) monitoring and rapid
                    incident response.</p>
                <a href="services.php" class="mt-4 text-accent">Learn more <i class="fas fa-arrow-right small"></i></a>
            </div>

            <div class="bento-card">
                <i class="fas fa-code bento-icon"></i>
                <h3>Secure Audit</h3>
                <p class="text-secondary mt-2 flex-grow-1">Comprehensive source code analysis and infrastructure
                    hardening.</p>
                <a href="services.php" class="mt-4 text-accent">Learn more <i class="fas fa-arrow-right small"></i></a>
            </div>

            <div class="bento-card">
                <i class="fas fa-lock bento-icon"></i>
                <h3>Zero Trust</h3>
                <p class="text-secondary mt-2 flex-grow-1">Implementation of zero trust architecture for maximum
                    security.</p>
                <a href="services.php" class="mt-4 text-accent">Learn more <i class="fas fa-arrow-right small"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Trust Section -->
<div class="section-padding text-center">
    <h2 class="mb-5">Trusted by the best.</h2>
    <div class="container-custom">
        <div class="row align-items-center opacity-50" style="filter: grayscale(100%);">
            <div class="col-3">
                <h3 class="text-secondary">CORP</h3>
            </div>
            <div class="col-3">
                <h3 class="text-secondary">NVDA</h3>
            </div>
            <div class="col-3">
                <h3 class="text-secondary">META</h3>
            </div>
            <div class="col-3">
                <h3 class="text-secondary">GOOG</h3>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>