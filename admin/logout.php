<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear all cookies related to admin settings
setcookie('admin_page', '', time() - 3600, '/');
setcookie('admin_per_page', '', time() - 3600, '/');
setcookie('contact_status', '', time() - 3600, '/');
setcookie('contact_date', '', time() - 3600, '/');
setcookie('logs_page', '', time() - 3600, '/');
setcookie('logs_per_page', '', time() - 3600, '/');

// Redirect to login page
header('Location: login.php');
exit;
?>