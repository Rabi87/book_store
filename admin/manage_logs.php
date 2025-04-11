<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db_logger.php';
// التحقق من صلاحيات المدير
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// في ملف manage_logs.php:
$logs = DatabaseLogger::readLogs(100);

?>

<div class="container mt-5">
    <?php include __DIR__ . '/../includes/alerts.php'; ?>
    <!-- جدول عرض الكتب -->
     <table class="table table-striped">
        <thead>
            <tr>
                    <th>التاريخ</th>
                    <th>نوع الحدث</th>
                    <th>المستخدم</th>
                    <th>التفاصيل</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <td><?= htmlspecialchars($log['event_type']) ?></td>
                    <td><?= htmlspecialchars($log['user']) ?></td>
                    <td><?= htmlspecialchars($log['details']) ?></td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
