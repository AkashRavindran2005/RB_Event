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
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $request_uri = $_SERVER['REQUEST_URI'];
        $timestamp = date('Y-m-d H:i:s');

        $log_query = "INSERT INTO activity_logs (timestamp, ip_address, user_agent, action, details, request_uri) 
                      VALUES ('$timestamp', '$ip', '$user_agent', '$action', '$details', '$request_uri')";
        mysqli_query($conn, $log_query);
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