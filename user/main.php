<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ━━━━━━━━━━ التحقق من الصلاحيات ━━━━━━━━━━
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
// ━━━━━━━━━━ جلب بيانات المحفظة ━━━━━━━━━━
$balance = 0.00;
try {
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $wallet = $result->fetch_assoc();
    $balance = $wallet ? (float)$wallet['balance'] : 0.00;
} catch (Exception $e) {
    error_log("Wallet Error: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <!-- بطاقة المحفظة -->
    <div class="container mt-5">
    <!-- بطاقة المحفظة -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>رصيدك الحالي</h5>
            <a href="<?= BASE_URL ?>payment.php" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-2"></i>إضافة رصيد
            </a>
        </div>
        <div class="card-body">
            <?php if ($balance == 0.00): ?>
                <!-- حالة عدم وجود رصيد -->
                <div class="alert alert-dark">
                    <h4 class="alert-heading">
                        <i class="fas fa-wallet me-2"></i>لا يوجد رصيد
                    </h4>
                    <hr>
                    <p class="mb-0">المحفظة فارغة. يرجى إضافة رصيد لاستخدام الخدمات.</p>
                </div>
            <?php else: ?>
                <!-- حالة وجود رصيد -->
                <div class="alert <?= $balance < 5000 ? 'alert-danger' : 'alert-success' ?>">
                    <h4 class="alert-heading">
                        <?= number_format($balance, 2) ?> ل.س
                    </h4>
                    <?php if ($balance < 5000): ?>
                        <hr>
                        <p class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            الرصيد غير كافي للاستعارة (الحد الأدنى 5,000 ليرة)
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- الإشعارات -->
    <div class="card">
        <div class="card-body">
            <?php
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($notifications)):
                foreach ($notifications as $notif): ?>
                    <div class="alert alert-secondary">
                        <?= htmlspecialchars($notif['message']) ?>
                        <?php if ($notif['link']): ?>
                            <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-primary mt-2">
                                عرض التفاصيل
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach;?>
            <?php else: ?>
                <div class="alert alert-info">لا توجد إشعارات جديدة</div>
            <?php endif; ?>
        </div>
    </div>
            </div>
</div>

