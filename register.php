<?php
ob_start();
require_once __DIR__ . '/datascss/auth.php';
requireAdmin(); // เรียกจาก auth.php เพื่อตรวจสอบว่าเป็น admin
ob_end_flush();
require_once __DIR__ . '/datascss/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // เข้ารหัสด้วย Bcrypt
    $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : 'user';

    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        header("Location: register.php?success=Registration successful");
        exit;
    } else {
        $error = "Registration failed: " . $conn->error;
    }
}

include 'html/register.html';

?>
