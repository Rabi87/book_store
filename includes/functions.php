
<?php
// دوال مساعدة
function getStatusColor($status) {
    switch ($status) {
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'warning';
    }
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'قيد المراجعة',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض'
    ];
    return $statuses[$status] ?? 'غير معروف';
}
?>
<?php
// ملف functions.php
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function set_error($message) {
    $_SESSION['error'] = $message;
}

function set_success($message) {
    $_SESSION['success'] = $message;
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function send_notification($user_id, $message, $link = '') {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, message, link) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $user_id, $message, $link);
    $stmt->execute();
}

function mock_payment_gateway($data) {
    // محاكاة عملية دفع ناجحة (في البيئة الحقيقية، استخدم API حقيقي)
    return true; // تغيير إلى false لمحاكاة الفشل
}
?>