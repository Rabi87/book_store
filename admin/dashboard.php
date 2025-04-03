<?php
// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) ){
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>

<div class="container mt-5">
    <h2 class="mb-4">لوحة التحكم الإدارية</h2>
    
    <!-- إضافة كتاب جديد -->
    <form action="<?= BASE_URL ?>process.php" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="title" placeholder="عنوان الكتاب" class="form-control" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="author" placeholder="المؤلف" class="form-control" required>
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select" required>
                    <option value="physical">كتاب فيزيائي</option>
                    <option value="e-book">كتاب إلكتروني</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="quantity" placeholder="الكمية" class="form-control" required>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" name="price" placeholder="السعر" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add_book" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>إضافة كتاب
                </button>
            </div>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>