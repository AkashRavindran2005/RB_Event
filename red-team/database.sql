
-- 1. CTF Platform Tables (Secure)
CREATE TABLE ctf_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    join_code VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ctf_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    team_id INT DEFAULT NULL,
    FOREIGN KEY (team_id) REFERENCES ctf_teams(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    flag_code VARCHAR(100) UNIQUE,
    points INT,
    description TEXT
);

CREATE TABLE ctf_solves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    challenge_id INT,
    user_id INT, -- Track who solved it
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES ctf_teams(id),
    FOREIGN KEY (challenge_id) REFERENCES challenges(id),
    FOREIGN KEY (user_id) REFERENCES ctf_users(id),
    UNIQUE(team_id, challenge_id) -- Only one solve per team per challenge
);

-- 2. Vulnerable Site Tables (Legacy/Game)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    credits INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    position VARCHAR(100),
    resume_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME,
    ip_address VARCHAR(45),
    user_agent TEXT,
    action VARCHAR(100),
    details TEXT,
    request_uri VARCHAR(255)
);

CREATE TABLE shop_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price INT,
    image_url VARCHAR(255)
);

-- Seed Data

-- Challenges
INSERT INTO challenges (name, flag_code, points, description) VALUES
('SQL Injection', 'FLAG{sql_1nj3ct10n_m4st3r}', 100, 'Legacy systems often have vulnerabilities. Check the footer for hints.'),
('HTTP Headers', 'FLAG{h34d3r5_t3ll_s3cr3ts}', 50, 'Developers sometimes hide information in HTTP headers. Check the dashboard.'),
('Config Discovery', 'FLAG{c0nf1g_f1l3s_4r3_tr34sur3s}', 150, 'Configuration files often contain secrets. Use LFI to read source code.'),
('IDOR', 'FLAG{1d0r_vuln3r4b1l1ty_f0und}', 100, 'Not all messages are meant for you. Try accessing different message IDs.'),
('Logic Flaw', 'FLAG{l0g1c_fl4w_sh0pp1ng_spr33}', 200, 'The shop has a logic bug. Think about edge cases with quantities.'),
('Cookie Manipulation', 'FLAG{c00k13_m0nst3r_4dm1n}', 150, 'PHP Object Injection can be dangerous. Check how sessions are serialized.'),
('Backup File', 'FLAG{b4ckup_f1l3s_l34k_s3cr3ts}', 50, 'Developers sometimes forget to delete backup files. Look for common extensions.'),
('Command Injection', 'FLAG{c0mm4nd_1nj3ct10n_r00t}', 200, 'The search API executes shell commands. Break out of the grep query.'),
('Console Leak', 'FLAG{c0ns0l3_l0g_l34k}', 25, 'Always check the browser console for developer mistakes.'),
('LocalStorage', 'FLAG{l0c4l_st0r4g3_s3cr3ts}', 25, 'Check what data is stored in localStorage after browsing the site.'),
('Client XSS', 'FLAG{cl13nt_s1d3_xss}', 75, 'The debug parameter in the URL might be interesting. Check main.js.'),
('Dev Mode', 'FLAG{d3v_m0d3_3xp0s3d}', 50, 'Developers often leave debug endpoints exposed. Check the JavaScript.');

-- Vulnerable Site Data
INSERT INTO users (username, password, email, role, credits) VALUES 
('admin', 'admin123', 'admin@cybertech.com', 'admin', 999999),
('john', 'password123', 'john@cybertech.com', 'user', 100);

INSERT INTO messages (name, email, message, is_private) VALUES 
('System', 'admin@cybertech.com', 'FLAG{1d0r_vuln3r4b1l1ty_f0und}', 1),
('John Doe', 'john@example.com', 'I cannot login to my account.', 0);

INSERT INTO shop_items (name, description, price, image_url) VALUES
('Standard Support', 'Basic email support', 50, 'assets/support_basic.png'),
('Premium Support', '24/7 Phone support', 500, 'assets/support_premium.png'),
('CTF Flag', 'The ultimate prize', 1000000, 'assets/flag.png');
