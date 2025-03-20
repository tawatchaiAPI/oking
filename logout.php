<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log');

session_start();

error_log("Logout attempt for user: " . ($_SESSION['username'] ?? 'unknown'));
error_log("Session before destroy: " . print_r($_SESSION, true));

session_unset();
session_destroy();

error_log("Session after destroy: " . (isset($_SESSION) ? print_r($_SESSION, true) : 'Session destroyed'));

ob_end_clean();
header("Location: login.php");
exit;
?>