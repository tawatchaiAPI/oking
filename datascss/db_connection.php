<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log');

$servername = "143.198.192.208";
$username = "vwrkgvedmn";
$password = "vFztSRb4gJ";
$dbname = "vwrkgvedmn";

$conn = new mysqli($servername, $username, $password, $dbname);

date_default_timezone_set('Asia/Bangkok');

if ($conn->connect_error) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");
ob_end_flush();
?>