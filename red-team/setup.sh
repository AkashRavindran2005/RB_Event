#!/bin/bash
DB_USER="redteam_user"
DB_PASS="root"
DB_NAME="cybertech_db"
SQL_FILE="database.sql"

echo "[*] Setting up Red Team CTF Platform..."

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

$MYSQL_CMD <<EOF
DROP DATABASE IF EXISTS $DB_NAME;
CREATE DATABASE $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'localhost';
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo "[*] Database reset and configured."

echo "[*] Importing schema..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

if [ $? -eq 0 ]; then
    echo "[+] Database setup complete!"
else
    echo "[!] Failed to import database."
    exit 1
fi


echo "[*] Starting CTF Platform..."
echo "[+] CTF Platform: http://localhost:8000"
echo "[+] Vulnerable Target: http://localhost:8000/challenge/"
php -S localhost:8000
