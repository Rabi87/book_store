<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn->begin_transaction();
        
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $loan_duration = 14; // يمكن تغييرها حسب سياسة المكتبة

        if ($action === 'approve') {
            // حالة الموافقة - تحديث المدة وتاريخ الاستحقاق
            $stmt = $conn->prepare("
                UPDATE borrow_requests 
                SET 
                    status = ?, 
                    processed_at = NOW(),
                    loan_duration = ?,
                    due_date = DATE_ADD(NOW(), INTERVAL ? DAY)
                WHERE id = ?
            ");
            $stmt->bind_param("siii", $new_status, $loan_duration, $loan_duration, $request_id);
        } else {
            // حالة الرفض - تحديث الحالة فقط
            $stmt = $conn->prepare("
                UPDATE borrow_requests 
                SET 
                    status = ?, 
                    processed_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("si", $new_status, $request_id);
        }
        
        $stmt->execute();

        if ($action === 'reject') {
            // إعادة الكمية إذا كان الرفض
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
        $_SESSION['success'] = "تم تحديث حالة الطلب بنجاح!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit();
}