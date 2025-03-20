<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/db_connection.php';

$raw_data = file_get_contents('php://input');
$json_data = json_decode($raw_data, true);

if ($json_data === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input',
        'raw_data' => $raw_data,
        'json_error' => json_last_error_msg()
    ]);
    exit;
}

$bot_name = trim($json_data['bot_name'] ?? '');
$token = trim($json_data['token'] ?? '');

if (empty($bot_name) || empty($token)) {
    echo json_encode(['status' => 'error', 'message' => 'Bot name or token missing']);
    exit;
}

// ตรวจสอบรูปแบบโทเค็น (ตัวอย่างสำหรับ Telegram Bot Token)
if (!preg_match('/^\d+:[A-Za-z0-9_-]{35}$/', $token)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token format. It should be like "123456:ABC..."']);
    exit;
}

$sql = "INSERT INTO bots (bot_name, token) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $bot_name, $token);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert bot: ' . $stmt->error]);
    exit;
}

$bot_id = $stmt->insert_id;
$stmt->close();

echo json_encode(['status' => 'success', 'bot_id' => $bot_id]);

$conn->close();
?>