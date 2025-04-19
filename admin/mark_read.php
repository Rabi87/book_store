<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
require __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

$notificationId = $_GET['id'];
$userId = $_SESSION['user_id'];

// ------ التحقق من ملكية الإشعار ------
$checkSql = "SELECT notification_id FROM notifications WHERE notification_id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkSql);

if ($checkStmt === false) {
    die("فشل إعداد الاستعلام: " . htmlspecialchars($conn->error));
}

$checkStmt->bind_param("ii", $notificationId, $userId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// ------ تحديث حالة الإشعار ------
$updateSql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
$updateStmt = $conn->prepare($updateSql);

if ($updateStmt === false) {
    die("فشل إعداد استعلام التحديث: " . htmlspecialchars($conn->error));
}

$updateStmt->bind_param("i", $notificationId);
$updateStmt->execute();

if ($updateStmt->affected_rows === 0) {
    die("لم يتم العثور على الإشعار أو تحديثه");
}

echo json_encode(['success' => true]);
?>