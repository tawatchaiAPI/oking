<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connection.php';

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// ดึงข้อมูลจากตาราง bots
$sql = "SELECT id, bot_name, token, created_at 
        FROM bots";

$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(["status" => "error", "message" => "Query failed: " . $conn->error]);
    exit();
}

$bots = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bots[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $bots], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["status" => "error", "message" => "No bots found"]);
}

$conn->close();
?>