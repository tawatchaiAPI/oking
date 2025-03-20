<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errorV2.log');
header('Content-Type: application/json');

$log_dir = __DIR__ . '/logs/';
$log_file = $log_dir . 'telegram_webhook_daily_log.txt';
$max_file_size = 1024 * 1024;

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

if (!is_writable($log_file) && !file_exists($log_file)) {
    if (!file_put_contents($log_file, "Initial log file creation\n")) {
        exit(json_encode(['status' => 'error', 'message' => 'Cannot create log file: ' . $log_file]));
    }
    chmod($log_file, 0644);
} elseif (!is_writable($log_file)) {
    exit(json_encode(['status' => 'error', 'message' => 'Log file is not writable: ' . $log_file]));
}

function writeLog($message) {
    global $log_file, $max_file_size;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] " . mb_convert_encoding($message, 'UTF-8', 'auto') . "\n";

    if (file_exists($log_file) && filesize($log_file) > $max_file_size) {
        $backup_file = $log_file . '.' . date('YmdHis') . '.bak';
        rename($log_file, $backup_file);
        file_put_contents($log_file, "Log rotated at $timestamp\n");
    }

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

$notification_bot_token = '7765463590:AAF8_8OG8CTaofg3Qf41JxhUsyvpX5CCR2s';
$notification_chat_id = '-4620964501'; // แทนที่ด้วย Chat ID ของกลุ่มคุณ

$month_names = [
    '01' => 'ม.ค.',
    '02' => 'ก.พ.',
    '03' => 'มี.ค.',
    '04' => 'เม.ย.',
    '05' => 'พ.ค.',
    '06' => 'มิ.ย.',
    '07' => 'ก.ค.',
    '08' => 'ส.ค.',
    '09' => 'ก.ย.',
    '10' => 'ต.ค.',
    '11' => 'พ.ย.',
    '12' => 'ธ.ค.'
];

function sendMessageBatch($token, $chat_ids, $message) {
    $telegram_url = "https://api.telegram.org/bot{$token}/sendMessage";
    $multi_handle = curl_multi_init();
    $curl_handles = [];

    foreach ($chat_ids as $chat_id) {
        $ch = curl_init();
        $data = [
            'chat_id' => $chat_id,
            'text' => $message
        ];
        curl_setopt($ch, CURLOPT_URL, $telegram_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_multi_add_handle($multi_handle, $ch);
        $curl_handles[$chat_id] = $ch;
    }

    do {
        curl_multi_exec($multi_handle, $running);
        curl_multi_select($multi_handle);
    } while ($running > 0);

    $results = [];
    foreach ($curl_handles as $chat_id => $ch) {
        $response = curl_multi_getcontent($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($response === false) {
            writeLog("cURL error for Chat ID $chat_id: " . $error);
            $results[$chat_id] = ['ok' => false, 'description' => "cURL error: $error"];
        } else {
            $decoded_response = json_decode($response, true);
            if (!$decoded_response['ok']) {
                writeLog("Failed to send to Chat ID $chat_id: " . $decoded_response['description']);
            }
            $results[$chat_id] = $decoded_response;
        }

        curl_multi_remove_handle($multi_handle, $ch);
        curl_close($ch);
    }

    curl_multi_close($multi_handle);
    return $results;
}

function sendNotification($token, $chat_id, $message) {
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
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        writeLog("Notification cURL error: " . $error);
    } else {
        $decoded_response = json_decode($response, true);
        if (!$decoded_response['ok']) {
            writeLog("Failed to send notification: " . $decoded_response['description']);
        }
    }
}

$input = file_get_contents('php://input');
if (!empty($input)) {
    writeLog("Daily webhook does not accept direct input");
    exit(json_encode(['status' => 'error', 'message' => 'Daily webhook does not accept direct input']));
}

if (!isset($_GET['mode']) || $_GET['mode'] !== 'schedule') {
    writeLog("Daily webhook requires schedule mode");
    exit(json_encode(['status' => 'error', 'message' => 'Daily webhook requires schedule mode']));
}

writeLog("Cron Job triggered at: " . date('Y-m-d H:i:s'));
$current_time = date('H:i:00');

$daily_topics = ['sendAnnouncementAlldayAuto'];
$responses = [];

foreach ($daily_topics as $topic) {
    $table_name = "announcements_$topic";
    $sql = "SELECT a.id AS announcement_id, a.group_id, a.message AS announcement, 
                   COALESCE(a.announcement_time, '') AS announcement_time, 
                   b.token, g.group_name
            FROM $table_name a
            JOIN bot_settings bs ON a.group_id = bs.group_id
            JOIN bots b ON bs.bot_id = b.id
            JOIN groups g ON a.group_id = g.id
            WHERE COALESCE(a.announcement_time, '') = ?";
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
    $stmt->bind_param('s', $current_time);
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

            $full_message = $announcement;

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

            $batch_size = 100;
            $batches = array_chunk($chat_ids, $batch_size);
            $success_count = 0;
            $fail_count = 0;

            foreach ($batches as $batch) {
                $batch_results = sendMessageBatch($token, $batch, $full_message);
                foreach ($batch_results as $chat_id => $response) {
                    if ($response['ok']) {
                        $success_count++;
                    } else {
                        $fail_count++;
                    }
                }
                if (count($batches) > 1) {
                    sleep(1);
                }
            }

            writeLog("Announcement ID {$announcement_id} sent: $success_count successes, $fail_count failures for topic {$topic}");

            $responses[] = [
                'announcement_id' => $announcement_id,
                'topic' => $topic,
                'group_name' => $group_name,
                'status' => ($fail_count === 0) ? 'success' : 'partial',
                'message' => "Sent to $success_count chat IDs, failed $fail_count chat IDs"
            ];

            if ($fail_count === 0) {
                $date = date('d ') . $month_names[date('m')] . date(' Y');
                $notification_message = "🔥 ANNOUNCEMENT SENT 🔥\n" .
                                       "🚀 Status: Successfully Sent\n" .
                                       "📅 Date: {$date}\n" .
                                       "🔗 Topic: {$topic}\n" .
                                       "👥 Group: {$group_name}\n" .
                                       "📩 Sent To: {$success_count} Chat IDs";
                sendNotification($notification_bot_token, $notification_chat_id, $notification_message);
            }
        }
    }
    $stmt->close();
}

if (empty($responses)) {
    writeLog("No daily announcements to send at this time");
    $responses[] = [
        'status' => 'info',
        'message' => 'No daily announcements to send'
    ];
}

$conn->close();
exit(json_encode(['status' => 'completed', 'results' => $responses], JSON_PRETTY_PRINT));
?>