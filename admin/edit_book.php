<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "login.php"); 
    exit(); 
} 

// جلب بيانات الكتاب
$book = [];
if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id']; 
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?"); 
    $stmt->bind_param("i", $book_id); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
    $book = $result->fetch_assoc();
    
    if (!$book) {
        $_SESSION['error'] = "الكتاب غير موجود";
        header("Location: manage_books.php");
        exit();
    }
} else {
    header("Location: manage_books.php");
    exit();
}
?>



<!-- بداية محتوى الصفحة -->
<div class="container mt-5">
    <!-- عرض رسائل الخطأ أو النجاح -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?>
    </div> <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h3 class="mb-4">تعديل معلومات الكتاب</h3>

    <!-- نموذج التعديل -->
    <form action="<?= BASE_URL ?>process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="book_id" value="<?= isset($book['id']) ? $book['id'] : '' ?>">

        <!-- المعلومات الأساسية -->
        <div class="row">
            <!-- العنوان والمؤلف -->
            <div class="col-md-6 mb-3">
                <label class="form-label">عنوان الكتاب</label>
                <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" class="form-control"
                    required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">المؤلف</label>
                <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" class="form-control"
                    required>
            </div>

            <!-- النوع والتصنيف -->
            <div class="col-md-6 mb-3">
                <label class="form-label">نوع الكتاب</label>
                <select name="type" class="form-select" required>
                    <option value="physical" <?= ($book['type'] === 'physical') ? 'selected' : '' ?>>كتاب فيزيائي
                    </option>
                    <option value="e-book" <?= ($book['type'] === 'e-book') ? 'selected' : '' ?>>كتاب إلكتروني</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">التصنيف</label>
                <select name="category_id" class="form-select" required>
                    <option value="">اختر التصنيف</option>
                    <?php
                $categories = $conn->query("SELECT * FROM categories");
                while ($cat = $categories->fetch_assoc()):
                ?>
                    <option value="<?= $cat['category_id'] ?>"
                        <?= ($book['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">

            <select name="material_type" class="form-select" required>
                <option value="كتاب" <?= $book['material_type'] == 'كتاب' ? 'selected' : '' ?>>كتاب</option>
                <option value="مجلة" <?= $book['material_type'] == 'مجلة' ? 'selected' : '' ?>>مجلة</option>
                <option value="جريدة" <?= $book['material_type'] == 'جريدة' ? 'selected' : '' ?>>جريدة</option>
            </select>

            <input type="number" name="page_count" value="<?= $book['page_count'] ?>" class="form-control">
            <input type="date" name="publication_date" value="<?= $book['publication_date'] ?>" class="form-control">
            <input type="text" name="isbn" value="<?= $book['isbn'] ?>" class="form-control">

            <!-- الكمية والسعر -->
            <div class="col-md-6 mb-3">
                <label class="form-label">الكمية المتاحة</label>
                <input type="number" name="quantity" value="<?= $book['quantity'] ?>" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">السعر (ل.س)</label>
                <input type="number" step="0.01" name="price" value="<?= $book['price'] ?>" class="form-control"
                    required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">التقييم</label>
                <input type="number" name="evaluation" value="<?= $book['evaluation'] ?>" class="form-control" required
                    min="1" max="5">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">تفاصيل</label>
                <textarea name="description" class="form-control"
                    required> <?= htmlspecialchars($book['description']) ?></textarea>
            </div>

            <!-- قسم الصورة -->
            <div class="col-md-6 mb-3">
                <label class="form-label">صورة الغلاف الحالية</label>
                <?php if (!empty($book['cover_image'])): ?>
                <img src="<?= BASE_URL . $book['cover_image'] ?>" class="img-fluid rounded mb-2"
                    style="max-width: 200px;">
                <?php else: ?>
                <div class="text-muted">لا توجد صورة مرفقة</div>
                <?php endif; ?>
                <label class="form-label mt-2">تغيير الصورة</label>
                <input type="file" name="cover_image" class="form-control">
                <small class="text-muted">اختياري - اتركه فارغًا للحفاظ على الصورة الحالية</small>
            </div>

            <!-- قسم الملف -->
            <div class="col-md-6 mb-3">
                <label class="form-label">الملف الحالي</label>
                <?php if (!empty($book['file_path'])): ?>
                <a href="<?= BASE_URL . $book['file_path'] ?>" class="btn btn-outline-primary btn-sm"
                    target="_blank">عرض الملف</a>
                <?php else: ?>
                <div class="text-muted">لا يوجد ملف مرفق</div>
                <?php endif; ?>
                <label class="form-label mt-2">تغيير الملف</label>
                <input type="file" name="file_path" class="form-control">
                <small class="text-muted">اختياري - اتركه فارغًا للحفاظ على الملف الحالي</small>
            </div>
        </div>

        <!-- زر التحديث -->
        <div class="text-center mt-4">
            <button type="submit" name="update_book" class="btn btn-warning px-5">حفظ التعديلات</button>
        </div>
    </form>


<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'نجاح!',
    text: '<?= $_SESSION['success'] ?>'
});
</script>

<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'خطأ!',
    text: '<?= $_SESSION['error'] ?>'
});
</script>

<?php unset($_SESSION['error']); ?>
<?php endif; ?>
