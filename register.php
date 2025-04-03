<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';
 ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">إنشاء حساب جديد</div>
                <div class="card-body">
                    <form action="process.php" method="POST" onsubmit="return validatePassword()">
                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم الكامل</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" class="form-control" id="confirm_password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary">تسجيل</button>
                    </form>
                    <div class="mt-3">
                        لديك حساب بالفعل؟ <a href="login.php">سجل دخول</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validatePassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        alert('كلمة المرور غير متطابقة!');
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>