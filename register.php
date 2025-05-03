<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();}
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';
// جلب التصنيفات باستخدام prepared statement
$stmt = $conn->prepare("SELECT category_id, category_name FROM categories");
$stmt->execute();
$categories = $stmt->get_result();
// جلب تصنيفات المستخدم المختارة
$user_categories = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT category_id FROM user_categories WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_categories[] = $row['category_id'];
    }
}
?>
<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'انتبه.. !',
    text: '<?= $_SESSION['error'] ?>'
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-user-plus me-2"></i> إنشاء حساب جديد
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form action="process.php" method="POST" id="regForm">
                        <!-- الخطوة 1 -->
                        <div id="step1">
                            <!-- حقل الاسم -->
                            <div class="mb-4 input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-id-card text-info"></i>
                                </span>
                                <input type="text" class="form-control" 
                                    name="name" 
                                    placeholder="الاسم الكامل" 
                                    required>
                            </div>

                            <!-- حقل البريد -->
                            <div class="mb-4 input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-envelope text-info"></i>
                                </span>
                                <input type="email" class="form-control" 
                                    name="email" 
                                    placeholder="البريد الإلكتروني" 
                                    required>
                            </div>

                            <!-- حقل كلمة المرور -->
                            <div class="mb-4 input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-lock text-info"></i>
                                </span>
                                <input type="password" class="form-control" 
                                    name="password" 
                                    id="password"
                                    placeholder="كلمة المرور" 
                                    required>
                            </div>

                            <!-- تأكيد كلمة المرور -->
                            <div class="mb-4 input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-check-circle text-info"></i>
                                </span>
                                <input type="password" class="form-control" 
                                    id="confirm_password"
                                    placeholder="تأكيد كلمة المرور" 
                                    required>
                            </div>

                            <button type="button" 
                                class="btn btn-primary w-100 py-2 fw-bold" 
                                onclick="nextStep()">
                                التالي <i class="fas fa-arrow-left ms-2"></i>
                            </button>
                        </div>

                        <!-- الخطوة 2 -->
                        <div id="step2" style="display:none;">
                            <h5 class="mb-4 fw-bold text-info">
                                <i class="fas fa-tags me-2"></i> اختر التصنيفات المفضلة
                            </h5>
                            <div class="row g-3">
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                <div class="col-md-4">
                                    <div class="category-card">
                                        <input type="checkbox" 
                                            name="categories[]"
                                            value="<?= $cat['category_id'] ?>" 
                                            id="cat-<?= $cat['category_id'] ?>"
                                            class="form-check-input visually-hidden">
                                        <label for="cat-<?= $cat['category_id'] ?>"
                                            class="d-block p-3 rounded-3 border bg-hover-light">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <?= htmlspecialchars($cat['category_name']) ?>
                                            </h6>
                                        </label>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" 
                                    class="btn btn-secondary px-4"
                                    onclick="prevStep()">
                                    <i class="fas fa-arrow-right me-2"></i> السابق
                                </button>
                                <button type="submit" 
                                    class="btn btn-success px-4"
                                    name="register">
                                    <i class="fas fa-check-circle me-2"></i> تسجيل
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        لديك حساب بالفعل؟ 
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i> سجل دخول
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- الأنماط -->
<style>
.bg-gradient-info {
    background: linear-gradient(135deg, #17ead9 0%, #6078ea 100%);
}

.category-card input:checked + label {
    background: #e3f2fd !important;
    border: 2px solid #17ead9 !important;
}

.bg-hover-light:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>


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