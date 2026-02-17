<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

include '../includes/config.php';

// JWT Vulnerability Challenge
// Multiple JWT weaknesses:
// 1. Weak secret key
// 2. Algorithm confusion (accepts "none")
// 3. No expiry validation
// 4. Hardcoded secret in source

// HARDCODED SECRET - Easy to find and crack
$JWT_SECRET = "supersecretkey123";  // Weak secret!

// Simple JWT implementation (intentionally vulnerable)
function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data)
{
    return base64_decode(strtr($data, '-_', '+/'));
}

function createJWT($payload, $secret)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);

    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode($payload);

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Signature = base64UrlEncode($signature);

    return "$base64Header.$base64Payload.$base64Signature";
}

function verifyJWT($token, $secret)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($base64Header, $base64Payload, $base64Signature) = $parts;

    $header = json_decode(base64UrlDecode($base64Header), true);
    $payload = json_decode(base64UrlDecode($base64Payload), true);

    // VULNERABILITY 1: Algorithm confusion - accepts "none"!
    if (isset($header['alg']) && strtolower($header['alg']) === 'none') {
        logActivity('jwt_bypass', 'Algorithm "none" bypass attempted!');
        return $payload; // No signature verification!
    }

    // VULNERABILITY 2: Weak signature verification
    $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $expectedBase64Signature = base64UrlEncode($expectedSignature);

    // VULNERABILITY 3: Timing attack possible (not using hash_equals)
    if ($base64Signature === $expectedBase64Signature) {
        return $payload;
    }

    return false;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'login':
        if ($method !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        // Check credentials (using prepared statement - no SQLi here)
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'iat' => time()
                // Note: No 'exp' field - tokens never expire!
            ];

            $token = createJWT($payload, $JWT_SECRET);

            logActivity('jwt_login', "JWT issued for user: {$user['username']}");

            echo json_encode([
                'success' => true,
                'token' => $token,
                'message' => 'Login successful. Use this token in the Authorization header.',
                'hint' => 'JWT tokens contain base64 encoded data. Have you tried decoding it?'
            ]);
        } else {
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;

    case 'profile':
        // Get token from Authorization header
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $payload = verifyJWT($token, $JWT_SECRET);

            if ($payload) {
                // Check if admin
                if (isset($payload['role']) && $payload['role'] === 'admin') {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Welcome, Admin!',
                        'data' => $payload,
                        'next_step' => 'Admin access confirmed. Access the restricted endpoint: /api/auth.php?action=admin_data for classified information.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Welcome, user!',
                        'data' => $payload,
                        'hint' => 'Only admins can see the flag. Can you modify your token?'
                    ]);
                }
            } else {
                echo json_encode(['error' => 'Invalid token']);
            }
        } else {
            echo json_encode(['error' => 'No token provided. Use: Authorization: Bearer <token>']);
        }
        break;

    case 'verify':
        // Token verification endpoint (for debugging - exposes info)
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $parts = explode('.', $token);

            if (count($parts) === 3) {
                $header = json_decode(base64UrlDecode($parts[0]), true);
                $payload = json_decode(base64UrlDecode($parts[1]), true);

                echo json_encode([
                    'token_parts' => [
                        'header' => $header,
                        'payload' => $payload,
                        'signature' => $parts[2]
                    ],
                    'hint' => 'Try changing the algorithm to "none" and see what happens...',
                    'another_hint' => 'Or maybe the secret is weak enough to brute force?'
                ]);
            } else {
                echo json_encode(['error' => 'Invalid token format']);
            }
        } else {
            echo json_encode(['error' => 'No token provided']);
        }
        break;

    case 'admin_data':
        // Restricted admin endpoint - requires admin JWT
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $payload = verifyJWT($token, $JWT_SECRET);

            if ($payload && isset($payload['role']) && $payload['role'] === 'admin') {
                logActivity('jwt_flag', 'Admin data accessed via forged JWT');
                echo json_encode([
                    'success' => true,
                    'message' => 'Classified admin data retrieved.',
                    'flag' => 'CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}',
                    'classified' => [
                        'internal_api_keys' => 'REDACTED',
                        'admin_notes' => 'JWT security review still pending...'
                    ]
                ]);
            } else if ($payload) {
                echo json_encode(['error' => 'Access denied. Admin role required.']);
            } else {
                echo json_encode(['error' => 'Invalid token']);
            }
        } else {
            echo json_encode(['error' => 'No token provided. Use: Authorization: Bearer <token>']);
        }
        break;

    default:
        echo json_encode([
            'api' => 'CyberTech JWT Authentication API',
            'version' => '1.0.0-beta',
            'endpoints' => [
                'POST /api/auth.php?action=login' => 'Authenticate and get JWT token',
                'GET /api/auth.php?action=profile' => 'Get profile (requires JWT)',
                'GET /api/auth.php?action=verify' => 'Debug token contents',
                'GET /api/auth.php?action=admin_data' => 'Access classified admin data (admin JWT required)'
            ],
            'example' => [
                'login' => '{"username": "guest", "password": "guest"}',
                'header' => 'Authorization: Bearer <your_token>'
            ],
            'note' => 'This is a beta API. Security review pending...'
        ]);
}

/*
    CTF Challenge: JWT Vulnerabilities

    Multiple attack vectors:

    1. Algorithm Confusion Attack:
       - Decode the token
       - Change header algorithm from "HS256" to "none"  
       - Remove the signature
       - Change role in payload to "admin"

       Example malicious token (base64):
       Header: {"typ":"JWT","alg":"none"}
       Payload: {"user_id":1,"username":"admin","role":"admin","iat":1234567890}
       Signature: (empty)

       Final token: eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ.

    2. Weak Secret Attack:
       - The secret is "supersecretkey123"
       - Can be cracked with john or hashcat
       - Once cracked, forge any token

    3. No Expiration:
       - Tokens don't expire
       - Stolen tokens work forever

    Flag: CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}
*/
?>