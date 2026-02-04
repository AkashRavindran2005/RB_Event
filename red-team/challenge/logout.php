<?php
session_start();
session_destroy();
if (isset($_COOKIE['auth_token'])) {
    unset($_COOKIE['auth_token']);
    setcookie('auth_token', null, -1, '/');
}
header('Location: index.php');
exit();
?>