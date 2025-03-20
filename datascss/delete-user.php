<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

// รับข้อมูลจาก request
$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';

if (empty($username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username is required']);
    exit;
}

// ลบผู้ใช้จากฐานข้อมูล
$sql = "DELETE FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete user']);
}

$stmt->close();
$conn->close();
?>