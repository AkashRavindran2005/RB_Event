# 🛡️ CyberTech Solutions CTF — Blue Team Investigation Walkthrough

> **Environment:** SOC SIEM Dashboard (Blue Team Web Application)  
> **Total Challenges:** 13 | **Total Points:** 1,750  
> **Flag Format:** `CCEE{...}`  
> **Dashboard Credentials:** `analyst:analyst123`  
> **Data Source:** `fulllogs.log` — 1,660 log entries from Feb 08

---

## Quick Reference

| # | Challenge | Category | Difficulty | Points | Flag |
|---|-----------|----------|:----------:|:------:|------|
| 1 | First Contact | Alert Triage | 🟢 Easy | 100 | `CCEE{00:01:52}` |
| 2 | Identify the Spray | SSH Analysis | 🟢 Easy | 100 | `CCEE{112.95.230.3}` |
| 3 | Attack Surface | Log Classification | 🟢 Easy | 100 | `CCEE{xss}` |
| 4 | File Target | LFI Analysis | 🟢 Easy | 100 | `CCEE{etc_passwd}` |
| 5 | Noise Filter | False Positive ID | 🟢 Easy | 50 | `CCEE{jk2_init}` |
| 6 | System Health | Operational Logs | 🟢 Easy | 50 | `CCEE{disk_space_low}` |
| 7 | Suspicious Domain | SSH Forensics | 🟡 Medium | 200 | `CCEE{ns.marryaldkfaczcz.com}` |
| 8 | Top Attacker | Attacker Profiling | 🟡 Medium | 200 | `CCEE{173.234.31.186_50}` |
| 9 | Privilege Check | RCE Analysis | 🟡 Medium | 150 | `CCEE{www-data}` |
| 10 | Brute Lockout | SSH Investigation | 🟡 Medium | 150 | `CCEE{5.36.59.76}` |
| 11 | Username Harvest | Credential Attack | 🟡 Medium | 150 | `CCEE{webmaster_test9_chen}` |
| 12 | Cloud Attribution | Threat Intel | 🔴 Hard | 250 | `CCEE{ec2_cn-north-1_test9}` |
| 13 | Kill Chain | Attack Reconstruction | 🔴 Hard | 250 | `CCEE{lfi_sqli_ssti_xss_rce}` |

---

# DETAILED INVESTIGATION WALKTHROUGHS

---

## Incident 1 — First Contact (Easy · 100pts)

**Category:** Alert Triage  
**Scenario:** The SOC manager needs to know the exact moment hostile activity began. Determine when the very first attack alert was logged on Feb 08.

### The Question

> Your SIEM has ingested 24 hours of logs from Feb 08. At what exact timestamp (HH:MM:SS) was the **first attack alert** recorded?

### Investigation Steps

1. Log into the Blue Team dashboard.
2. Sort the log feed by **Time** (default / ascending).
3. Look for the earliest `ALERT` entry — ignore system noise like `jk2_init()` and `workerEnv.init()`.
4. The first alert in chronological order is:
   ```
   Feb 08 00:01:52 firewall-02 waf: ALERT CMD injection detected: cmd.exe /c dir in GET request from 203.0.113.88
   ```
5. The timestamp is `00:01:52`.

> **Why this matters:** In real SOC work, establishing the timeline of an incident starts with identifying first contact. This is always step one in any IR playbook.

### 🏁 Flag: `CCEE{00:01:52}`

---

## Incident 2 — Identify the Spray (Easy · 100pts)

**Category:** SSH Analysis  
**Scenario:** Multiple IPs are conducting SSH brute-force attacks against your servers. One attacker has the highest number of failed password attempts against the `root` account. Find them.

### The Question

> Which source IP has the **most failed SSH password attempts** targeting the `root` account?

### Investigation Steps

1. Filter the dashboard by the **SSH** category or search for `Failed password for root` in the logs.
2. Tally attempts per IP:
   | IP | Failed Root Logins |
   |---|---|
   | **112.95.230.3** | 15 |
   | 5.36.59.76 | 10 |
   | 173.234.31.186 | 10 |
   | 52.80.34.196 | 5 |
   | 202.100.179.208 | 5 |
3. IP `112.95.230.3` leads with 15 failed attempts across multiple ports (45378, 47068, 49188).

> **Analysis:** This IP targets multiple SSH ports, indicating an automated scanning/brute-force tool rotating through port numbers.

### 🏁 Flag: `CCEE{112.95.230.3}`

---

## Incident 3 — Attack Surface (Easy · 100pts)

**Category:** Log Classification  
**Scenario:** Management wants to know which attack vector generated the highest number of WAF alerts to prioritize patching.

### The Question

> Which attack type triggered the **most WAF alerts** in the logs?

### Investigation Steps

1. Open the dashboard and examine the **Alerts by Category** panel (or **Attack Vectors** bar chart).
2. Count alerts per attack type:
   | Attack Type | Count |
   |---|---|
   | **XSS** | **180** |
   | RCE | 177 |
   | SSTI | 168 |
   | CMD Injection | 164 |
   | SQLi | 156 |
   | LFI | 155 |
3. XSS has the highest count at 180 alerts.

> **Context:** Cross-Site Scripting being the most frequent attack is common in real environments — it's the most widely attempted web attack globally (OWASP Top 10).

### 🏁 Flag: `CCEE{xss}`

---

## Incident 4 — File Target (Easy · 100pts)

**Category:** LFI Analysis  
**Scenario:** LFI attacks are targeting a specific sensitive file on the server. Identify what the attacker is after.

### The Question

> What file was the attacker attempting to read via the Local File Inclusion vulnerability? Provide the path without leading slashes, using underscores instead of `/`.

### Investigation Steps

1. Filter the dashboard by **LFI** or search for `LFI Detected`.
2. Every LFI alert shows the same payload:
   ```
   GET /vulnerable.php?page=../../../../etc/passwd
   ```
3. The target file is `/etc/passwd` → formatted as `etc_passwd`.

> **Why `/etc/passwd`?** This is the classic LFI proof-of-concept. If an attacker can read this, they can likely read any file — including config files with database credentials.

### 🏁 Flag: `CCEE{etc_passwd}`

---

## Incident 5 — Noise Filter (Easy · 50pts)

**Category:** False Positive Identification  
**Scenario:** Not every log entry is an attack. A junior analyst is overwhelmed by the volume. Help them identify benign system noise.

### The Question

> Which recurring **application initialization message** appears most frequently in the logs and represents normal system operation, NOT an attack?

### Investigation Steps

1. Browse the log feed and look for non-ALERT entries (no "ALERT" keyword).
2. Count recurring system messages:
   | Message Pattern | Count |
   |---|---|
   | `workerEnv.init() ok` | 145 |
   | **`jk2_init() Found child`** | **115** |
   | `session opened/closed` | 50 |
   | `disk space low` | 26 |
3. The most common application initialization message is `jk2_init()` — this is Apache mod_jk (Tomcat connector) initializing worker processes. Completely benign.

> **Key Skill:** Real SOC analysts spend 80% of their time filtering noise. Recognizing false positives is as critical as detecting true positives.

### 🏁 Flag: `CCEE{jk2_init}`

---

## Incident 6 — System Health (Easy · 50pts)

**Category:** Operational Log Analysis  
**Scenario:** While investigating attacks, a good analyst also monitors infrastructure health. Something is silently failing.

### The Question

> What **system warning** appears 26 times in the logs, indicating an infrastructure problem that is NOT an attack but could impact logging capability?

### Investigation Steps

1. Search for `warning` in the logs.
2. You'll find this message on multiple hosts (db-01, web-02, mail-01, app-srv-01):
   ```
   warning: disk space low on /var/log
   ```
3. This appears 26 times across multiple servers. Low disk on `/var/log` means the system could stop logging — a critical operational issue.

> **Impact:** If `/var/log` fills up, new attack logs won't be recorded. Attackers sometimes deliberately fill disk space to hide their tracks.

### 🏁 Flag: `CCEE{disk_space_low}`

---

## Incident 7 — Suspicious Domain (Medium · 200pts)

**Category:** SSH Forensics / Threat Intelligence  
**Scenario:** One of the SSH attackers triggered a `POSSIBLE BREAK-IN ATTEMPT` alert from the SSH daemon itself. This happens when reverse DNS resolution reveals a mismatch — a strong indicator of a spoofed or compromised host.

### The Question

> What is the **fully qualified domain name (FQDN)** that failed reverse DNS mapping, triggering the SSHD `POSSIBLE BREAK-IN ATTEMPT` warning?

### Investigation Steps

1. Search the logs for `POSSIBLE BREAK-IN ATTEMPT`.
2. Multiple entries reveal:
   ```
   reverse mapping checking getaddrinfo for ns.marryaldkfaczcz.com [173.234.31.186] failed - POSSIBLE BREAK-IN ATTEMPT!
   ```
3. The FQDN is `ns.marryaldkfaczcz.com`.
4. **Investigation deeper:** This domain pretends to be a nameserver (`ns.`) but the random-looking domain `marryaldkfaczcz.com` is characteristic of a DGA (Domain Generation Algorithm) or throwaway domain — strong indicator of a compromised/malicious host.

> **Real-World Application:** Reverse DNS mismatches are a common IOC (Indicator of Compromise). Security teams would blocklist both the IP and domain, and search threat intelligence platforms for further context.

### 🏁 Flag: `CCEE{ns.marryaldkfaczcz.com}`

---

## Incident 8 — Top Attacker (Medium · 200pts)

**Category:** Attacker Profiling  
**Scenario:** Your SOC lead wants you to identify the single most active attacker by total log entries to prioritize threat response.

### The Question

> Which IP address generated the **most total log entries** (all types combined), and how many entries did it generate? Format: `IP_count`

### Investigation Steps

1. Use the **Top Attackers** panel on the dashboard, or manually correlate SSH attacker IPs.
2. Count all log entries per SSH attacker IP:
   | IP | Total Entries | Activity Types |
   |---|---|---|
   | **173.234.31.186** | **50** | Auth failure, reverse DNS fail, failed password, invalid user webmaster, connection close |
   | 112.95.230.3 | 40 | Failed password root, auth failure, disconnect |
   | 218.188.2.4 | 38 | Auth failure, check pass user unknown |
   | 220-135-151-1.hinet-ip.hinet.net | 38 | Auth failure for root |
   | 5.36.59.76 | 20 | Failed password root, PAM max retries, disconnect |
   | 52.80.34.196 | 20 | Invalid user test9, failed password, disconnect |
   | 202.100.179.208 | 20 | Invalid user chen, failed password, disconnect |

3. IP `173.234.31.186` leads with 50 total entries. It also shows the most diverse activity: authentication failures, invalid user attempts (webmaster), reverse DNS failures, and POSSIBLE BREAK-IN alerts.

### 🏁 Flag: `CCEE{173.234.31.186_50}`

---

## Incident 9 — Privilege Check (Medium · 150pts)

**Category:** RCE Impact Assessment  
**Scenario:** RCE (Remote Code Execution) alerts show that commands are being executed on the server. Determine what privilege level the attacker is operating under.

### The Question

> What **user account / privilege level** was executing commands during the RCE attempts? This determines the blast radius of the compromise.

### Investigation Steps

1. Filter by **RCE** or search for `RCE Attempt`.
2. Every RCE alert has the same format:
   ```
   ALERT RCE Attempt: uname -a executed by www-data
   ```
3. The commands are running as `www-data`.
4. **Impact Assessment:**
   - `www-data` is the web server process user (Apache/Nginx).
   - This means the attacker has **web-level access**, NOT root.
   - They can read web files, config files, and potentially access databases via web app credentials.
   - They **cannot** install rootkits, modify system binaries, or add users — unless they escalate privileges.

> **Triage Decision:** This is serious but not yet a full compromise. Immediate action: isolate the web server, rotate database credentials, and check for webshells in the uploads directory.

### 🏁 Flag: `CCEE{www-data}`

---

## Incident 10 — Brute Lockout (Medium · 150pts)

**Category:** SSH Investigation  
**Scenario:** PAM (Pluggable Authentication Module) is logging that an attacker exceeded the maximum retry limit. Identify who triggered the lockout.

### The Question

> Which IP address caused PAM to log `ignoring max retries; 6 > 3` — meaning they exceeded the maximum allowed authentication attempts?

### Investigation Steps

1. Search for `max retries` or `PAM service` in the logs.
2. The entries show:
   ```
   PAM service(sshd) ignoring max retries; 6 > 3
   ```
3. These entries originate from SSH session `24227`, which corresponds to root login attempts from `5.36.59.76` port `42393`.
4. Verify by correlating: the same session ID `24227` shows:
   ```
   Failed password for root from 5.36.59.76 port 42393 ssh2
   message repeated 5 times: [ Failed password for root from 5.36.59.76 port 42393 ssh2]
   Disconnecting: Too many authentication failures for root [preauth]
   ```
5. The full brute-force chain for `5.36.59.76` shows: initial failed password → repeated failures (5x) → PAM exceeding max retries → disconnection.

> **Correlation Skill:** This question requires linking the PAM max retry warning to the actual attacker via SSH session identifiers and surrounding log context.

### 🏁 Flag: `CCEE{5.36.59.76}`

---

## Incident 11 — Username Harvest (Medium · 150pts)

**Category:** Credential Stuffing Analysis  
**Scenario:** SSH attackers aren't just trying `root`. They're testing non-existent usernames — a technique used to enumerate valid accounts. Identify all invalid usernames attempted.

### The Question

> List the three **invalid usernames** that SSH attackers attempted, sorted by frequency (most common first), separated by underscores.

### Investigation Steps

1. Search for `Invalid user` in the logs.
2. Extract and count unique usernames:
   | Username | Attempts | Source IP |
   |---|---|---|
   | **webmaster** | 10 | 173.234.31.186 |
   | **test9** | 5 | 52.80.34.196 |
   | **chen** | 5 | 202.100.179.208 |
3. Sorted by frequency: `webmaster_test9_chen`

> **Pattern Analysis:** Each attacker tried a single username — this suggests they're using targeted credential lists rather than random guessing. `webmaster` is a common default, `test9` suggests testing/dev accounts, and `chen` may indicate a targeted/personal attack.

### 🏁 Flag: `CCEE{webmaster_test9_chen}`

---

## Incident 12 — Cloud Attribution (Hard · 250pts)

**Category:** Threat Intelligence / Attribution  
**Scenario:** One SSH attacker's reverse DNS reveals they're operating from a cloud infrastructure provider. This is crucial for incident response — cloud-hosted attacks can be reported to the provider for takedown.

### The Question

> One SSH attacker's hostname resolves to a cloud provider. Identify: (1) the cloud service prefix, (2) the AWS region, and (3) the username they attempted. Format: `prefix_region_username`

### Investigation Steps

1. Search for cloud-related hostnames in SSH logs. Look for entries with `rhost=` containing provider-specific patterns.
2. Find the entry:
   ```
   pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=ec2-52-80-34-196.cn-north-1.compute.amazonaws.com.cn
   ```
3. Break down the hostname:
   - **Cloud prefix:** `ec2` (Amazon Elastic Compute Cloud)
   - **Region:** `cn-north-1` (AWS China – Beijing)
   - **IP embedded:** `52.80.34.196`
4. Cross-reference this IP with other SSH logs:
   ```
   Invalid user test9 from 52.80.34.196
   Failed password for invalid user test9 from 52.80.34.196 port 36060 ssh2
   ```
5. The username attempted was `test9`.
6. **Combined answer:** `ec2_cn-north-1_test9`

> **Threat Intel Value:** The attacker is using AWS China infrastructure (cn-north-1 region). This is actionable intel:
> - Report the instance to AWS Abuse Team
> - The cn-north-1 region is operated by Sinnet, a Chinese company — relevant for geopolitical context
> - The username `test9` suggests automated scanning from disposable cloud instances

### 🏁 Flag: `CCEE{ec2_cn-north-1_test9}`

---

## Incident 13 — Kill Chain (Hard · 250pts)

**Category:** Attack Chain Reconstruction  
**Scenario:** Your SIEM captured alerts for multiple web attack vectors across the day. Reconstruct the complete attack surface by identifying ALL unique web attack types (WAF alerts) in chronological order of their first appearance.

### The Question

> List all **unique web application attack types** (from WAF alerts only, exclude SSH and CMD injection) in the order they **first appeared** chronologically. Use lowercase, separated by underscores.

### Investigation Steps

1. Sort all `ALERT` entries by timestamp.
2. Track the **first occurrence** of each unique web attack category:

   | First Seen | Attack Type | Log Entry |
   |---|---|---|
   | **00:02:29** | LFI | `ALERT LFI Detected: GET /vulnerable.php?page=../../../../etc/passwd` |
   | **00:04:24** | SQLi | `ALERT SQL Injection attempt: UNION SELECT * FROM users from 198.51.100.122` |
   | **00:07:10** | SSTI | `ALERT SSTI Attempt: {{7*7}} payload found in template parameter` |
   | **00:08:03** | XSS | `ALERT XSS Payload detected: <script>alert(1)</script> from 192.0.2.142` |
   | **00:09:58** | RCE | `ALERT RCE Attempt: uname -a executed by www-data` |

3. Chronological order: **LFI → SQLi → SSTI → XSS → RCE**

> **Kill Chain Analysis:** This progression mirrors a real attack lifecycle:
> 1. **LFI** — Attacker reads server-side files (configs, source code) for reconnaissance  
> 2. **SQLi** — Attacker probes for database access using discovered structure  
> 3. **SSTI** — Attacker achieves server-side code execution via template injection  
> 4. **XSS** — Attacker tests client-side injection for lateral movement  
> 5. **RCE** — Attacker achieves full remote code execution — game over  

### 🏁 Flag: `CCEE{lfi_sqli_ssti_xss_rce}`

---

# 📊 POINT DISTRIBUTION COMPARISON

| Tier | Red Team | Blue Team |
|---|---|---|
| 🟢 Easy (50-100 pts) | 6 challenges · 500 pts | 6 challenges · 500 pts |
| 🟡 Medium (150-200 pts) | 5 challenges · 850 pts | 5 challenges · 850 pts |
| 🔴 Hard (250 pts) | 2 challenges · 500 pts | 2 challenges · 500 pts |
| **Total** | **13 challenges · 1,750 pts** | **13 challenges · 1,750 pts** |

---

# 🎯 DESIGN PHILOSOPHY

### Why These Questions Work Competitively

1. **No Ctrl+F Gameplay** — Questions require correlation across multiple log lines, not single keyword matches.

2. **Real Analyst Skills** — Each question maps to a real SOC skill:
   - Timeline reconstruction (Incidents 1, 13)
   - Attacker profiling (Incidents 2, 8, 12)
   - False positive filtering (Incidents 5, 6)
   - Impact assessment (Incident 9)
   - Forensic correlation (Incidents 7, 10, 11)

3. **Difficulty Scales Naturally:**
   - **Easy:** Find a single data point (IP, timestamp, attack type)
   - **Medium:** Correlate across multiple log entries or count occurrences
   - **Hard:** Multi-step analysis requiring threat intel and chain reconstruction

4. **Randomized Question Order** — Questions are numbered by incident, not chronologically through the logs, preventing linear walkthroughs.

5. **Overlapping IP Activity** — The SSH attackers (173.234.31.186 has 50 entries but only SSH, while WAF attackers use different IP ranges) forces careful IP-to-attack-type mapping.
