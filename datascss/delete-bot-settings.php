<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing setting ID']);
    exit;
}

$sql = "DELETE FROM bot_settings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Bot setting deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bot setting not found']);
}

$stmt->close();
$conn->close();
?>