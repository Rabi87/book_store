<?php
// ملف index.php
session_start();
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// استعلامات الكتب
$physical_books = $conn->query("SELECT * FROM books WHERE type = 'physical' AND quantity > 0");
$e_books = $conn->query("SELECT * FROM books WHERE type = 'e-book'");

?>

<div class="container my-5">
    <h1 class="text-center mb-4">مرحبًا بكم في المكتبة الذكية</h1>

    <!-- قسم الكتب الفيزيائية -->
    <div class="row mb-5">
        <h3 class="mb-3">الكتب الفيزيائية</h3>
        <?php if($physical_books->num_rows > 0): ?>
        <?php while($book = $physical_books->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text">
                        <strong>المؤلف:</strong> <?= htmlspecialchars($book['author']) ?><br>
                        <strong>المتبقي:</strong> <?= $book['quantity'] ?>
                    </p>
                    <?php if(isset($_SESSION['user_id'])): ?>

                        <form method="POST" action="<?= BASE_URL ?>process.php" style="display: inline;">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                          <button type="submit" name="borrow_book" class="btn btn-primary"
                             onclick="return confirm('هل تريد استعارة هذا الكتاب؟')">
                            استعارة fالكتاب
                          </button>
                        </form>
                    <a href="<?= BASE_URL ?>process.php?borrow=<?= $book['id'] ?>" class="btn btn-primary"
                        onclick="return confirm('هل تريد استعارة هذا الكتاب؟')">
                        استعارة الكتاب
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#loginModal">
                        سجل دخول للاستعارة
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">لا توجد كتب فيزيائية متاحة حاليًا</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- قسم الكتب الإلكترونية -->
    <div class="row">
        <h3 class="mb-3">الكتب الإلكترونية</h3>
        <?php if($e_books->num_rows > 0): ?>
        <?php while($book = $e_books->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text">
                        <strong>المؤلف:</strong> <?= htmlspecialchars($book['author']) ?><br>
                        <strong>السعر:</strong> <?= $book['price'] ?> ر.س
                    </p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>process.php?buy=<?= $book['id'] ?>" class="btn btn-success"
                        onclick="return confirm('هل تريد شراء هذا الكتاب؟')">
                        شراء الكتاب
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#loginModal">
                        سجل دخول للشراء
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">لا توجد كتب إلكترونية متاحة حاليًا</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php

require __DIR__ . '/includes/footer.php';
?>