<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$topic = $data['topic'] ?? null;
$message = $data['message'] ?? null;

if (!$id || !$topic || !$message) {
    exit(json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']));
}

$webhook_url = 'https://khotbotz.com/telegram_webhook_instant.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    exit(json_encode(['status' => 'error', 'message' => 'cURL error: ' . $error]));
}

// ตรวจสอบว่า response เป็น JSON ที่ถูกต้อง
$decoded_response = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    exit($response); // ส่งต่อ JSON ที่ถูกต้อง
} else {
    writeLog("Invalid JSON response from telegram_webhook_instant.php: " . $response); // ถ้ามี log function
    exit(json_encode(['status' => 'error', 'message' => 'Invalid response from telegram_webhook_instant.php: ' . $response]));
}

function writeLog($message) {
    $log_file = __DIR__ . '/logs/send_message_error.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}
?>