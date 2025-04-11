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
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="card-body text-center">
            <!-- عرض البيانات -->
            <div class="mb-3">
                <h5 class="text-primary">الاسم الكامل:</h5>
                <p class="fs-5"><?php echo htmlspecialchars($user['name']); ?></p>
            </div>
            <div class="mb-3">
                <h5 class="text-primary">نوع المستخدم:</h5>
                <p class="fs-5 badge bg-<?php echo $user['user_type'] == 'admin' ? 'success' : 'info'; ?>">
                    <?php echo $user['user_type'] == 'admin' ? 'مدير النظام' : 'مستخدم عادي'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>