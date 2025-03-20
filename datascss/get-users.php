<?php
// ตั้งค่า header ให้ส่งข้อมูลเป็น JSON
header('Content-Type: application/json');

// รวมไฟล์การเชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/db_connection.php';

// ดึงข้อมูลจากตาราง users
$sql = "SELECT username, role FROM users";
$result = $conn->query($sql);

// สร้าง array สำหรับเก็บข้อมูล
$data = [];

if ($result->num_rows > 0) {
    // เก็บข้อมูลแต่ละแถวลงใน array
    while($row = $result->fetch_assoc()) {
        $data[] = [
            'username' => $row['username'],
            'role' => $row['role']
        ];
    }
} else {
    $data = []; // หรือจะส่งข้อความแจ้งว่าไม่พบข้อมูลก็ได้
}

// ปิดการเชื่อมต่อ
$conn->close();

// ส่งข้อมูลในรูปแบบ JSON
echo json_encode($data, JSON_PRETTY_PRINT);
?>