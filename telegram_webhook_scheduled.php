<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errorV2.log');
header('Content-Type: application/json');

$log_file = __DIR__ . '/logs/telegram_webhook_scheduled_log.txt';

if (!is_writable($log_file) && !file_exists($log_file)) {
    if (!file_put_contents($log_file, "Initial log file creation\n")) {
        exit(json_encode(['status' => 'error', 'message' => 'Cannot create log file: ' . $log_file]));
    }
    chmod($log_file, 0644);
} elseif (!is_writable($log_file)) {
    exit(json_encode(['status' => 'error', 'message' => 'Log file is not writable: ' . $log_file]));
}

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] " . mb_convert_encoding($message, 'UTF-8', 'auto') . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

writeLog("File accessed");

if (!file_exists(__DIR__ . '/datascss/db_connection.php')) {
    writeLog("db_connection.php not found");
    exit(json_encode(['status' => 'error', 'message' => 'datascss/db_connection.php not found']));
}
require_once __DIR__ . '/datascss/db_connection.php';

if ($conn->connect_error) {
    writeLog("Database connection failed: " . $conn->connect_error);
    exit(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}
writeLog("Database connected");

function sendMessage($token, $chat_id, $message) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        writeLog("cURL error in sendMessage: " . $error);
        return ['ok' => false, 'description' => "cURL error: $error"];
    }
    $decoded_response = json_decode($response, true);
    writeLog("Telegram API response for Chat ID $chat_id: HTTP $http_code - " . $response);
    return $decoded_response;
}

$input = file_get_contents('php://input');
if (!empty($input)) {
    writeLog("Scheduled webhook does not accept direct input");
    exit(json_encode(['status' => 'error', 'message' => 'Scheduled webhook does not accept direct input']));
}

if (!isset($_GET['mode']) || $_GET['mode'] !== 'schedule') {
    writeLog("Scheduled webhook requires schedule mode");
    exit(json_encode(['status' => 'error', 'message' => 'Scheduled webhook requires schedule mode']));
}

writeLog("Cron Job triggered at: " . date('Y-m-d H:i:s'));
$current_time = date('H:i:00');
$current_date = date('Y-m-d');

$scheduled_topics = ['sendAnnouncementTimeAllday'];
$responses = [];

foreach ($scheduled_topics as $topic) {
    $table_name = "announcements_$topic";
    $sql = "SELECT a.id AS announcement_id, a.group_id, a.message AS announcement, 
                   COALESCE(a.announcement_time, '') AS announcement_time, 
                   COALESCE(a.announcement_date, '') AS announcement_date, 
                   b.token, g.group_name
            FROM $table_name a
            JOIN bot_settings bs ON a.group_id = bs.group_id
            JOIN bots b ON bs.bot_id = b.id
            JOIN groups g ON a.group_id = g.id
            WHERE COALESCE(a.announcement_time, '') = ? AND COALESCE(a.announcement_date, '') = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        writeLog("Prepare failed for $table_name: " . $conn->error);
        $responses[] = [
            'topic' => $topic,
            'status' => 'error',
            'message' => 'Prepare failed: ' . $conn->error
        ];
        continue;
    }
    $stmt->bind_param('ss', $current_time, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $announcement_id = $row['announcement_id'];
            $group_id = $row['group_id'];
            $group_name = $row['group_name'];
            $token = $row['token'];
            $announcement = $row['announcement'];
            $announcement_time = $row['announcement_time'];
            $announcement_date = $row['announcement_date'];

            $full_message = $announcement;

            // ดึงทุก chat_id ที่ผูกกับ group_id
            $chat_sql = "SELECT chat_id FROM chat_ids WHERE group_id = ?";
            $chat_stmt = $conn->prepare($chat_sql);
            if (!$chat_stmt) {
                writeLog("Prepare failed for chat_ids: " . $conn->error);
                $responses[] = [
                    'announcement_id' => $announcement_id,
                    'topic' => $topic,
                    'status' => 'error',
                    'message' => 'Chat prepare failed: ' . $conn->error
                ];
                continue;
            }
            $chat_stmt->bind_param('i', $group_id);
            $chat_stmt->execute();
            $chat_result = $chat_stmt->get_result();

            $chat_ids = [];
            while ($chat_row = $chat_result->fetch_assoc()) {
                $chat_ids[] = $chat_row['chat_id'];
            }
            $chat_stmt->close();

            writeLog("Found announcement: ID {$announcement_id}, Topic {$topic}, Group ID {$group_id}, Chat IDs " . implode(', ', $chat_ids) . ", Message {$full_message}");

            if (empty($chat_ids) || !$full_message) {
                writeLog("No chat IDs or message for announcement ID {$announcement_id} in topic {$topic}");
                $responses[] = [
                    'announcement_id' => $announcement_id,
                    'topic' => $topic,
                    'status' => 'error',
                    'message' => 'No chat IDs or message found'
                ];
                continue;
            }

            // ส่งข้อความไปยังทุก chat_id ที่ผูกกับ group_id
            foreach ($chat_ids as $group_chat_id) {
                $response = sendMessage($token, $group_chat_id, $full_message);

                if ($response['ok']) {
                    writeLog("Announcement '{$full_message}' sent to Chat ID {$group_chat_id} successfully for topic {$topic}");
                    $responses[] = [
                        'announcement_id' => $announcement_id,
                        'topic' => $topic,
                        'group_name' => $group_name,
                        'chat_id' => $group_chat_id,
                        'status' => 'success',
                        'message' => "Announcement sent successfully to Chat ID $group_chat_id"
                    ];
                } else {
                    writeLog("Failed to send announcement to Chat ID {$group_chat_id} for topic {$topic}: " . $response['description']);
                    $responses[] = [
                        'announcement_id' => $announcement_id,
                        'topic' => $topic,
                        'group_name' => $group_name,
                        'chat_id' => $group_chat_id,
                        'status' => 'error',
                        'message' => "Failed to send: " . $response['description']
                    ];
                }
            }
        }
    }
    $stmt->close();
}

if (empty($responses)) {
    writeLog("No scheduled announcements to send at this time");
    $responses[] = [
        'status' => 'info',
        'message' => 'No scheduled announcements to send'
    ];
}

$conn->close();
exit(json_encode(['status' => 'completed', 'results' => $responses], JSON_PRETTY_PRINT));
?>