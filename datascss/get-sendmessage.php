<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
header('Content-Type: application/json; charset=UTF-8');
ob_start();
require_once __DIR__ . '/db_connection.php';

if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    error_log("❌ Database connection failed: " . $conn->connect_error);
    exit();
}

$topics = [
    'sendOpenOnlyAll',
    'sendCloseDeposit',
    'sendCloseWithdraw',
    'sendCloseOnlyAll',
    'sendCloseRechargeByOne',
    'sendSystemCutOff',
    'sendSystemMaintenance',
    'sendAnnouncementAlldayAuto'
];

$announcements_by_topic = []; // เปลี่ยนเป็น associative array แยกตาม topic
foreach ($topics as $topic) {
    $table_name = "announcements_$topic";
    $sql = "SELECT id, group_id, message, created_at, '$topic' as topic FROM $table_name ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $announcements_by_topic[$topic] = [];
        while ($row = $result->fetch_assoc()) {
            $announcements_by_topic[$topic][] = $row;
        }
    } elseif (!$result) {
        error_log("❌ Query failed for $table_name: " . $conn->error);
        $announcements_by_topic[$topic] = [];
    }
}

error_log("✅ Fetched announcements for " . count($announcements_by_topic) . " topics");
$conn->close();
ob_end_clean();
echo json_encode($announcements_by_topic, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
?>