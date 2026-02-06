<?php
session_name('TARGET_SESSION');
session_start();
session_destroy();
// Clear cookies
if (isset($_COOKIE['auth_token'])) {
    unset($_COOKIE['auth_token']);
    setcookie('auth_token', null, -1, '/');
}
if (isset($_COOKIE['session_token'])) {
    unset($_COOKIE['session_token']);
    setcookie('session_token', null, -1, '/');
}
header('Location: index.php');
exit();
?>