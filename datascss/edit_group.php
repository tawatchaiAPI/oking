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
$group_name = $json_data['group_name'] ?? null;
$chat_ids = $json_data['chat_ids'] ?? null;

if (!$id || !$group_name || !$chat_ids) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน (id, group_name, chat_ids)']);
    exit;
}

$updateSuccess = false;

try {
    $conn->begin_transaction();

    // อัปเดตตาราง groups
    $sql = "UPDATE groups SET group_name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $group_name, $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $updateSuccess = true;
    }
    $stmt->close();

    // ลบ chat_ids เดิม
    $sqlDelete = "DELETE FROM chat_ids WHERE group_id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param('i', $id);
    $stmtDelete->execute();
    $stmtDelete->close();

    // เพิ่ม chat_ids ใหม่
    $chat_ids_array = explode(',', $chat_ids);
    foreach ($chat_ids_array as $chat) {
        $chat = trim($chat);
        if (!empty($chat)) {
            $sql = "INSERT INTO chat_ids (group_id, chat_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('is', $id, $chat);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $updateSuccess = true;
            }
            $stmt->close();
        }
    }

    $conn->commit();

    if ($updateSuccess) {
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขกลุ่มสำเร็จ']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่มีการเปลี่ยนแปลงข้อมูล']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage()]);
}

$conn->close();
?>