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