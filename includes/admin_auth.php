<?php
// بدء الجلسة (إذا لم تكن بدأت)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=يجب تسجيل الدخول أولاً");
    exit();
}

// التحقق من صلاحية المدير
if ($_SESSION['user_type'] !== 'admin') {
    header("Location: ../index.php?error=ليس لديك صلاحية الوصول");
    exit();
}

// (اختياري) تحديث وقت الجلسة لمنع التهريب
$_SESSION['last_activity'] = time();
?>