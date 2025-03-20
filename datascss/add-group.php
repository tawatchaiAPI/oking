<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/db_connection.php';

// อ่านข้อมูลจาก JSON ที่ถูกส่งมาจาก JavaScript
$raw_data = file_get_contents('php://input');
$json_data = json_decode($raw_data, true);

// ตรวจสอบว่าได้รับข้อมูล JSON ถูกต้องหรือไม่
if ($json_data === null) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input',
        'raw_data' => $raw_data,
        'json_error' => json_last_error_msg()
    ]);
    exit;
}

$group_name = trim($json_data['group_name'] ?? '');
$chat_ids = explode(',', trim($json_data['chat_ids'] ?? ''));

// ตรวจสอบข้อมูลที่จำเป็น
if (empty($group_name) || empty($chat_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'Group name or chat IDs missing']);
    exit;
}

// สั่ง INSERT ข้อมูลกลุ่มใหม่
$sql = "INSERT INTO groups (group_name) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $group_name);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to insert group: ' . $stmt->error]);
    exit;
}

$group_id = $stmt->insert_id; // ได้รับ group_id หลังการ insert
$stmt->close();

// สั่ง INSERT chat_ids
$chat_sql = "INSERT INTO chat_ids (group_id, chat_id) VALUES (?, ?)";
$chat_stmt = $conn->prepare($chat_sql);

foreach ($chat_ids as $chat_id) {
    $chat_id = trim($chat_id);
    if (!empty($chat_id)) {
        $chat_stmt->bind_param("is", $group_id, $chat_id);
        if (!$chat_stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert chat ID: ' . $chat_stmt->error]);
            exit;
        }
    }
}
$chat_stmt->close();

// ส่งผลลัพธ์กลับไปที่ JavaScript
echo json_encode(['status' => 'success', 'group_id' => $group_id]);

$conn->close();
?>
