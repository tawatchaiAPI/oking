<?php

$daily_log_file = __DIR__ . '/logs/telegram_webhook_log.txt'; // อ่านจาก log
$activity_log_file = __DIR__ . '/logs/activity_log.txt'; // บันทึก Activity Log
$max_file_size = 1024 * 1024; // 1MB

// ตรวจสอบและสร้างโฟลเดอร์ logs
if (!is_dir(dirname($activity_log_file))) {
    if (!mkdir(dirname($activity_log_file), 0755, true)) {
        die("Cannot create directory: " . dirname($activity_log_file));
    }
}

// ตรวจสอบการเขียนไฟล์
if (!is_writable($activity_log_file) && !file_exists($activity_log_file)) {
    if (!file_put_contents($activity_log_file, "Initial log file creation\n")) {
        die("Cannot create log file: $activity_log_file - Check permissions");
    }
    chmod($activity_log_file, 0644);
} elseif (!is_writable($activity_log_file)) {
    die("Log file is not writable: $activity_log_file - Check permissions");
}

function log_message($message, $file) {
    global $max_file_size;
    $current_time = date("Y-m-d H:i:s");
    $log_entry = "[{$current_time}] " . mb_convert_encoding($message, 'UTF-8', 'auto') . "\n";

    if (file_exists($file) && filesize($file) > $max_file_size) {
        $backup_file = $file . '.' . date('YmdHis') . '.bak';
        if (!rename($file, $backup_file)) {
            die("Cannot rotate log file: $file to $backup_file");
        }
        file_put_contents($file, "Log rotated at $current_time\n");
    }

    // ตรวจสอบการเขียน
    $result = file_put_contents($file, $log_entry, FILE_APPEND);
    if ($result === false) {
        die("Failed to write to log file: $file - Check permissions or disk space");
    }
}

// ดึงลิสต์ Chat IDs จาก log
function get_chat_ids($log_file) {
    if (!file_exists($log_file) || !is_readable($log_file)) {
        log_message("ไม่พบไฟล์ log หรือไม่สามารถอ่านได้: $log_file", $GLOBALS['activity_log_file']);
        return [];
    }

    $log_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $chat_ids = [];

    foreach ($log_lines as $line) {
        if (preg_match('/Chat IDs ([-\d, ]+)/', $line, $matches)) {
            $chat_ids_string = trim($matches[1]);
            $chat_ids = explode(',', $chat_ids_string);
            $chat_ids = array_filter($chat_ids, 'trim'); // ลบช่องว่าง
            $total_chat_ids = count($chat_ids);
            log_message("นับ Chat IDs ได้: $total_chat_ids จาก log", $GLOBALS['activity_log_file']);
            break;
        }
    }

    if (empty($chat_ids)) {
        log_message("ไม่พบ Chat IDs ใน log", $GLOBALS['activity_log_file']);
    }

    return $chat_ids;
}

// จำลองการส่งข้อความไปยัง chat id พร้อมสถานะ HTTP
function simulate_task($daily_log_file, $activity_log_file) {
    $chat_ids = get_chat_ids($daily_log_file); // ดึงลิสต์ Chat IDs
    $total_chat_ids = count($chat_ids);

    if ($total_chat_ids === 0) {
        log_message("ไม่สามารถดำเนินการต่อได้เนื่องจากไม่มี Chat IDs", $activity_log_file);
        return;
    }

    log_message("กำลังส่งข้อความ", $activity_log_file);
    sleep(3); // จำลองการเตรียมการ 3 วินาที

    $start_time = microtime(true);

    // จำลองการส่งตาม Chat IDs จริง
    $index = 1;
    foreach ($chat_ids as $chat_id) {
        // จำลองสถานะ HTTP (80% เป็น 200, 20% เป็นอื่นๆ)
        $http_status = (rand(0, 10) > 2) ? 200 : 403;
        $status_text = ($http_status === 200) ? "HTTP 200 - Success" : "HTTP 403 - Forbidden";
        log_message("Chat ID $chat_id: $status_text", $activity_log_file);
        usleep(1000); // จำลองดีเลย์ 0.001 วินาทีต่อ chat id
        $index++;
    }

    log_message("Wrapping up", $activity_log_file);
    usleep(100000);

    $elapsed_time = microtime(true) - $start_time;
    $hours = floor($elapsed_time / 3600);
    $minutes = floor(($elapsed_time % 3600) / 60);
    $seconds = floor($elapsed_time % 60);
    log_message("Done! Finished in " . sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds), $activity_log_file);
}

// รันโปรแกรม
simulate_task($daily_log_file, $activity_log_file);

echo "Task completed. Check logs in $activity_log_file";
?>