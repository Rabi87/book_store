<?php
// استدعاء ملف التهيئة الذي يحتوي على الاتصال وبدء الجلسة
require_once __DIR__ . '/../includes/config.php';

// تحقق من صلاحيات المستخدم (يجب أن يكون أدمن)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    // استعلام لجلب البيانات
    $sql = "
        SELECT 
            p.payment_id,
            u.name AS user_name,
            br.id AS request_id,
            p.amount,
            p.payment_date,
            p.status AS payment_status,
            p.transaction_id
        FROM payments p
        INNER JOIN borrow_requests br ON p.request_id = br.id
        INNER JOIN users u ON br.user_id = u.id
        ORDER BY p.payment_date DESC
    ";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    
} catch (Exception $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>

    <?php if (empty($payments)): ?>
        <div class="alert alert-info">لا توجد عمليات دفع لعرضها</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>رقم العملية</th>
                        <th>صاحب الطلب</th>
                        <th>رقم الطلب</th>
                        <th>المبلغ</th>
                        <th>تاريخ الدفع</th>
                        <th>الحالة</th>
                        <th>رقم المعاملة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                            <td><?= htmlspecialchars($payment['user_name']) ?></td>
                            <td><?= htmlspecialchars($payment['request_id']) ?></td>
                            <td><?= number_format($payment['amount'], 2) ?> ر.س</td>
                            <td>
                                <?= $payment['payment_date'] 
                                    ? date('Y-m-d H:i', strtotime($payment['payment_date']))
                                    : 'N/A' ?>
                            </td>
                            <td>
                                <?php 
                                $status = $payment['payment_status'];
                                $badgeClass = [
                                    'pending' => 'warning',
                                    'completed' => 'success',
                                    'failed' => 'danger'
                                ][$status];
                                ?>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td><?= $payment['transaction_id'] ?? 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
