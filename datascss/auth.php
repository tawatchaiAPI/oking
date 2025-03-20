<?php
ob_start();
session_start();

// ตรวจสอบว่าเป็น admin หรือไม่
function requireAdmin() {
    error_log("Checking admin access: user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", role = " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        ob_end_clean();
        error_log("Access denied: Redirecting to login.php");
        header("Location: /login.php");
        exit;
    }
}

// ตรวจสอบว่าเป็น user หรือไม่
function requireUser() {
    error_log("Checking user access: user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", role = " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        ob_end_clean();
        error_log("Access denied: Redirecting to login.php");
        header("Location: /login.php");
        exit;
    }
}

// ตรวจสอบว่าล็อกอินแล้วหรือไม่ (ไม่สน role)
function requireLogin() {
    error_log("Checking login: user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
    
    if (!isset($_SESSION['user_id'])) {
        ob_end_clean();
        error_log("Access denied: Redirecting to login.php");
        header("Location: /login.php");
        exit;
    }
}

// เพิ่มฟังก์ชันใหม่: อนุญาตเฉพาะ admin หรือ user
function requireAdminOrUser() {
    error_log("Checking admin or user access: user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", role = " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'user')) {
        ob_end_clean();
        error_log("Access denied: Redirecting to login.php");
        header("Location: /login.php");
        exit;
    }
}
?>