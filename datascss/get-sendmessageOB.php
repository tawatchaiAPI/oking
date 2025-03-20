<?php
ini_set('display_errors', 0); // ปิดแสดง error ในหน้าเว็บ
ini_set('log_errors', 1);     // เปิดการบันทึก error ลง log
header('Content-Type: application/json; charset=UTF-8');

// เริ่ม output buffering
ob_start();

// เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/db_connection.php';

if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    error_log("❌ Database connection failed: " . $conn->connect_error);
    exit();
}


// รายการ topics ที่ต้องการดึงข้อมูล
$topics = [
    'sendNotificationAnnouncement',
    'sendCutoff',
    'sendOpen',
    'sendOpenD',
    'sendOpenW',
    'sendClose',
    'sendClose_Deposit',
    'sendClose_Withdraw',
    'sendAnnounceDeposit',
    'sendAnnounceWithdraw'
];

// ดึงข้อมูลจากตาราง
$announcements_by_topic = [];
foreach ($topics as $topic) {
    $table_name = "announcements_$topic";
    $sql = "SELECT id, group_id, message, created_at, ? AS topic FROM $table_name ORDER BY id DESC";
    
    // ใช้ prepared statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("❌ Prepare failed for $table_name: " . $conn->error);
        $announcements_by_topic[$topic] = [];
        continue;
    }

    $stmt->bind_param("s", $topic);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $announcements_by_topic[$topic] = $result->fetch_all(MYSQLI_ASSOC);
        error_log("✅ Found " . $result->num_rows . " records for $table_name");
    } else {
        error_log("⚠️ No records found for $table_name");
        $announcements_by_topic[$topic] = [];
    }

    $stmt->close();
}

// สร้าง response
$response = [
    "debug" => "Connected to database: $db_name",
    "data" => $announcements_by_topic
];

error_log("✅ Final data to send: " . json_encode($response));
$conn->close();
ob_end_clean();
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
?>