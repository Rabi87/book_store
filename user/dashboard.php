<?php

session_start();

// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';


// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'user') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// جلب بيانات المستخدم
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();

// جلب الكتب المستعارة
$sql_books = "SELECT books.title, transactions.start_date, transactions.end_date 
              FROM transactions 
              JOIN books ON transactions.book_id = books.id 
              WHERE transactions.user_id = $user_id 
              AND transactions.status = 'active'";
$result_books = $conn->query($sql_books);
?>
<!DOCTYPE html>
<html lang="ar">


<div class="container mt-5">
    <div class="row">
        <!-- البطاقة الشخصية -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">معلومات الحساب</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>الاسم:</strong> <?php echo $user['name']; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>البريد:</strong> <?php echo $user['email']; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>تاريخ التسجيل:</strong> 
                            <?php echo date('Y/m/d', strtotime($user['created_at'])); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- الكتب المستعارة -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">الكتب الحالية</h5>
                </div>
                <div class="card-body">
                    <?php if ($result_books->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم الكتاب</th>
                                        <th>تاريخ الاستعارة</th>
                                        <th>تاريخ الإرجاع</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($book = $result_books->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $book['title']; ?></td>
                                        <td><?php echo $book['start_date']; ?></td>
                                        <td><?php echo $book['end_date']; ?></td>
                                        <td><span class="badge bg-success">نشطة</span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">لا توجد كتب مستعارة حالياً</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>