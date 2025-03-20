<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db_connection.php';
error_log("📥 Starting delete-announcement.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    error_log("❌ Invalid request method");
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
error_log("📥 Received Data: " . json_encode($data));

if (!isset($data['announcement_id']) || !isset($data['topic'])) {
    echo json_encode(["status" => "error", "message" => "Missing announcement ID or topic"]);
    error_log("❌ Missing announcement ID or topic: " . json_encode($data));
    exit;
}

$announcement_id = (int)$data['announcement_id'];
$topic = $data['topic'];

$valid_topics = [
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

if (!in_array($topic, $valid_topics)) {
    echo json_encode(["status" => "error", "message" => "Invalid topic: '$topic'"]);
    error_log("❌ Invalid topic: '$topic'");
    exit;
}

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    error_log("❌ Database connection failed: " . $conn->connect_error);
    exit;
}

$table_name = "announcements_$topic";
$sql = "DELETE FROM $table_name WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    error_log("❌ Prepare failed: " . $conn->error);
    exit;
}

$stmt->bind_param("i", $announcement_id);
$result = $stmt->execute();

if ($result) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Announcement deleted successfully"]);
        error_log("✅ Deleted announcement ID: $announcement_id from $table_name");
    } else {
        echo json_encode(["status" => "error", "message" => "No announcement found with ID: $announcement_id"]);
        error_log("ℹ️ No announcement found with ID: $announcement_id in $table_name");
    }
} else {
    echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
    error_log("❌ Execute failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
exit();
?>