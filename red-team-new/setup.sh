#!/bin/bash

# CyberTech Vulnerable Site Setup
# This sets up the intentionally vulnerable website for CTF practice

DB_USER="redteam_user"
DB_PASS="root"
DB_NAME="cybertech_db"
SQL_FILE="database.sql"

echo "[*] Setting up CyberTech Vulnerable Site..."

# Check if MySQL/MariaDB is running
if ! pgrep -x "mysqld" >/dev/null && ! pgrep -x "mariadbd" >/dev/null; then
    echo "[!] MySQL/MariaDB is not running. Please start it first."
    echo "    Try: sudo systemctl start mariadb"
    exit 1
fi

echo "[*] Configuring Database..."

MYSQL_CMD="mysql -u root"
if ! $MYSQL_CMD -e "SELECT 1" &>/dev/null; then
    echo "[!] Cannot connect to MySQL as root without password. Trying sudo..."
    MYSQL_CMD="sudo mysql -u root"
fi

# Recreate Database and User
$MYSQL_CMD <<EOF
DROP DATABASE IF EXISTS $DB_NAME;
CREATE DATABASE $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'localhost';
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "[*] Database reset and configured."

# Import SQL
echo "[*] Importing schema..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

if [ $? -eq 0 ]; then
    echo "[+] Database setup complete!"
else
    echo "[!] Failed to import schema"
    exit 1
fi

# Create temp directory for command injection flag
mkdir -p /tmp/ctf_data
echo "CCEE{c0mm4nd_1nj3ct10n_r00t}" > /tmp/ctf_data/flag.txt
echo -e "red-teaming\nblue-teaming\npenetration-testing\nsecurity-audit\nincident-response" > /tmp/ctf_data/services.txt
echo "[+] CTF data files created."

echo ""
echo "=========================================="
echo "[*] Starting Vulnerable Site..."
echo "[+] URL: http://localhost:8000/challenge/"
echo "=========================================="
echo ""
echo "Flags available (12 total, 1175 pts):"
echo "  - SQL Injection (100 pts)"
echo "  - HTTP Headers (50 pts)"
echo "  - Config Discovery (150 pts)"
echo "  - IDOR (100 pts)"
echo "  - Logic Flaw (200 pts)"
echo "  - Cookie Manipulation (150 pts)"
echo "  - Backup File (50 pts)"
echo "  - Command Injection (200 pts)"
echo "  - Console Leak (25 pts)"
echo "  - LocalStorage (25 pts)"
echo "  - Client XSS (75 pts)"
echo "  - Dev Mode (50 pts)"
echo ""

php -S localhost:8000
