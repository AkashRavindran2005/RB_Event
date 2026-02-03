<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'jwt_demo');
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-3">API Authentication Portal</h1>
        <p class="text-secondary mb-5">Test our new JWT-based authentication system (Beta)</p>

        <div class="row g-4">
            <!-- Login Section -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-sign-in-alt me-2"></i>API Login</h3>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label text-secondary">Username</label>
                            <input type="text" id="username" class="form-control" placeholder="Enter username"
                                style="background: #1d1d1f; border-color: #424245;" value="guest">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary">Password</label>
                            <input type="password" id="password" class="form-control" placeholder="Enter password"
                                style="background: #1d1d1f; border-color: #424245;" value="guest">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Get JWT Token
                        </button>
                    </form>

                    <div id="tokenResult" class="mt-4" style="display:none;">
                        <label class="form-label text-success">Your JWT Token:</label>
                        <textarea id="tokenOutput" class="form-control" rows="4" readonly
                            style="background: #0d1117; border-color: #30363d; font-family: monospace; font-size: 12px;"></textarea>
                        <button class="btn btn-outline-secondary btn-sm mt-2" onclick="copyToken()">
                            <i class="fas fa-copy me-1"></i>Copy Token
                        </button>
                    </div>
                </div>
            </div>

            <!-- Token Testing Section -->
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-flask me-2"></i>Test Token</h3>

                    <div class="mb-4">
                        <label class="form-label text-secondary">Paste JWT Token</label>
                        <textarea id="testToken" class="form-control" rows="4"
                            placeholder="Paste your JWT token here..."
                            style="background: #1d1d1f; border-color: #424245; font-family: monospace; font-size: 12px;"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-info" onclick="decodeToken()">
                            <i class="fas fa-search me-1"></i>Decode
                        </button>
                        <button class="btn btn-success" onclick="testProfile()">
                            <i class="fas fa-user me-1"></i>Get Profile
                        </button>
                        <button class="btn btn-warning" onclick="verifyToken()">
                            <i class="fas fa-check me-1"></i>Verify
                        </button>
                    </div>

                    <div id="testResult" class="mt-4 p-3 rounded"
                        style="background: #0d1117; border: 1px solid #30363d; display:none;">
                        <pre id="resultOutput" class="text-light mb-0" style="white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Token Decoder Tool -->
        <div class="bento-card p-5 mt-4">
            <h3 class="text-white mb-4"><i class="fas fa-code me-2"></i>JWT Decoder (Client-Side)</h3>

            <div id="decodedParts" style="display:none;">
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

            <p class="text-secondary mt-3 mb-0" id="decoderHint">
                <i class="fas fa-lightbulb me-2 text-warning"></i>
                Paste a token above and click "Decode" to see its contents.
            </p>
        </div>

        <!-- API Documentation -->
        <div class="bento-card p-5 mt-4">
            <h3 class="text-white mb-4"><i class="fas fa-book me-2"></i>API Documentation</h3>

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
                            <td>Get JWT token with username/password</td>
                        </tr>
                        <tr>
                            <td><code>/api/auth.php?action=profile</code></td>
                            <td><span class="badge bg-primary">GET</span></td>
                            <td>Get user profile (requires JWT)</td>
                        </tr>
                        <tr>
                            <td><code>/api/auth.php?action=verify</code></td>
                            <td><span class="badge bg-primary">GET</span></td>
                            <td>Debug token contents</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Authorization header format: <code>Authorization: Bearer &lt;your_token&gt;</code>
            </p>
        </div>

        <!-- Hints -->
        <div class="bento-card p-4 mt-4">
            <p class="text-secondary mb-0">
                <i class="fas fa-lightbulb me-2 text-warning"></i>
                <strong>Developer Note:</strong> JWT tokens are self-contained and can be decoded by anyone.
                The security comes from the signature verification. Speaking of which,
                have you checked what algorithm we're using?
            </p>
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
                decodeToken();
            } else {
                alert(data.error || 'Login failed');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    });

    function copyToken() {
        const token = document.getElementById('tokenOutput').value;
        navigator.clipboard.writeText(token);
        alert('Token copied to clipboard!');
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
                throw new Error('Invalid JWT format');
            }

            const header = JSON.parse(base64UrlDecode(parts[0]));
            const payload = JSON.parse(base64UrlDecode(parts[1]));

            document.getElementById('headerPart').textContent = JSON.stringify(header, null, 2);
            document.getElementById('payloadPart').textContent = JSON.stringify(payload, null, 2);
            document.getElementById('signaturePart').textContent = parts[2];
            document.getElementById('decodedParts').style.display = 'block';
            document.getElementById('decoderHint').innerHTML = '<i class="fas fa-check-circle me-2 text-success"></i>Token decoded successfully! Notice anything interesting in the header?';

        } catch (error) {
            alert('Failed to decode token: ' + error.message);
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

    async function verifyToken() {
        const token = document.getElementById('testToken').value.trim();
        if (!token) {
            alert('Please enter a token');
            return;
        }

        try {
            const response = await fetch(`${API_BASE}?action=verify`, {
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

<!--
    CTF Hints for JWT Challenge:
    
    1. Decode the token - notice the algorithm is HS256
    2. Look at the verify endpoint hints
    3. Try algorithm confusion attack:
       - Change "alg" to "none"
       - Change "role" to "admin"
       - Remove signature (keep the trailing dot!)
    4. Alternatively, the secret "supersecretkey123" can be cracked
    
    Malicious token to forge:
    Header: {"typ":"JWT","alg":"none"}
    Payload: {"user_id":1,"username":"admin","role":"admin","iat":1234567890}
    
    Base64:
    eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ.
-->

<?php include 'includes/footer.php'; ?>