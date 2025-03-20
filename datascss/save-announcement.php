<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/db_connection.php';

if (!$conn) {
    $error_message = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้';
    file_put_contents(__DIR__ . '/logs/save_error.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    exit(json_encode(['status' => 'error', 'message' => $error_message]));
}

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents(__DIR__ . '/logs/save_input.log', date('Y-m-d H:i:s') . " - Input: " . json_encode($data) . "\n", FILE_APPEND);

$group_id = $data['group_id'] ?? null;
$message = $data['message'] ?? null;
$topic = $data['topic'] ?? null;
$announcement_time = $data['announcement_time'] ?? null;
$announcement_date = $data['announcement_date'] ?? null;

if (!$group_id || !$message || !$topic) {
    $error_message = 'ข้อมูลไม่ครบถ้วน: ' . json_encode($data);
    file_put_contents(__DIR__ . '/logs/save_error.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    exit(json_encode(['status' => 'error', 'message' => $error_message]));
}

// กำหนดตารางตาม topic
switch ($topic) {
    case 'sendOpenOnlyAll':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpenOnlyAll (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendCloseDeposit':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCloseDeposit (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendCloseWithdraw':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCloseWithdraw (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendCloseOnlyAll':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCloseOnlyAll (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendCloseRechargeByOne':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCloseRechargeByOne (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendSystemCutOff':
        $stmt = $conn->prepare("INSERT INTO announcements_sendSystemCutOff (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendSystemMaintenance':
        $stmt = $conn->prepare("INSERT INTO announcements_sendSystemMaintenance (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendNotificationAnnouncement':
        $stmt = $conn->prepare("INSERT INTO announcements_sendNotificationAnnouncement (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendCutoff':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCutoff (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpen':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpen (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpenD':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpenD (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpenW':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpenW (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose_Deposit':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose_Deposit (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose_Withdraw':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose_Withdraw (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendAnnounceDeposit':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnounceDeposit (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendAnnounceWithdraw':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnounceWithdraw (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendNotificationAnnouncement':
        $stmt = $conn->prepare("INSERT INTO announcements_sendNotificationAnnouncement (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendCutoff':
        $stmt = $conn->prepare("INSERT INTO announcements_sendCutoff (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpen':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpen (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpenD':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpenD (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendOpenW':
        $stmt = $conn->prepare("INSERT INTO announcements_sendOpenW (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose_Deposit':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose_Deposit (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendClose_Withdraw':
        $stmt = $conn->prepare("INSERT INTO announcements_sendClose_Withdraw (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendAnnounceDeposit':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnounceDeposit (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;

    case 'sendAnnounceWithdraw':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnounceWithdraw (group_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $group_id, $message);
        break;
    case 'sendAnnouncementAlldayAuto':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnouncementAlldayAuto (group_id, message, announcement_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $group_id, $message, $announcement_time);
        break;
    case 'sendAnnouncementTimeAllday':
        $stmt = $conn->prepare("INSERT INTO announcements_sendAnnouncementTimeAllday (group_id, message, announcement_time, announcement_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $group_id, $message, $announcement_time, $announcement_date);
        break;
    default:
        $error_message = "ไม่พบตารางสำหรับ topic: $topic";
        file_put_contents(__DIR__ . '/logs/save_error.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
        exit(json_encode(['status' => 'error', 'message' => $error_message]));
}

if (!$stmt) {
    $error_message = 'Prepare failed: ' . $conn->error;
    file_put_contents(__DIR__ . '/logs/save_error.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    exit(json_encode(['status' => 'error', 'message' => $error_message]));
}

if ($stmt->execute()) {
    file_put_contents(__DIR__ . '/logs/save_input.log', date('Y-m-d H:i:s') . " - Success for topic: $topic\n", FILE_APPEND);
    echo json_encode(['status' => 'success']);
} else {
    $error_message = 'Execute failed: ' . $stmt->error;
    file_put_contents(__DIR__ . '/logs/save_error.log', date('Y-m-d H:i:s') . " - " . $error_message . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => $error_message]);
}

$stmt->close();
$conn->close();
?>