<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db_connection.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
    exit;
}

// รับข้อมูล JSON
$raw_data = file_get_contents('php://input');
$json_data = json_decode($raw_data, true);

if (!$json_data) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูล JSON ไม่ถูกต้อง']);
    exit;
}

// ดึงค่าจาก JSON (ใช้ชื่อให้ตรงกับ JS)
$id = $json_data['id'] ?? null;
$bot_name = $json_data['bot_name'] ?? null;
$token = $json_data['token'] ?? null;

if (!$id || !$bot_name || !$token) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน (id, bot_name, token)']);
    exit;
}

try {
    // อัปเดตตาราง bots
    $sql = "UPDATE bots SET bot_name = ?, token = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $bot_name, $token, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'แก้ไขบอทสำเร็จ']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่มีการเปลี่ยนแปลงข้อมูลหรือไม่พบ ID']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'การอัปเดตล้มเหลว: ' . $stmt->error]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage()]);
}

$conn->close();
?>