<?php
session_start();
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// معالجة معاملات البحث والتصفية
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? 'all';

// بناء الاستعلام الأساسي مع شروط التصفية
$base_query = "
    SELECT 
        books.*, 
        categories.category_name 
    FROM books
    INNER JOIN categories 
        ON books.category_id = categories.category_id
    WHERE 1=1
    AND (books.type = 'physical' AND books.quantity > 0 OR books.type = 'e-book')
";

// إضافة شروط البحث
$params = [];
$types = '';

if (!empty($search)) {
    $base_query .= " AND (books.title LIKE ? OR books.author LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

// إضافة فلتر التصنيف
if ($category_filter !== 'all') {
    $base_query .= " AND categories.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

// تقسيم الاستعلام حسب النوع
$physical_query = $base_query . " AND books.type = 'physical'";
$e_book_query = $base_query . " AND books.type = 'e-book'";

// تنفيذ الاستعلامات
$stmt_physical = $conn->prepare($physical_query);
if ($types !== '') $stmt_physical->bind_param($types, ...$params);
$stmt_physical->execute();
$physical_books = $stmt_physical->get_result();

$stmt_e = $conn->prepare($e_book_query);
if ($types !== '') $stmt_e->bind_param($types, ...$params);
$stmt_e->execute();
$e_books = $stmt_e->get_result();
?>

<div class="row mb-5 bg-light p-3 rounded-3 shadow-sm">
    <!-- شريط البحث والتصفية -->
    <div class="row mb-5 bg-light p-3 rounded-3 shadow-sm">
        <div class="col-12">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-8 mb-3">
                    <input type="text" name="search" class="form-control" placeholder="ابحث عن كتاب، مؤلف، أو تصنيف..."
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="col-md-3 mb-3">
                    <select name="category" class="form-select">
                        <option value="all">جميع التصنيفات</option>
                        <?php
                        $categories = $conn->query("SELECT * FROM categories");
                        while ($cat = $categories->fetch_assoc()):
                        ?>
                        <option value="<?= $cat['category_id'] ?>"
                            <?= ($category_filter == $cat['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-1 mb-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

   <!-- قسم الكتب -->
   <div class="row">
        <!-- الكتب الفيزيائية -->
        <?php if($physical_books->num_rows > 0): ?>
        <div class="col-12 mb-5">
            <h3 class="mb-4 text-primary"><i class="fas fa-book-open me-2"></i>الكتب الفيزيائية</h3>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-1">
                <?php while($book = $physical_books->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                    <div class="card-image-container">
                            <img src="<?= BASE_URL ?>assets/images/books/<?= $book['cover_image'] ?>" 
                                class="fixed-size-image"
                                 alt="<?= htmlspecialchars($book['title']) ?>">
                        </div>
                        <div class="card-body p-2 d-flex flex-column">
                            <h6 class="card-title mb-1 text-truncate"><?= htmlspecialchars($book['title']) ?></h6>
                            <p class="card-text small text-muted mb-2 text-truncate">
                                <?= htmlspecialchars($book['author']) ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="badge bg-primary text-truncate">
                                        <?= htmlspecialchars($book['category_name']) ?>
                                    </span>
                                    <span class="text-success">
                                        <?= $book['quantity'] ?> <i class="fas fa-box"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent p-2 border-top">
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="process.php">
                                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                <button type="submit" name="borrow_book" 
                                        class="btn btn-primary btn-sm w-100 py-1"
                                        onclick="return confirm('هل تريد استعارة هذا الكتاب؟')">
                                    <i class="fas fa-hand-holding me-1"></i>استعارة
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-outline-primary btn-sm w-100 py-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt me-1"></i>تسجيل الدخول
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>


        <!-- الكتب الإلكترونية -->
        <?php if($e_books->num_rows > 0): ?>
        <div class="col-12">
            <h3 class="mb-4 text-success"><i class="fas fa-laptop"></i> الكتب الإلكترونية</h3>
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4">
                <?php while($book = $e_books->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow">
                        <img src="<?= BASE_URL ?>assets/images/books/<?= $book['cover_image'] ?>" class="card-img-top"
                            style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($book['title']) ?>">

                        <div class="card-body">
                            <h6 class="card-title text-truncate"><?= htmlspecialchars($book['title']) ?></h6>
                            <p class="card-text small text-muted">
                                <?= htmlspecialchars($book['author']) ?>
                            </p>
                            <div class="d-flex justify-content-between small">
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($book['category_name']) ?>
                                </span>
                                <span class="text-success">
                                    <?= $book['price'] ?> <i class="fas fa-coins"></i>
                                </span>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent">
                            <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="<?= BASE_URL ?>process.php?buy=<?= $book['id'] ?>"
                                class="btn btn-success btn-sm w-100"
                                onclick="return confirm('هل تريد شراء هذا الكتاب؟')">
                                <i class="fas fa-shopping-cart"></i> شراء
                            </a>
                            <?php else: ?>
                            <button class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal"
                                data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">لا توجد كتب إلكترونية متاحة</div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>