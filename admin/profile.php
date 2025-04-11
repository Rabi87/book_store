<?php
require __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $check_sql = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error = "البريد الإلكتروني مسجل مسبقاً";
    } else {
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
            $_SESSION['user_name'] = $name;
        } else {
            $error = "خطأ في التحديث: " . $conn->error;
        }
    }
}

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

<form method="POST" action="">
    <div class="mb-3">
        <input type="text" name="name" placeholder="اسم المستخدم" class="form-control" value="<?php echo $user['name']; ?>" required>
    </div>
    <div class="mb-3">       
        <input type="email" name="email" class="form-control" placeholder="البريد الإلكتروني" value="<?php echo $user['email']; ?>" required>
    </div>
    <div class="mb-3">
        <div class="input-group">
            <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control"
                placeholder="كلمة المرور الجديدة (اختياري)"
                style="padding-right: 45px;"
            >
            <button 
                type="button" 
                class="btn btn-outline-secondary border-start-0" 
                onclick="togglePasswordVisibility()"
                style="position: absolute; right: 0; z-index: 10; background: none; border: none;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16" id="eyeIcon">
                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                </svg>
            </button>
        </div>
        <small class="text-muted">اتركه فارغاً إذا لم ترغب في التغيير</small>
    </div>

    <button type="submit" name="update_profile" class="btn btn-primary">
        حفظ التغييرات
    </button>
</form>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.setAttribute('class', 'bi bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.setAttribute('class', 'bi bi-eye');
    }
}
</script>