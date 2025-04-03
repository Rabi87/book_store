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

// معالجة حذف الكتاب
if(isset($_GET['delete'])){
    $book_id = $_GET['delete'];
    $sql = "DELETE FROM books WHERE id = $book_id";
    
    if($conn->query($sql) === TRUE){
        $success = "تم حذف الكتاب بنجاح";
    }
}

// عرض رسائل النجاح/الخطأ
if(isset($_GET['success'])){
    echo '<div class="alert alert-success">'.$_GET['success'].'</div>';
}
?>

<div class="container mt-5">
    <h2 class="mb-4">إدارة الكتب</h2>
    
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

    <!-- نموذج إضافة كتاب -->
    <h4 class="mt-5">إضافة كتاب جديد</h4>
    <form action="<?= BASE_URL ?>process.php" method="POST">
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
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" name="quantity" placeholder="الكمية" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="number" step="0.01" name="price" placeholder="السعر" class="form-control" required>
            </div>
            <div class="col-md-12">
                <button type="submit" name="add_book" class="btn btn-primary">إضافة كتاب</button>
            </div>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php' ?>

