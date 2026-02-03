<?php
session_name('TARGET_SESSION');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = getenv('DB_HOST') ? getenv('DB_HOST') : "localhost";
$db_user = "redteam_user";
$db_pass = "root";
$db_name = "cybertech_db";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!function_exists('logActivity')) {
    function logActivity($action, $details = "")
    {
        global $conn;
        $ip = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']);
        $user_agent = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');
        $request_uri = mysqli_real_escape_string($conn, $_SERVER['REQUEST_URI']);
        $action = mysqli_real_escape_string($conn, $action);
        $details = mysqli_real_escape_string($conn, substr($details, 0, 500)); // Limit details length
        $timestamp = date('Y-m-d H:i:s');

        $log_query = "INSERT INTO activity_logs (timestamp, ip_address, user_agent, action, details, request_uri) 
                      VALUES ('$timestamp', '$ip', '$user_agent', '$action', '$details', '$request_uri')";
        @mysqli_query($conn, $log_query); // Suppress errors for logging
    }
}

if (!function_exists('getUserCredits')) {
    function getUserCredits($user_id)
    {
        global $conn;
        $query = "SELECT credits FROM users WHERE id = $user_id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['credits'];
    }
}

// REMOVED: getUserScore and isFlagSolved (Moved to CTF Platform)

// Flag hidden in config comments
// CCEE{c0nf1g_f1l3s_4r3_tr34sur3s}
?>