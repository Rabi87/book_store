<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';

// معالجة الطلبات
if (isset($_POST['backup'])) {
    $backup_type = $_POST['backup_type'];
    
    if ($backup_type == 'database') {
        $filename = backupDatabase('localhost', 'rabi', 'Asd@123@123', 'library_db');
    } else {
        $filename = backupFiles(__DIR__ . '/uploads/');
    }

    if ($filename) {
        $_SESSION['success'] = "تم إنشاء النسخة: " . $filename;
    } else {
        $_SESSION['error'] = "فشل في النسخ الاحتياطي!";
    }
}

if (isset($_POST['restore'])) {
    $uploaded_file = $_FILES['restore_file'];
    $file_type = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);

    if ($file_type == 'sql') {
        $success = restoreDatabase('localhost', 'user', 'pass', 'dbname', $uploaded_file['tmp_name']);
    } elseif ($file_type == 'zip') {
        $success = restoreFiles($uploaded_file['tmp_name'], __DIR__ . '/uploads/');
    }

    if ($success) {
        $_SESSION['success'] = "تم الاسترجاع بنجاح!";
    } else {
        $_SESSION['error'] = "فشل في الاسترجاع!";
    }
}
?>

<!-- واجهة HTML -->
<form method="post">
    <h3>النسخ الاحتياطي</h3>
    <select name="backup_type">
        <option value="database">قاعدة البيانات</option>
        <option value="files">الملفات</option>
    </select>
    <button type="submit" name="backup">إنشاء نسخة</button>
</form>

<form method="post" enctype="multipart/form-data">
    <h3>استرجاع</h3>
    <input type="file" name="restore_file" required>
    <button type="submit" name="restore">استرجاع</button>
</form>