<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';


// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) ){
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// معالجة حذف الكتاب (التحديث النهائي)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
if (isset($_GET['delete'])) {
    try {
        // تحويل المُعرف إلى عدد صحيح
        $book_id = (int)$_GET['delete'];
        
        // 1. التحقق من وجود استعارات مرتبطة
        $stmt = $conn->prepare("SELECT COUNT(*) FROM borrow_requests WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $borrow_count = $stmt->get_result()->fetch_row()[0];
        
        if ($borrow_count > 0) {
            throw new Exception("❌ لا يمكن حذف الكتاب بسبب وجود طلبات استعارة مرتبطة به!");
        }

        // 2. حذف الكتاب
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ تم حذف الكتاب بنجاح!";
        } else {
            throw new Exception("❌ فشل في الحذف: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    // إعادة التوجيه إلى نفس الصفحة
    header("Location: manage_books.php");
    exit();
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// عرض الرسائل (مُحدَّث)
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

// عرض رسائل النجاح/الخطأ
if(isset($_GET['success'])){
    echo '<div class="alert alert-success">'.$_GET['success'].'</div>';
}

// إعداد الترقيم
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $records_per_page;

// جلب عدد السجلات الكلي
$result = $conn->query("SELECT COUNT(*) AS total FROM books");
$row = $result->fetch_assoc();
$total_books = $row['total'];
$total_pages = ceil($total_books / $records_per_page);

// جلب البيانات الحالية
$sql = "SELECT * FROM books LIMIT $records_per_page OFFSET $offset";
$result = $conn->query($sql);
?>

<div class="container mt-5">
    <!-- جدول عرض الكتب -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>العنوان</th>
                <th>المؤلف</th>
                <th>النوع</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM books";
            $result = $conn->query($sql);
            
            while($row = $result->fetch_assoc()){
                echo '<tr>';
                echo '<td>'.$row['title'].'</td>';
                echo '<td>'.$row['author'].'</td>';
                echo '<td>'.$row['type'].'</td>';
                echo '<td>'.$row['quantity'].'</td>';
                echo '<td>'.$row['price'].' ر.س</td>';
                echo '<td>
                        <a href="edit_book.php?id='.$row['id'].'" class="btn btn-sm btn-warning">تعديل</a>
                        <a href="?delete='.$row['id'].'" class="btn btn-sm btn-danger" onclick="return confirm(\'هل أنت متأكد؟\')">حذف</a>
                      </td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>



    <!-- روابط الترقيم -->
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?php echo $current_page - 1; ?>">السابق</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php echo ($i == $current_page) ? 'class="active"' : ''; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?php echo $current_page + 1; ?>">التالي</a>
        <?php endif; ?>
    </div>
</div>


    <!-- نموذج إضافة كتاب -->
    <h4 class="mt-5">إضافة كتاب جديد</h4>
    <form action="<?= BASE_URL ?>process.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <input type="text" name="title" placeholder="عنوان الكتاب" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="text" name="author" placeholder="المؤلف" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <select name="type" class="form-select" required>
                    <option value="physical">كتاب فيزيائي</option>
                    <option value="e-book">كتاب إلكتروني</option>
                </select>
                <select name="category_id" class="form-select" required>
    <option value="">اختر التصنيف</option>
    <?php
    $categories = $conn->query("SELECT * FROM categories");
    while ($cat = $categories->fetch_assoc()):
    ?>
    <option value="<?= $cat['category_id'] ?>">
        <?= htmlspecialchars($cat['category_name']) ?>
    </option>
    <?php endwhile; ?>
</select>
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" name="quantity" placeholder="الكمية" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" step="0.01" name="price" placeholder="السعر" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="file" name="cover_image" class="form-control"  required>
            </div>
            <div class="col-md-12">
                <button type="submit" name="add_book" class="btn btn-primary">إضافة كتاب</button>
            </div>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php' ?>

