<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT g.id AS group_id, g.group_name, 
               COALESCE(GROUP_CONCAT(c.chat_id SEPARATOR ', '), '-') AS chat_ids
        FROM groups g 
        LEFT JOIN chat_ids c ON g.id = c.group_id
        GROUP BY g.id, g.group_name";

$result = $conn->query($sql);

$groups = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // ตรวจสอบ chat_ids หากไม่มีค่าก็ให้เป็น "-"
        $row['chat_ids'] = $row['chat_ids'] == null ? '-' : $row['chat_ids'];
        $groups[] = $row;
    }
} else {
    // หากไม่มีข้อมูลในฐานข้อมูล
    echo json_encode(["status" => "error", "message" => "No groups found"]);
    exit();
}

// ส่ง JSON
echo json_encode($groups, JSON_PRETTY_PRINT);

$conn->close();
?>
