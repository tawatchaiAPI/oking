<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$group_id = isset($data['group_id']) ? (int)$data['group_id'] : null;
$bot_id = isset($data['bot_id']) ? (int)$data['bot_id'] : null;

if (!$group_id || !$bot_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing group_id or bot_id']);
    exit;
}

// ตรวจสอบว่ามี bot_id นี้ใน bot_settings แล้วหรือยัง
$sql_check = "SELECT COUNT(*) FROM bot_settings WHERE bot_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $bot_id);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    echo json_encode(['status' => 'error', 'message' => 'This bot is already assigned to a group']);
    exit;
}

try {
    $sql = "INSERT INTO bot_settings (bot_id, group_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $bot_id, $group_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Bot setting added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add bot setting: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    if ($conn->errno === 1062) {
        echo json_encode(['status' => 'error', 'message' => 'This bot is already assigned to this group']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>