<?php
header('Content-Type: application/json');

$log_file = __DIR__ . '/../logs/activity_log.txt';
$max_lines = 100;
$chat_logs = [];
$activity_logs = [];
$all_sent = true;

if (file_exists($log_file)) {
    if (!is_readable($log_file)) {
        $activity_logs[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => 'ERROR',
            'message' => 'Log file not readable at: ' . $log_file
        ];
    } else {
        $log_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $log_lines = array_slice($log_lines, -$max_lines);
        
        foreach ($log_lines as $line) {
            preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.+)/', $line, $matches);
            if ($matches) {
                $timestamp = $matches[1];
                $message = $matches[2];
                $level = (stripos($message, 'error') !== false || stripos($message, 'failed') !== false) ? 'ERROR' : 'INFO';
                
                $activity_logs[] = [
                    'timestamp' => $timestamp,
                    'level' => $level,
                    'message' => $message
                ];
                
                // แยก Chat ID และสถานะจาก log
                if (preg_match('/Chat ID (-\d+): HTTP (\d+)/', $message, $chat_matches)) {
                    $chat_id = $chat_matches[1];
                    $http_code = (int)$chat_matches[2];
                    $status = ($http_code === 200) ? 'ส่งสำเร็จ' : 'ล้มเหลว';
                    if ($http_code !== 200) {
                        $all_sent = false;
                    }
                    $chat_logs[] = [
                        'timestamp' => $timestamp,
                        'level' => $level,
                        'chat_id' => $chat_id,
                        'status' => $status
                    ];
                }
            }
        }
        
        usort($chat_logs, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        usort($activity_logs, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
    }
} else {
    $activity_logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => 'ERROR',
        'message' => 'Log file not found at: ' . $log_file
    ];
}

$response = [
    'chat_logs' => $chat_logs,
    'activity_logs' => $activity_logs,
    'all_sent' => $all_sent
];

echo json_encode($response);
?>