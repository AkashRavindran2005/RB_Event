<?php
include 'includes/config.php';

// Protected internal documents - requires authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-5">Internal Documents</h1>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-file-alt me-2"></i>Confidential Memos</h3>

                    <div class="p-4 rounded mb-4" style="background: #0d1117; border: 1px solid #30363d;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-danger">CONFIDENTIAL</span>
                            <span class="text-muted small">2024-01-15</span>
                        </div>
                        <p class="text-secondary mb-2">FROM: IT Security Team</p>
                        <p class="text-secondary mb-2">TO: All Staff</p>
                        <p class="text-secondary mb-4">RE: Authentication System Security Audit</p>
                        <hr class="border-secondary">
                        <p class="text-white">
                            Following our recent security audit, we've identified critical issues with the legacy login
                            system
                            (<code>login_legacy.php</code>). The system uses direct string concatenation in SQL queries,
                            making it vulnerable to SQL injection attacks.
                        </p>
                        <p class="text-white">
                            <strong>Action Required:</strong> All staff must migrate to the new authentication portal
                            immediately. The legacy system will be decommissioned next month.
                        </p>
                        <div class="mt-4 p-3 rounded"
                            style="background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.3);">
                            <p class="text-muted small mb-1">Internal Security Reference:</p>
                            <code class="text-success fs-5">CCEE{sql_1nj3ct10n_m4st3r}</code>
                        </div>
                    </div>

                    <div class="p-4 rounded" style="background: #0d1117; border: 1px solid #30363d;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-warning text-dark">INTERNAL</span>
                            <span class="text-muted small">2024-01-10</span>
                        </div>
                        <p class="text-secondary mb-2">FROM: HR Department</p>
                        <p class="text-secondary mb-2">TO: Engineering Team</p>
                        <p class="text-secondary mb-4">RE: Q1 Planning Meeting</p>
                        <hr class="border-secondary">
                        <p class="text-white">
                            Reminder: Q1 planning meeting scheduled for next Monday at 10 AM.
                            Please review the roadmap document before attending.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bento-card p-4 h-auto">
                    <h5 class="text-white mb-3"><i class="fas fa-folder me-2"></i>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="dashboard.php" class="text-accent"><i
                                    class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li class="mb-2"><a href="profile.php" class="text-accent"><i
                                    class="fas fa-user me-2"></i>Profile</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-accent"><i
                                    class="fas fa-shopping-cart me-2"></i>Shop</a></li>
                    </ul>
                </div>

                <div class="bento-card p-4 mt-4 h-auto">
                    <h5 class="text-white mb-3"><i class="fas fa-info-circle me-2"></i>Document Access</h5>
                    <p class="text-secondary small mb-0">
                        You are viewing documents available to your access level.
                        Some documents may be restricted based on your role.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>