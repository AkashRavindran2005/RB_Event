-- Vulnerable Site Database Schema
-- CyberTech Solutions CTF Target

-- Users table (intentionally insecure - plaintext passwords)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    credits INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages table (for IDOR vulnerability)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job applications (for file upload)
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    position VARCHAR(100),
    resume_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Activity logs (for log injection)
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME,
    ip_address VARCHAR(45),
    user_agent TEXT,
    action VARCHAR(100),
    details TEXT,
    request_uri VARCHAR(255)
);

-- Shop items (for logic flaw)
CREATE TABLE shop_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price INT,
    image_url VARCHAR(255)
);

-- Seed Data

-- Default users (weak credentials for SQLi discovery)
INSERT INTO users (username, password, email, role, credits) VALUES 
('admin', 'admin123', 'admin@cybertech.com', 'admin', 999999),
('john', 'password123', 'john@cybertech.com', 'user', 100),
('guest', 'guest', 'guest@cybertech.com', 'user', 50);

-- Messages (message id=1 contains IDOR flag)
INSERT INTO messages (name, email, message, is_private) VALUES 
('System', 'admin@cybertech.com', 'CCEE{1d0r_vuln3r4b1l1ty_f0und}', 1),
('John Doe', 'john@example.com', 'I cannot login to my account. Please help!', 0),
('Jane Smith', 'jane@example.com', 'Great service! Very satisfied with the security audit.', 0);

-- Shop items (flag costs 1 million credits - need logic flaw to afford)
INSERT INTO shop_items (name, description, price, image_url) VALUES
('Standard Support', 'Basic email support - 48hr response time', 50, 'assets/support_basic.png'),
('Premium Support', '24/7 Phone and email support', 500, 'assets/support_premium.png'),
('Enterprise Support', 'Dedicated account manager', 5000, 'assets/support_enterprise.png'),
('CTF Flag', 'The ultimate prize - can you afford it?', 1000000, 'assets/flag.png');
