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

$announcements = [];
foreach ($topics as $topic) {
    $table_name = "announcements_$topic";
    // ตรวจสอบโครงสร้างตาราง
    $sql = "SHOW COLUMNS FROM $table_name LIKE 'announcement_time'";
    $has_time = $conn->query($sql) && $conn->query($sql)->num_rows > 0;
    $sql = "SHOW COLUMNS FROM $table_name LIKE 'announcement_date'";
    $has_date = $conn->query($sql) && $conn->query($sql)->num_rows > 0;

    $columns = "id, group_id, message, '$topic' as topic";
    if ($has_time) $columns .= ", announcement_time";
    if ($has_date) $columns .= ", announcement_date";

    $sql = "SELECT $columns FROM $table_name ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
        error_log("✅ Fetched " . $result->num_rows . " rows from $table_name");
    } elseif (!$result) {
        error_log("❌ Query failed for $table_name: " . $conn->error);
    } else {
        error_log("ℹ️ No data found in $table_name");
    }
}

usort($announcements, function($a, $b) {
    return $b['id'] <=> $a['id'];
});

error_log("✅ Fetched " . count($announcements) . " announcements total");
$conn->close();
ob_end_clean();
echo json_encode($announcements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
?>