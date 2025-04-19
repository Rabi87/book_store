<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// جلب التصنيفات من قاعدة البيانات
$categories = $conn->query("SELECT * FROM categories");
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">إنشاء حساب جديد</div>
                <div class="card-body">
                    <form action="process.php" method="POST" id="regForm">
                        <!-- الخطوة 1: البيانات الأساسية -->
                        <div id="step1">
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
                            <button type="button" class="btn btn-primary" onclick="nextStep()">التالي</button>
                        </div>

                        <!-- الخطوة 2: اختيار التصنيفات -->
                        <div id="step2" style="display:none;">
                            <h5>اختر التصنيفات المفضلة:</h5>
                            <div class="row">
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check">
                                        <input 
                                            type="checkbox" 
                                            name="categories[]" 
                                            value="<?= $cat['category_id'] ?>">
                                        <label class="form-check-label">
                                            <?= htmlspecialchars($cat['category_name']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-secondary" onclick="prevStep()">السابق</button>
                                <button type="submit" class="btn btn-success" name="register">تسجيل</button>
                            </div>
                        </div>
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
function nextStep() {
    const pass = document.getElementById('password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (pass !== confirmPass) {
        alert('كلمة المرور غير متطابقة!');
        return;
    }
    
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}

function prevStep() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
}

document.getElementById('regForm').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[name="categories[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('الرجاء اختيار تصنيف واحد على الأقل!');
    }
});
</script>

<?php include 'includes/footer.php'; ?>