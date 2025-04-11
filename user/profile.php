<?php

require __DIR__ . '/../includes/config.php';


// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = $success = '';

// تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // التحقق من البريد الإلكتروني
    $check_sql = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error = "البريد الإلكتروني مسجل مسبقاً";
    } else {
        // تحديث كلمة المرور إذا تم إدخالها
        $password_update = '';
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $password_update = ", password = '$hashed_password'";
        }
        
        $sql = "UPDATE users 
                SET name = '$name', email = '$email' $password_update 
                WHERE id = $user_id";
        
        if ($conn->query($sql) === TRUE) {
            $success = "تم تحديث البيانات بنجاح";
            $_SESSION['user_name'] = $name; // تحديث الجلسة
        } else {
            $error = "خطأ في التحديث: " . $conn->error;
        }
    }
}

// جلب البيانات الحالية
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<div class="container mt-5">
    <h3>الإشعارات</h3>
    <?php
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ?>
    
    <?php foreach ($notifications as $notif): ?>
    <div class="card mb-2 <?= $notif['is_read'] ? '' : 'border-primary' ?>">
        <div class="card-body">
            <p><?= $notif['message'] ?></p>
            <?php if ($notif['link']): ?>
            <a href="<?= $notif['link'] ?>" class="btn btn-sm btn-outline-primary">
                الانتقال <i class="fas fa-arrow-left"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<form method="POST" action="">
    <div class="mb-3">
        <label class="form-label">الاسم الكامل</label>
        <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">كلمة المرور الجديدة (اختياري)</label>
        <input type="password" name="password" class="form-control">
        <small class="text-muted">اتركه فارغاً إذا لم ترغب في التغيير</small>
    </div>

    <button type="submit" name="update_profile" class="btn btn-primary">
        حفظ التغييرات
    </button>
</form>