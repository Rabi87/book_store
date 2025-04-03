<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        // تفعيل تقارير الأخطاء
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        $conn->begin_transaction();
        
        // 1. تحديث حالة الطلب
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("
            UPDATE borrow_requests 
            SET status = ?, processed_at = NOW() 
            WHERE id = ?
        ");
        
        if (!$stmt) {
            throw new Exception("خطأ في الاستعلام: " . $conn->error);
        }
        
        $stmt->bind_param("si", $new_status, $request_id);
        $stmt->execute();
        
        // 2. إعادة الكمية إذا كان الرفض
        if ($action === 'reject') {
            $stmt = $conn->prepare("
                UPDATE books 
                SET quantity = quantity + 1 
                WHERE id = (
                    SELECT book_id 
                    FROM borrow_requests 
                    WHERE id = ?
                )
            ");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
        }
        
        $conn->commit();
        $_SESSION['success'] = "تم التحديث بنجاح!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit();
}