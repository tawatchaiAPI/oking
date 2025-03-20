<?php
ob_start();

// รวม auth.php (ปรับ path ตามจริง)
require_once __DIR__ . '/datascss/auth.php'; // ถ้า auth.php อยู่ใน root
// หรือ require_once __DIR__ . '/datascss/auth.php'; ถ้าอยู่ใน datascss

requireAdmin();

ob_end_flush();

// แสดง HTML โดยตรง (ถ้า index.html เป็น pure HTML)
include 'html/onebank.html';
include 'datascss/footer.php';
// หรือถ้ามี PHP ใน index.html ให้ใช้:
// include 'html/index.html';
?>