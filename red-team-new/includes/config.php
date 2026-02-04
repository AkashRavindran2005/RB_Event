<?php
session_start();

// Database credentials - Intentionally vulnerable
$db_host = "localhost";
$db_user = "root";
$db_pass = "password123";
$db_name = "cybertech_db";

// Vulnerable connection - no prepared statements
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Log all requests for Blue Team
function logActivity($action, $details = "") {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $timestamp = date('Y-m-d H:i:s');
    
    $log_query = "INSERT INTO activity_logs (timestamp, ip_address, user_agent, action, details, request_uri) 
                  VALUES ('$timestamp', '$ip', '$user_agent', '$action', '$details', '$request_uri')";
    mysqli_query($conn, $log_query);
}

// Flag hidden in config comments
// FLAG{c0nf1g_f1l3s_4r3_tr34sur3s}
?>
