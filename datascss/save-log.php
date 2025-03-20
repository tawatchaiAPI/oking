<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['token'])) {
    exit(json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']));
}

$log_file = __DIR__ . '/logs/message_log.json';
$logs = [];

if (file_exists($log_file)) {
    $logs = json_decode(file_get_contents($log_file), true);
    if (!is_array($logs)) $logs = [];
}

$logs[] = $data;
file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

exit(json_encode(['status' => 'success', 'message' => 'บันทึก log สำเร็จ']));
?>