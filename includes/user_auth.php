<?php
// بدء الجلسة إذا لم تكن بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=يجب تسجيل الدخول أولاً");
    exit();
}

// 2. التحقق من أن المستخدم ليس مسؤولًا
if ($_SESSION['user_type'] === 'admin') {
    header("Location: admin/dashboard.php?error=غير مصرح للمسؤولين بالوصول هنا");
    exit();
}

// (اختياري) تحديث وقت النشاط لمنع انتهاء الجلسة
$_SESSION['last_activity'] = time();
?>