<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errorV2.log');
header('Content-Type: application/json');

$log_dir = __DIR__ . '/logs/';
$log_file = $log_dir . 'telegram_webhook_log.txt';
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

$webhook_url = 'https://khotbotz.com/telegram_webhook_instant.php';
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
            writeLog("Telegram API response for Chat ID $chat_id: HTTP $http_code - " . $response);
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

if ($conn->connect_error) {
    writeLog("Database connection failed: " . $conn->connect_error);
    exit(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}
writeLog("Database connected");

$announcement_tables = [
    'sendOpenOnlyAll',
    'sendCloseDeposit',
    'sendCloseWithdraw',
    'sendCloseOnlyAll',
    'sendCloseRechargeByOne',
    'sendSystemCutOff',
    'sendSystemMaintenance',
    'sendAnnouncementAlldayAuto',
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

$input = file_get_contents('php://input');
if (empty($input)) {
    if (isset($_GET['mode']) && $_GET['mode'] === 'schedule') {
        writeLog("Cron Job triggered at: " . date('Y-m-d H:i:s'));

        $responses = [];
        foreach ($announcement_tables as $topic) {
            $table_name = "announcements_$topic";
            $sql = "SELECT a.id AS announcement_id, a.group_id, a.message AS announcement, 
                           b.token, g.group_name
                    FROM $table_name a
                    JOIN bot_settings bs ON a.group_id = bs.group_id
                    JOIN bots b ON bs.bot_id = b.id
                    JOIN groups g ON a.group_id = g.id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $announcement_id = $row['announcement_id'];
                    $group_id = $row['group_id'];
                    $group_name = $row['group_name'];
                    $token = $row['token'];
                    $announcement = $row['announcement'];

                    $chat_sql = "SELECT chat_id FROM chat_ids WHERE group_id = ?";
                    $chat_stmt = $conn->prepare($chat_sql);
                    $chat_stmt->bind_param('i', $group_id);
                    $chat_stmt->execute();
                    $chat_result = $chat_stmt->get_result();

                    $chat_ids = [];
                    while ($chat_row = $chat_result->fetch_assoc()) {
                        $chat_ids[] = $chat_row['chat_id'];
                    }
                    $chat_stmt->close();

                    writeLog("Found announcement: ID {$announcement_id}, Topic {$topic}, Group ID {$group_id}, Chat IDs " . implode(', ', $chat_ids));

                    if (empty($chat_ids) || !$announcement) {
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
                        $batch_results = sendMessageBatch($token, $batch, $announcement);
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
            writeLog("No announcements to send at this time");
            $responses[] = ['status' => 'info', 'message' => 'No announcements to send'];
        }

        $conn->close();
        exit(json_encode(['status' => 'completed', 'results' => $responses], JSON_PRETTY_PRINT));
    } else {
        $sql = "SELECT id, bot_name, token FROM bots";
        $result = $conn->query($sql);

        if ($result === false) {
            writeLog("Query failed: " . $conn->error);
            exit(json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]));
        }

        $responses = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bot_id = $row['id'];
                $bot_name = $row['bot_name'];
                $token = $row['token'];

                $telegram_api_url = "https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhook_url);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $telegram_api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_FAILONERROR, false);
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($response === false) {
                    $responses[] = [
                        'bot_id' => $bot_id,
                        'bot_name' => $bot_name,
                        'status' => 'error',
                        'message' => "cURL error for {$bot_name}: " . $error
                    ];
                } elseif ($http_code === 200) {
                    $telegram_response = json_decode($response, true);
                    if ($telegram_response['ok']) {
                        $responses[] = [
                            'bot_id' => $bot_id,
                            'bot_name' => $bot_name,
                            'status' => 'success',
                            'message' => "Webhook set successfully for {$bot_name}"
                        ];
                    } else {
                        $responses[] = [
                            'bot_id' => $bot_id,
                            'bot_name' => $bot_name,
                            'status' => 'error',
                            'message' => "Failed to set webhook for {$bot_name}: " . $telegram_response['description']
                        ];
                    }
                } else {
                    $responses[] = [
                        'bot_id' => $bot_id,
                        'bot_name' => $bot_name,
                        'status' => 'error',
                        'message' => "HTTP error {$http_code} for {$bot_name}: " . $error
                    ];
                }
            }
        } else {
            writeLog("No bots found in database");
            exit(json_encode(['status' => 'error', 'message' => 'No bots found in database']));
        }

        $conn->close();
        exit(json_encode(['status' => 'completed', 'results' => $responses], JSON_PRETTY_PRINT));
    }
} else {
    $data = json_decode($input, true);
    writeLog("Received input: " . json_encode($data));

    if (isset($data['id']) && isset($data['topic']) && isset($data['message'])) {
        $id = $data['id'];
        $topic = $data['topic'];
        $message = $data['message'];

        if (!in_array($topic, $announcement_tables)) {
            writeLog("Invalid topic: $topic");
            exit(json_encode(['status' => 'error', 'message' => 'Invalid topic']));
        }

        $table_name = "announcements_$topic";
        $sql = "SELECT a.id, a.group_id, a.message, b.token, g.group_name
                FROM $table_name a
                JOIN bot_settings bs ON a.group_id = bs.group_id
                JOIN bots b ON bs.bot_id = b.id
                JOIN groups g ON a.group_id = g.id
                WHERE a.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $group_id = $row['group_id'];
            $group_name = $row['group_name'];
            $token = $row['token'];
            $announcement = $row['message'];

            $chat_sql = "SELECT chat_id FROM chat_ids WHERE group_id = ?";
            $chat_stmt = $conn->prepare($chat_sql);
            $chat_stmt->bind_param('i', $group_id);
            $chat_stmt->execute();
            $chat_result = $chat_stmt->get_result();

            $chat_ids = [];
            while ($chat_row = $chat_result->fetch_assoc()) {
                $chat_ids[] = $chat_row['chat_id'];
            }
            $chat_stmt->close();

            writeLog("Found announcement: ID $id, Topic $topic, Group ID $group_id, Chat IDs " . implode(', ', $chat_ids));

            if (!$token) {
                writeLog("No token found for announcement ID $id in topic $topic");
                exit(json_encode(['status' => 'error', 'message' => 'No bot token available']));
            }

            if (empty($chat_ids)) {
                writeLog("No chat IDs found for group ID $group_id in topic $topic");
                exit(json_encode(['status' => 'error', 'message' => 'No chat IDs associated with this group']));
            }

            $batch_size = 100;
            $batches = array_chunk($chat_ids, $batch_size);
            $success = true;

            foreach ($batches as $batch) {
                $batch_results = sendMessageBatch($token, $batch, $announcement);
                foreach ($batch_results as $chat_id => $response) {
                    if (!$response['ok']) {
                        $success = false;
                        writeLog("Failed to send announcement to Chat ID {$chat_id} for topic {$topic}: " . $response['description']);
                    }
                }
                if (count($batches) > 1) {
                    sleep(1);
                }
            }

            if ($success) {
                writeLog("Announcement '{$announcement}' sent to all Chat IDs successfully for topic {$topic}");
                $date = date('d ') . $month_names[date('m')] . date(' Y');
                $notification_message = "🔥 ANNOUNCEMENT SENT 🔥\n" .
                                       "🚀 Status: Successfully Sent\n" .
                                       "📅 Date: {$date}\n" .
                                       "🔗 Topic: {$topic}\n" .
                                       "👥 Group: {$group_name}\n" .
                                       "📩 Sent To: " . count($chat_ids) . " Chat IDs";
                sendNotification($notification_bot_token, $notification_chat_id, $notification_message);
                exit(json_encode(['status' => 'success', 'message' => "Announcement sent successfully"]));
            } else {
                writeLog("Failed to send announcement to some Chat IDs for topic {$topic}");
                exit(json_encode(['status' => 'error', 'message' => "Failed to send to some chat IDs. Check logs for details"]));
            }
        } else {
            writeLog("No matching announcement found for ID: $id in topic $topic");
            exit(json_encode(['status' => 'error', 'message' => 'Announcement not found']));
        }
        $stmt->close();
    } elseif (isset($data['my_chat_member']) && $data['my_chat_member']['new_chat_member']['status'] === 'member' && $data['my_chat_member']['new_chat_member']['user']['is_bot']) {
        $chat_id = $data['my_chat_member']['chat']['id'];
        $group_name = $data['my_chat_member']['chat']['title'] ?? 'Unnamed Group';
        writeLog("Bot added to group: Chat ID {$chat_id}, Group Name {$group_name}");
    }

    $conn->close();
    exit(json_encode(['status' => 'ok']));
}
?>