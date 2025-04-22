<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require __DIR__ . '/../includes/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب بيانات المستخدم
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, user_type FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("المستخدم غير موجود");
}

// جلب الإشعارات إذا كان مديرًا
$notifications = [];
if ($user['user_type'] === 'admin') {
    $notif_sql = "
        SELECT * 
        FROM notifications 
        WHERE user_id = ? 
        AND is_read = 0 
        AND (expires_at IS NULL OR expires_at > NOW())
        ORDER BY created_at DESC
    ";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $user_id);
    $notif_stmt->execute();
    $notifications = $notif_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ━━━━━━━━━━ جلب إحصائيات المستخدمين ━━━━━━━━━━
$stats = [
    'total_users' => 0,
    'online_users' => 0
];

// عدد المستخدمين الكلي
$total_sql = "SELECT COUNT(*) AS total_users FROM users";
$total_result = $conn->query($total_sql);
if ($total_result) {
    $stats['total_users'] = $total_result->fetch_assoc()['total_users'];
}

// عدد المستخدمين النشطين (آخر 5 دقائق)
$online_sql = "SELECT COUNT(*) AS online_users FROM users 
               WHERE last_activity >= NOW() - INTERVAL 2 MINUTE";
$online_result = $conn->query($online_sql);
if ($online_result) {
    $stats['online_users'] = $online_result->fetch_assoc()['online_users'];
}
?>

<div class="container mt-5">
    <!-- بطاقات الإحصائيات -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users me-2"></i>المستخدمين الكلي</h5>
                    <p class="card-text display-4"><?= $stats['total_users'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-signal me-2"></i>المستخدمين النشطين</h5>
                    <p class="card-text display-4"><?= $stats['online_users'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- باقي الكود الحالي لعرض الإشعارات -->
    <div class="container mt-4">
        <?php if (empty($notifications)): ?>
        <!-- ... الكود الحالي للإشعارات ... -->
        <?php endif; ?>
    </div>
</div>
<div class="container mt-5">
    <div class="container mt-4">
        <!-- داخل الجزء الخاص بعرض الإشعارات -->
        <?php if (empty($notifications)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            لا توجد إشعارات جديدة
        </div>
        <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
        <div class="card mb-3 notification-card" data-notif="<?= $notif['notification_id'] ?>">
            <div class="card-body">
                <p><?= htmlspecialchars($notif['message']) ?></p>
                <small class="text-muted"><?= $notif['created_at'] ?></small>
                <a href="<?= $notif['link'] ?>" class="btn btn-sm btn-success float-end"
                    onclick="markAsRead(<?= $notif['notification_id'] ?>, event)">
                    <i class="fas fa-external-link-alt me-2"></i> إدارة الطلب
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<script>
function markAsRead(notifId, event) {
    event.preventDefault();

    fetch('mark_read.php?id=' + notifId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إزالة الإشعار
                const notificationElement = document.querySelector(`[data-notif="${notifId}"]`);
                if (notificationElement) notificationElement.remove();

                // تحويل العرض لقسم "إدارة العمليات" داخل الـ dashboard
                if (typeof showSection === 'function') {
                    showSection('ops'); // تشغيل الدالة من dashboard.php
                    history.pushState(null, null, 'dashboard.php#ops'); // تحديث الـ URL
                } else {
                    window.location.href = 'dashboard.php#ops';
                }
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>