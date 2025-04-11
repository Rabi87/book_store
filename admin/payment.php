<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';
// التحقق من وجود الجلسة و نوع المستخدم
if (!isset($_SESSION['user_id']) ){
    header("Location: " . BASE_URL . "login.php");
    exit();
}
if ($_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// الحصول على تفاصيل الدفع
$stmt = $conn->prepare("
    SELECT p.*, br.book_id 
    FROM payments p
    JOIN borrow_requests br ON p.request_id = br.id
    WHERE p.request_id = ? AND p.status = 'pending'
");
$stmt->bind_param("i", $_GET['request_id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    $_SESSION['error'] = "لا يوجد دفع مطلوب";
    header("Location: " . BASE_URL . "profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // هنا يجب إضافة منطق معالجة الدفع الفعلي (بوابة دفع)
    
    // تحديث حالة الدفع
    $stmt = $conn->prepare("
        UPDATE payments 
        SET status = 'completed', 
            payment_date = NOW() 
        WHERE payment_id = ?
    ");
    $stmt->bind_param("i", $payment['payment_id']);
    $stmt->execute();

    // إنشاء إشعار القراءة
    $read_link = BASE_URL . "read_book.php?request_id=" . $payment['request_id'];
    $expires = date('Y-m-d H:i:s', strtotime('+14 days'));
    
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, link, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $message = "رابط قراءة الكتاب متاح حتى $expires";
    $stmt->bind_param("isss", $_SESSION['user_id'], $message, $read_link, $expires);
    $stmt->execute();

    $_SESSION['success'] = "تم الدفع بنجاح";
    header("Location: " . $read_link);
    exit();
}
?>

<!-- واجهة الدفع البسيطة -->
<div class="container">
    <h2>دفع مبلغ <?= $payment['amount'] ?> ريال</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">رقم البطاقة</label>
            <input type="text" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">تأكيد الدفع</button>
    </form>
</div>