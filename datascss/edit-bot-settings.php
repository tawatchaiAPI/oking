<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : null;
$bot_id = isset($data['bot_id']) ? (int)$data['bot_id'] : null;
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : null;

if (!$id || !$bot_id || !$group_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$sql = "UPDATE bot_settings SET bot_id = ?, group_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $bot_id, $group_id, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Bot setting updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bot setting not found or no changes made']);
}

$stmt->close();
$conn->close();
?>