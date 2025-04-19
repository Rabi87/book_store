<?php
session_start();
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// معالجة معاملات البحث والتصفية
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? 'all';

// بناء الاستعلام الأساسي
$base_query = "
    SELECT 
        books.*, 
        categories.category_name,
        books.price,
        books.description,
        books.cover_image 
    FROM books
    INNER JOIN categories 
        ON books.category_id = categories.category_id
    WHERE (books.type = 'physical' AND books.quantity > 0) 
    OR books.type = 'e-book'
";

$params = [];
$types = '';

// إضافة شروط البحث
if (!empty($search)) {
    $base_query .= " AND (books.title LIKE ? OR books.author LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term);
    $types .= 'ss';
}

// إضافة فلتر التصنيف
if ($category_filter !== 'all') {
    $base_query .= " AND categories.category_id = ?";
    array_push($params, $category_filter);
    $types .= 'i';
}

// دالة مساعدة لتنفيذ الاستعلامات
function executeQuery($conn, $query, $params, $types) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("خطأ في إعداد الاستعلام: " . $conn->error);
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// تنفيذ الاستعلامات
$physical_books = executeQuery($conn, $base_query . " AND books.type = 'physical'", $params, $types);
$e_books = executeQuery($conn, $base_query . " AND books.type = 'e-book'", $params, $types);

// جلب الكتب المقترحة
$recommended_books = [];
if (isset($_SESSION['user_id'])) {
    $query = "
    SELECT 
        b.*, 
        categories.category_name 
    FROM books b
    JOIN user_categories uc 
        ON b.category_id = uc.category_id
    INNER JOIN categories 
        ON b.category_id = categories.category_id
    WHERE uc.user_id = ?
";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $recommended_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// جلب الكتب المقترحة
$rated_books = [];

$query = "
SELECT 
    b.*, 
    categories.category_name 
FROM books b
INNER JOIN categories 
    ON b.category_id = categories.category_id
WHERE b.evaluation > 2
";
$stmt = $conn->prepare($query);

$stmt->execute();
$rated_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<style>
.flip-card {
    perspective: 1000px;
    min-height: 200px;
    margin-bottom: 1.5rem;
}

.flip-inner {
    position: relative;
    width: 60%;
    height: 100%;
    transition: transform 0.6s;
    transform-style: preserve-3d;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.flip-card:hover .flip-inner {
    transform: rotateY(180deg);
}

.flip-front,
.flip-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 1px;
    overflow: hidden;
}

.flip-back {
    background: #000;
    color: #fff;
    padding: 15px;
    transform: rotateY(180deg);
    display: flex;
    flex-direction: column;
}

.card-actions {
    margin-top: auto;
    display: flex;
    gap: 10px;
    justify-content: center;
}

/* تنسيقات النافذة المنبثقة */
#bookDetailsModal .modal-content {
    background: #1a1a1a;
    color: #fff;
}

#bookDetailsModal img {
    max-height: 300px;
    object-fit: cover;
}
</style>


<div>
    <!-- شريط البحث (معدل باستخدام Bootstrap) -->
    <div class="home-search mb-4 text-center">
        <input type="text" id="searchInput" class="form-control rounded-pill w-100 mx-auto" style="max-width: 400px;"
            placeholder="ابحث عن كتاب...">
    </div>
    <!-- شريط التصنيفات (معدل باستخدام Bootstrap) -->
    <div class="filter-bar d-flex justify-content-center gap-2 mb-4 flex-wrap">
        <button class="filter-btn btn btn-outline-primary rounded-pill active" data-category="all">الكل</button>
        <button class="filter-btn btn btn-outline-primary rounded-pill" data-category="علم النفس">علم النفس</button>
        <button class="filter-btn btn btn-outline-primary rounded-pill" data-category="تقنية">تقنية</button>
        <button class="filter-btn btn btn-outline-primary rounded-pill" data-category="تنمية ذاتية">تنمية
            ذاتية</button>
        <button class="filter-btn btn btn-outline-primary rounded-pill" data-category="تاريخ">تاريخ</button>
        <button class="filter-btn btn btn-outline-primary rounded-pill" data-category="تاريخ">...</button>
    </div>

    <div class="accordion">
        <?php if (!empty($rated_books)): ?>
        <button class="accordion-header"> الأعلى تقييماُ</button>
        <div class="accordion-content">
            <div class="row g-4">
                <?php foreach ($rated_books as $book): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="flip-card h-100">
                        <div class="flip-inner">
                            <!-- الوجه الأمامي -->
                            <div class="flip-front">
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($book['cover_image']) ?>"
                                    alt="غلاف الكتاب">
                            </div>

                            <!-- الوجه الخلفي -->
                            <div class="flip-back">
                                <h6 class="fw-bold"><?= htmlspecialchars($book['title']) ?></h6>
                                <p class="small"><?= htmlspecialchars($book['author']) ?></p>

                                <div class="rating-stars mb-3">
                                    <?= str_repeat('★', $book['evaluation']) . str_repeat('☆', 5 - $book['evaluation']) ?>
                                </div>

                                <div class="card-actions">
                                    <!-- أيقونة التفاصيل -->
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#bookDetails" onclick="loadBookDetails(
                                            '<?= addslashes($book['title']) ?>',
                                            '<?= addslashes($book['author']) ?>',
                                            '<?= addslashes($book['category_name']) ?>',
                                            <?= $book['evaluation'] ?>,
                                            <?= $book['price'] ?>,
                                            '<?= addslashes($book['description']) ?>',
                                            '<?= htmlspecialchars($book['cover_image']) ?>'
                                        )">
                                        <i class="fas fa-info"></i>
                                    </button>
                                    <!-- الأزرار كأيقونات -->
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                    <div class="card-actions">
                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="borrow"
                                                class="btn btn-primary btn-sm rounded-circle" title="استعارة الكتاب">
                                                <i class="fas fa-book"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>

                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="purchase"
                                                class="btn btn-success btn-sm rounded-circle" title="شراء الكتاب">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm rounded-circle" data-bs-toggle="modal"
                                        data-bs-target="#loginModal" title="تسجيل الدخول">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="accordion">
        <?php if (!empty($recommended_books)): ?>
        <button class="accordion-header"> المفضلة</button>
        <div class="accordion-content">
            <div class="row g-4">
                <?php foreach ($recommended_books as $book): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="flip-card h-100">
                        <div class="flip-inner">
                            <!-- الوجه الأمامي -->
                            <div class="flip-front">
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($book['cover_image']) ?>"
                                    alt="غلاف الكتاب">
                            </div>

                            <!-- الوجه الخلفي -->
                            <div class="flip-back">
                                <h6 class="fw-bold"><?= htmlspecialchars($book['title']) ?></h6>
                                <p class="small"><?= htmlspecialchars($book['author']) ?></p>

                                <div class="rating-stars mb-3">
                                    <?= str_repeat('★', $book['evaluation']) . str_repeat('☆', 5 - $book['evaluation']) ?>
                                </div>

                                <div class="card-actions">
                                    <!-- أيقونة التفاصيل -->
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#bookDetails" onclick="loadBookDetails(
                                            '<?= addslashes($book['title']) ?>',
                                            '<?= addslashes($book['author']) ?>',
                                            '<?= addslashes($book['category_name']) ?>',
                                            <?= $book['evaluation'] ?>,
                                            <?= $book['price'] ?>,
                                            '<?= addslashes($book['description']) ?>',
                                            '<?= htmlspecialchars($book['cover_image']) ?>'
                                        )">
                                        <i class="fas fa-info"></i>
                                    </button>
                                    <!-- الأزرار كأيقونات -->
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                    <div class="card-actions">
                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="borrow"
                                                class="btn btn-primary btn-sm rounded-circle" title="استعارة الكتاب">
                                                <i class="fas fa-book"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>

                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="purchase"
                                                class="btn btn-success btn-sm rounded-circle" title="شراء الكتاب">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm rounded-circle" data-bs-toggle="modal"
                                        data-bs-target="#loginModal" title="تسجيل الدخول">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="accordion">
        <button class="accordion-header"> المكتبة الشاملة</button>
        <div class="accordion-content">
            <div class="row g-4">
                <?php while($book = $physical_books->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="flip-card h-100">
                        <div class="flip-inner">
                            <!-- الوجه الأمامي -->
                            <div class="flip-front">
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($book['cover_image']) ?>"
                                    alt="غلاف الكتاب">
                            </div>

                            <!-- الوجه الخلفي -->
                            <div class="flip-back">
                                <h6 class="fw-bold"><?= htmlspecialchars($book['title']) ?></h6>
                                <p class="small"><?= htmlspecialchars($book['author']) ?></p>

                                <div class="rating-stars mb-3">
                                    <?= str_repeat('★', $book['evaluation']) . str_repeat('☆', 5 - $book['evaluation']) ?>
                                </div>

                                <div class="card-actions">
                                    <!-- أيقونة التفاصيل -->
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#bookDetails" onclick="loadBookDetails(
                                                '<?= addslashes($book['title']) ?>',
                                                '<?= addslashes($book['author']) ?>',
                                                '<?= addslashes($book['category_name']) ?>',
                                                <?= $book['evaluation'] ?>,
                                                <?= $book['price'] ?>,
                                                '<?= htmlspecialchars($book['description']) ?>',
                                                '<?= htmlspecialchars($book['cover_image']) ?>'
                                            )">
                                        <i class="fas fa-info"></i>
                                    </button>
                                    <!-- الأزرار كأيقونات -->
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                    <div class="card-actions">
                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="borrow"
                                                class="btn btn-primary btn-sm rounded-circle" title="استعارة الكتاب">
                                                <i class="fas fa-book"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>

                                        <form method="POST" action="process.php">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" name="action" value="purchase"
                                                class="btn btn-success btn-sm rounded-circle" title="شراء الكتاب">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                                        </form>
                                    </div>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm rounded-circle" data-bs-toggle="modal"
                                        data-bs-target="#loginModal" title="تسجيل الدخول">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <!-- نافذة التفاصيل -->
    <div class="modal fade" id="bookDetails">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تفاصيل الكتاب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img id="modalCover" src="" class="img-fluid">
                        </div>
                        <div class="col-md-8">
                            <h4 id="modalTitle"></h4>
                            <p><strong>المؤلف:</strong> <span id="modalAuthor"></span></p>
                            <p><strong>التصنيف:</strong> <span id="modalCategory"></span></p>
                            <p><strong>التقييم:</strong> <span id="modalRating"></span></p>
                            <p><strong>السعر:</strong> <span id="modalPrice"></span> ل.س</p>
                            <p id="modalDesc"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مودال تسجيل الدخول -->
    <div class="modal fade" id="loginModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تسجيل الدخول مطلوب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>يجب تسجيل الدخول لإكمال هذه العملية</p>
                        <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
                        <a href="register.php" class="btn btn-secondary">إنشاء حساب</a>
                    </div>
                </div>
            </div>
        </div>
</div>
<script>
        function loadBookDetails(title, author, category, rating, price, desc, cover) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalAuthor').textContent = author;
            document.getElementById('modalCategory').textContent = category;
            document.getElementById('modalPrice').textContent = price;
            document.getElementById('modalDesc').textContent = desc;
            document.getElementById('modalCover').src = "<?= BASE_URL ?>" + cover;

            // توليد النجوم
            const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
            document.getElementById('modalRating').innerHTML = stars;
        }
    </script>

<?php require __DIR__ . '/includes/footer.php'; ?>