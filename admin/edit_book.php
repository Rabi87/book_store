<?php
session_start();
require __DIR__ . '/../includes/config.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// جلب بيانات الكتاب
$book = [];
if(isset($_GET['id'])){
    $book_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    
    if(!$book){
        $_SESSION['error'] = "الكتاب غير موجود";
        header("Location: manage_books.php");
        exit();
    }
}

// عرض النموذج
?>
<div class="container mt-5">
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <h4>تعديل الكتاب</h4>
    <form action="<?= BASE_URL ?>process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <input type="text" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="text" name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <select name="type" class="form-select" required>
                    <option value="physical" <?= ($book['type'] ?? '') === 'physical' ? 'selected' : '' ?>>كتاب فيزيائي</option>
                    <option value="e-book" <?= ($book['type'] ?? '') === 'e-book' ? 'selected' : '' ?>>كتاب إلكتروني</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <select name="category_id" class="form-select" required>
                    <option value="">اختر التصنيف</option>
                    <?php
                    $categories = $conn->query("SELECT * FROM categories");
                    while ($cat = $categories->fetch_assoc()):
                    ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($book['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" name="quantity" value="<?= $book['quantity'] ?? '' ?>" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" step="0.01" name="price" value="<?= $book['price'] ?? '' ?>" class="form-control" required>
            </div>
            <div class="col-md-12">
                <button type="submit" name="update_book" class="btn btn-warning">تحديث</button>
            </div>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php' ?>