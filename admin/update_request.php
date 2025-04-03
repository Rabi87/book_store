<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $request_id = (int)$_POST['request_id'];
        $status = $_POST['status'];
        $admin_id = (int)$_SESSION['user_id'];

        // تحديث حالة الطلب
        $stmt = $conn->prepare("
            UPDATE borrow_requests 
            SET status = ?, admin_id = ?, processed_date = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("sii", $status, $admin_id, $request_id);
        $stmt->execute();

        // إذا تمت الموافقة، تحديث كمية الكتاب
        if ($status === 'approved') {
            $conn->query("
                UPDATE books b
                JOIN borrow_requests r ON b.id = r.book_id
                SET b.quantity = b.quantity - 1
                WHERE r.id = $request_id
            ");
        }

        $_SESSION['success'] = "تم تحديث حالة الطلب بنجاح!";
    } catch (Exception $e) {
        error_log("Update Error: " . $e->getMessage());
        $_SESSION['error'] = "حدث خطأ أثناء التحديث";
    }
    header("Location: requests.php");
    exit();
}