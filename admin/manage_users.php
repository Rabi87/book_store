<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__ . '/../includes/config.php';
// التحقق من صلاحيات المدير
if (!isset($_SESSION['user_id']) ){
    header("Location: " . BASE_URL . "login.php");
    exit();
}
if ($_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
// جلب جميع المستخدمين مع عدد الطلبات لكل حالة
$users = [];
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.user_type, 
        u.status,
        SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN br.status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN br.status = 'rejected' THEN 1 ELSE 0 END) AS rejected
    FROM users u
    LEFT JOIN borrow_requests br ON u.id = br.user_id
    GROUP BY u.id
";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
// معالجة الإجراءات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('طلب غير صالح');
    }
    $user_id = $conn->real_escape_string($_POST['user_id']);
    if (isset($_POST['delete_user'])) {
        // التحقق مما إذا كان المستخدم لديه طلبات استعارة
        $check_borrow_requests = $conn->prepare("SELECT COUNT(*) AS total_requests FROM borrow_requests WHERE user_id = ?");
        $check_borrow_requests->bind_param("i", $user_id);
        $check_borrow_requests->execute();
        $borrow_requests_result = $check_borrow_requests->get_result();
        $total_requests = $borrow_requests_result->fetch_assoc()['total_requests'];
        if ($total_requests > 0) {
            // حذف جميع الطلبات المرتبطة بالمستخدم
            $delete_requests_stmt = $conn->prepare("DELETE FROM borrow_requests WHERE user_id = ?");
            $delete_requests_stmt->bind_param("i", $user_id);
            $delete_requests_stmt->execute();
        }
        // حذف المستخدم
        $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user_stmt->bind_param("i", $user_id);
        $delete_user_stmt->execute();    
    } 
    elseif (isset($_POST['change_password'])) {
        // تغيير كلمة المرور
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $user_id);
        $stmt->execute();
    } 
    elseif (isset($_POST['change_status'])) {
        // تغيير الحالة (مفعل/غير مفعل)
        $status = $conn->real_escape_string($_POST['status']);
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $user_id);
        $stmt->execute();
    } 
    elseif (isset($_POST['make_admin'])) {
        // تغيير نوع المستخدم (مدير/عادي)
        $user_type = $conn->real_escape_string($_POST['user_type']);
        $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE id = ?");
        $stmt->bind_param("si", $user_type, $user_id);
        $stmt->execute();
    }
}
?>
<div class="container py-5">
    <?php include __DIR__ . '/../includes/alerts.php'; ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>النوع</th>
                    <th>الحالة</th>
                    <th> الطلبات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <!-- تغيير نوع المستخدم -->
                    <td>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="user_type" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="user" <?= $user['user_type'] === 'user' ? 'selected' : '' ?>> عادي
                                </option>
                                <option value="admin" <?= $user['user_type'] === 'admin' ? 'selected' : '' ?>>مدير
                                </option>
                            </select>
                            <input type="hidden" name="make_admin">
                        </form>
                    </td>
                    <!-- تغيير الحالة -->
                    <td>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="0" <?= $user['status'] == 0 ? 'selected' : '' ?>>غير مفعل</option>
                                <option value="1" <?= $user['status'] == 1 ? 'selected' : '' ?>>مفعل</option>
                            </select>
                            <input type="hidden" name="change_status">
                        </form>
                    </td>
                    <!-- حالة الطلبات -->
                    <td>
                        <?php
                        echo '<span class="status-pending">';
                        echo '<i class="fas fa-clock"></i> ' . ($user['pending'] ?? 0);
                        echo '</span><br>';
                        
                        echo '<span class="status-approved">';
                        echo '<i class="fas fa-check-circle"></i> ' . ($user['approved'] ?? 0);
                        echo '</span><br>';
                        
                        echo '<span class="status-rejected">';
                        echo '<i class="fas fa-times-circle"></i> ' . ($user['rejected'] ?? 0);
                        echo '</span>';
                        ?>
                    </td>
                    <!-- الإجراءات -->
                    <td>
                        <?php
                        // التحقق مما إذا كان المستخدم لديه طلبات استعارة
                        $check_borrow_requests = $conn->prepare("SELECT COUNT(*) AS total_requests FROM borrow_requests WHERE user_id = ?");
                        $check_borrow_requests->bind_param("i", $user['id']);
                        $check_borrow_requests->execute();
                        $borrow_requests_result = $check_borrow_requests->get_result();
                        $has_requests = $borrow_requests_result->fetch_assoc()['total_requests'] > 0;
                        ?>
                        <form method="post" class="d-inline" onsubmit="return confirmDelete(<?= $has_requests ?>);">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm btn-action">
                                <i class="fas fa-trash-alt"></i> حذف
                            </button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="password" name="new_password" placeholder="كلمة السر الجديدة" required
                                class="form-control form-control-sm d-inline-block" style="width: 120px;">
                            <button type="submit" name="change_password" class="btn btn-warning btn-sm btn-action">
                                <i class="fas fa-key"></i> تغيير كلمة السر
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
   
</div>
  <!-- JavaScript for Confirmation -->
  <script>
    function confirmDelete(hasRequests) {
        let message = hasRequests ?
            "هذا المستخدم لديه طلبات استعارة. هل تريد حذف الطلبات ثم حذف المستخدم؟" :
            "هل أنت متأكد من أنك تريد حذف هذا المستخدم؟";
        return confirm(message);
    }
    </script>