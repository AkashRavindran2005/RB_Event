<?php if (isset($_SESSION['obj_injection_solved']) && $_SESSION['obj_injection_solved'] === true): ?>
    <div class="alert alert-success mb-4" style="background: rgba(0,255,0,0.1); border: 1px solid rgba(0,255,0,0.3);">
        <strong>🎉 Congratulations!</strong> You exploited PHP Object Injection to reach the admin settings.<br>
        Flag: <code>CCEE{c00k13_m0nst3r_4dm1n}</code>
    </div>
    <?php unset($_SESSION['obj_injection_solved']); ?>
<?php endif; ?>
<h5 class="text-white mb-3">Global Settings</h5>
<form>
    <div class="mb-3">
        <label class="form-label text-secondary small">SITE NAME</label>
        <input type="text" class="form-control bg-dark text-white border-secondary" value="CyberTech Solutions"
            disabled>
    </div>
    <div class="mb-3">
        <label class="form-label text-secondary small">MAINTENANCE MODE</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="maintenanceMode" disabled>
            <label class="form-check-label text-white" for="maintenanceMode">Enabled</label>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label text-secondary small">DEBUG LOGGING</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="debugLog" checked disabled>
            <label class="form-check-label text-white" for="debugLog">Enabled (Verbose)</label>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label text-secondary small">API KEYS</label>
        <input type="password" class="form-control bg-dark text-white border-secondary" value="************************"
            disabled>
        <div class="form-text text-secondary">Keys are hidden for security.</div>
    </div>
    <button type="button" class="btn btn-primary" disabled>Save Changes</button>
</form>
<div class="alert alert-warning mt-4"><i class="fas fa-exclamation-triangle me-2"></i> Settings are read-only in this
    demo environment.</div>