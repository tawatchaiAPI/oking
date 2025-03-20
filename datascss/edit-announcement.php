<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db_connection.php';
error_log("📥 Starting edit-announcement.php");

$data = json_decode(file_get_contents('php://input'), true);
error_log("📥 Received Data: " . json_encode($data));

$id = $data['id'] ?? null;
$topic = $data['topic'] ?? null;
$message = $data['message'] ?? null;
$announcement_time = $data['announcement_time'] ?? null;
$announcement_date = $data['announcement_date'] ?? null;

if (!$id || !$topic || !$message) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน: ' . json_encode($data)]);
    error_log("❌ Missing required fields: " . json_encode($data));
    exit;
}

$valid_tables = [
    'sendOpenOnlyAll',
    'sendCloseDeposit',
    'sendCloseWithdraw',
    'sendCloseOnlyAll',
    'sendCloseRechargeByOne',
    'sendSystemCutOff',
    'sendSystemMaintenance',
    'sendAnnouncementAlldayAuto',
    'sendAnnouncementTimeAllday',
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

if (!in_array($topic, $valid_tables)) {
    echo json_encode(['status' => 'error', 'message' => "Topic ไม่ถูกต้อง: '$topic'"]);
    error_log("❌ Invalid topic: '$topic'");
    exit;
}

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "Database connection failed: " . $conn->connect_error]);
    error_log("❌ Database connection failed: " . $conn->connect_error);
    exit;
}

$table_name = "announcements_$topic";

if ($topic === 'sendAnnouncementTimeAllday') {
    $sql = "UPDATE $table_name SET message = ?, announcement_time = ?, announcement_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $message, $announcement_time, $announcement_date, $id);
} elseif ($topic === 'sendAnnouncementAlldayAuto') {
    $sql = "UPDATE $table_name SET message = ?, announcement_time = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $message, $announcement_time, $id);
} else {
    $sql = "UPDATE $table_name SET message = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $message, $id);
}

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => "Prepare failed: " . $conn->error]);
    error_log("❌ Prepare failed: " . $conn->error);
    exit;
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'แก้ไขประกาศสำเร็จ']);
        error_log("✅ Edited announcement ID: $id in $table_name");
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบประกาศที่ต้องการแก้ไข']);
        error_log("ℹ️ No announcement found with ID: $id in $table_name");
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
    error_log("❌ Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
exit();
?>