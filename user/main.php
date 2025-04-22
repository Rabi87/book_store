<?php

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

<!-- بطاقة المحفظة المحدثة -->
<div class="card border-0 mb-4">

    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white py-1">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-wallet me-2"></i> الرصيد
        </h6>

        <?php if ($balance == 0.00): ?>
        <div class="alert alert-dark d-flex align-items-center mb-0">
            <i class="fas fa-exclamation-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">لا يوجد رصيد متاح</h5>
                <p class="mb-0">قم بإضافة رصيد لبدء استخدام الخدمات</p>
            </div>
        </div>
        <?php else: ?>
        <div class="alert <?= $balance < 5000 ? 'alert-danger' : 'alert-success' ?> d-flex align-items-center">

            <div>
                <h4 class="alert-heading mb-1">
                    <?= number_format($balance, 2) ?> ل.س
                </h4>
                <?php if ($balance < 5000): ?>
                <hr class="mt-2 mb-2">
                <p class="mb-0 small">
                    الرصيد الحالي لا يكفي لإتمام عملية الاستعارة (الحد الأدنى 5,000 ليرة)
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>payment.php" class="btn btn-light btn-sm rounded-pill">
            <i class="fas fa-coins me-2"></i>شحن الرصيد
        </a>
    </div>

</div>

<!-- قسم الإشعارات المحدث -->
<div class="card border-0">
    <div class="card-header bg-dark text-white py-3">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-bell me-2"></i>
        </h4>
    </div>

    <div class="card-body p-4">
        <div class="notifications-scroll-container">
            <?php
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);?>
            <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notif): ?>
            <div class="alert alert-secondary d-flex align-items-center mb-3">
                <i class="fas fa-info-circle me-3 fa-lg"></i>
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-medium"><?= htmlspecialchars($notif['message']) ?></span>
                        <?php if ($notif['link'] && $notif['link_read'] == 0): ?>
                        <!-- عرض الرابط إذا كان link_read = 0 -->
                        <a href="<?= htmlspecialchars($notif['link']) ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>عرض
                        </a>
                        <?php else: ?>
                        <!-- إخفاء الرابط أو تعطيله -->
                        <span class="text-muted">(تمت القراءة)</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($notif['created_at'])): ?>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('Y-m-d H:i', strtotime($notif['created_at'])) ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="alert alert-info d-flex align-items-center mb-0">
                <i class="fas fa-info-circle me-3 fa-lg"></i>
                <div>
                    <h5 class="alert-heading mb-1">لا توجد إشعارات جديدة</h5>
                    <p class="mb-0">سيتم إعلامك هنا بأي تحديثات جديدة</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
.notifications-scroll-container {
    max-height: 320px;
    /* تحديد أقصى ارتفاع */
    overflow-y: auto;
    /* التمرير التلقائي */
    padding-right: 0.5rem;
    /* منع قص المحتوى */
}
</style>


<!-- إضافة الأنماط المخصصة -->
<style>
.bg-gradient-primary {
    background: linear-gradient(to right, #2c3e50, #3498db);
}

.bg-gradient-info {
    background: linear-gradient(to right, #2980b9, #3498db);
}

.card {
    border-radius: 15px;
    overflow: hidden;

}


.alert {
    border-radius: 10px;
    border: none;

}

.btn-outline-primary {
    border-width: 2px;
}
</style>
