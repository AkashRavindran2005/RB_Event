<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'api_portal');
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-3">Developer Portal</h1>
        <p class="text-secondary mb-5">Access our API for integrating CyberTech security services into your
            applications.</p>

        <div class="row g-4">
            <!-- Login Section -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-sign-in-alt me-2"></i>API Authentication</h3>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label text-secondary">API Username</label>
                            <input type="text" id="username" class="form-control" placeholder="Enter username"
                                value="guest">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary">API Key</label>
                            <input type="password" id="password" class="form-control" placeholder="Enter API key"
                                value="guest">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Authenticate
                        </button>
                    </form>

                    <div id="tokenResult" class="mt-4" style="display:none;">
                        <label class="form-label text-success">Access Token:</label>
                        <textarea id="tokenOutput" class="form-control" rows="3" readonly
                            style="background: #0d1117; border-color: #30363d; font-family: monospace; font-size: 12px;"></textarea>
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyToken()">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Test Section -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-flask me-2"></i>Test Endpoint</h3>

                    <div class="mb-4">
                        <label class="form-label text-secondary">Bearer Token</label>
                        <textarea id="testToken" class="form-control" rows="3" placeholder="Paste your token here..."
                            style="font-family: monospace; font-size: 12px;"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="testProfile()">
                            <i class="fas fa-user me-1"></i>Get Profile
                        </button>
                        <button class="btn btn-info" onclick="decodeToken()">
                            <i class="fas fa-search me-1"></i>Decode
                        </button>
                    </div>

                    <div id="testResult" class="mt-4 p-3 rounded"
                        style="background: #0d1117; border: 1px solid #30363d; display:none;">
                        <pre id="resultOutput" class="text-light mb-0" style="white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="bento-card p-5 mt-4">
            <h3 class="text-white mb-4"><i class="fas fa-book me-2"></i>API Reference</h3>

            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Endpoint</th>
                            <th>Method</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>/api/auth.php?action=login</code></td>
                            <td><span class="badge bg-success">POST</span></td>
                            <td>Authenticate and receive access token</td>
                        </tr>
                        <tr>
                            <td><code>/api/auth.php?action=profile</code></td>
                            <td><span class="badge bg-primary">GET</span></td>
                            <td>Get authenticated user profile</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Header format: <code>Authorization: Bearer &lt;token&gt;</code>
            </p>
        </div>

        <!-- Token Decoder -->
        <div class="bento-card p-5 mt-4" id="decodedParts" style="display:none;">
            <h3 class="text-white mb-4"><i class="fas fa-code me-2"></i>Token Details</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label text-danger">Header</label>
                    <pre id="headerPart" class="p-3 rounded" style="background: #1c1c1e; color: #ff6b6b;"></pre>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-info">Payload</label>
                    <pre id="payloadPart" class="p-3 rounded" style="background: #1c1c1e; color: #4ecdc4;"></pre>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-warning">Signature</label>
                    <pre id="signaturePart" class="p-3 rounded"
                        style="background: #1c1c1e; color: #feca57; word-break: break-all;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const API_BASE = 'api/auth.php';

    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch(`${API_BASE}?action=login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (data.token) {
                document.getElementById('tokenOutput').value = data.token;
                document.getElementById('testToken').value = data.token;
                document.getElementById('tokenResult').style.display = 'block';
            } else {
                alert(data.error || 'Authentication failed');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });

    function copyToken() {
        const token = document.getElementById('tokenOutput').value;
        navigator.clipboard.writeText(token);
        alert('Token copied!');
    }

    function base64UrlDecode(str) {
        str = str.replace(/-/g, '+').replace(/_/g, '/');
        while (str.length % 4) str += '=';
        return atob(str);
    }

    function decodeToken() {
        const token = document.getElementById('testToken').value.trim();
        if (!token) {
            alert('Please enter a token');
            return;
        }

        try {
            const parts = token.split('.');
            if (parts.length !== 3) {
                throw new Error('Invalid token format');
            }

            const header = JSON.parse(base64UrlDecode(parts[0]));
            const payload = JSON.parse(base64UrlDecode(parts[1]));

            document.getElementById('headerPart').textContent = JSON.stringify(header, null, 2);
            document.getElementById('payloadPart').textContent = JSON.stringify(payload, null, 2);
            document.getElementById('signaturePart').textContent = parts[2];
            document.getElementById('decodedParts').style.display = 'block';

        } catch (error) {
            alert('Failed to decode: ' + error.message);
        }
    }

    async function testProfile() {
        const token = document.getElementById('testToken').value.trim();
        if (!token) {
            alert('Please enter a token');
            return;
        }

        try {
            const response = await fetch(`${API_BASE}?action=profile`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            const data = await response.json();
            document.getElementById('resultOutput').textContent = JSON.stringify(data, null, 2);
            document.getElementById('testResult').style.display = 'block';

        } catch (error) {
            document.getElementById('resultOutput').textContent = 'Error: ' + error.message;
            document.getElementById('testResult').style.display = 'block';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>