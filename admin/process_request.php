<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/admin_auth.php';
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("طلب غير مصرح به!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    
    try {
        // ━━━━━━━━━━ خصم المبلغ بعد الموافقة ━━━━━━━━━━

            // الحصول على user_id من جدول borrow_requests
        $stmt_user = $conn->prepare("SELECT user_id FROM borrow_requests WHERE id = ?");
        $stmt_user->bind_param("i", $request_id);
        $stmt_user->execute();
        $result = $stmt_user->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("طلب غير موجود!");
        }
        
        $user_id = $result->fetch_assoc()['user_id']; // user_id هنا يتم استخراج
        
            // الحصول على المبلغ من الطلب بدلًا من القيمة الثابتة
        $stmt_amount = $conn->prepare("SELECT amount FROM borrow_requests WHERE id = ?");
        $stmt_amount->bind_param("i", $request_id);
        $stmt_amount->execute();
        $amount = $stmt_amount->get_result()->fetch_assoc()['amount'];
        
        
        // التحقق من الرصيد الحالي
        $stmt_balance = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt_balance->bind_param("i", $user_id);
        $stmt_balance->execute();
        $balance = $stmt_balance->get_result()->fetch_assoc()['balance'];
        
   
        
        if ($balance < $amount) {
            throw new Exception("رصيد المستخدم غير كافٍ لإكمال العملية");
        }
        
        // تنفيذ الخصم
        $stmt_deduct = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
        $stmt_deduct->bind_param("di", $amount, $user_id);
        $stmt_deduct->execute();
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn->begin_transaction();
        
        $new_status = ($action === 'approve') ? 'approved' : 'rejected';
        $loan_duration = 14;

        if ($action === 'approve') {
            // تحديث حالة الطلب إلى pending_payment
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
            $stmt->execute();

            // إضافة سجل دفع جديد مع التعديلات
            
            $stmt_payment = $conn->prepare("
                INSERT INTO payments (
                    request_id, 
                    amount, 
                    status
                ) VALUES (?, ?, 'completed')
            ");
            $stmt_payment->bind_param("id", $request_id, $amount); // 'd' لـ decimal
            $stmt_payment->execute();

            // الحصول على user_id من الطلب
            $stmt_user = $conn->prepare("SELECT user_id FROM borrow_requests WHERE id = ?");
            $stmt_user->bind_param("i", $request_id);
            $stmt_user->execute();
            $user_id = $stmt_user->get_result()->fetch_assoc()['user_id'];

            $stmt_book = $conn->prepare("
                SELECT b.title 
                FROM borrow_requests br
                JOIN books b ON br.book_id = b.id
                WHERE br.id = ?
            ");
            $stmt_book->bind_param("i", $request_id);
            $stmt_book->execute();
            $book_title = $stmt_book->get_result()->fetch_assoc()['title'];


            // إضافة إشعار للمستخدم
            $message = "يمكنك تصفح كتاب $book_title على الرابط التالي"; 
            $payment_link = BASE_URL . "read_book.php?request_id=" . $request_id;
            
            $stmt_notif = $conn->prepare("
                INSERT INTO notifications (user_id, message, link)
                VALUES (?, ?, ?)
            ");
            $stmt_notif->bind_param("iss", $user_id, $message, $payment_link);
            $stmt_notif->execute();

        } else {
            // ... (نفس كود الرفض السابق)
        }

        $conn->commit();
        $_SESSION['success'] = "تم تحديث حالة الطلب بنجاح!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
    }
    
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}