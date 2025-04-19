<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();}
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// جلب الكتب المقترحة
$recommended_books = [];
if (isset($_SESSION['user_id'])) {
    $query = "
        SELECT b.* 
        FROM books b
        JOIN user_categories uc ON b.category_id = uc.category_id
        WHERE uc.user_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $recommended_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// جلب جميع الكتب
$all_books = $conn->query("SELECT * FROM books");
?>

<!-- قسم البحث -->
<section class="search-section">
    <div class="search-container">
        <input type="text" class="search-box" placeholder="ابحث عن كتاب...">
        <select class="search-box">
            <option>جميع التصنيفات</option>
            <option>روايات</option>
            <option>علوم</option>
            <option>تكنولوجيا</option>
        </select>
        <button class="search-btn">بحث</button>
    </div>
</section>

<!-- عرض الكتب المقترحة -->
<?php if (!empty($recommended_books)): ?>
<h3>📚 كتب مخصصة لك</h3>
<div class="books-grid">
    <?php foreach ($recommended_books as $book): ?>
    <div class="book-card">
        <!-- بطاقة كتاب -->
        <div class="book-image">
            <img src="<?= BASE_URL ?>assets/images/books/<?= $book['cover_image'] ?>" class="card-img"
                alt="غلاف الكتاب">
        </div>
        <div class="book-info">
            <h5><?= htmlspecialchars($book['title']) ?></h5>
            <p><?= htmlspecialchars($book['author']) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- عرض جميع الكتب -->
<h3>🏛 جميع الكتب المتاحة</h3>
<div class="books-grid">
    <?php while ($book = $all_books->fetch_assoc()): ?>
    <div class="book-card">
        <div class="book-image">
            <img src="<?= BASE_URL ?>assets/images/books/<?= $book['cover_image'] ?>" class="card-img-top"
                alt="غلاف الكتاب">
        </div>
        <div class="book-info">
            <h5><?= htmlspecialchars($book['title']) ?></h5>
            <p><?= htmlspecialchars($book['author']) ?></p>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php require __DIR__ . '/includes/footer.php';?>