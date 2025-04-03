<?php
// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المكتبة الذكية</title>
    <!-- الخطوط العربية -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
   
    
    <style>
        .navbar-brand img { height: 40px; }
        .nav-link { transition: all 0.3s; }
        .nav-link:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="شعار المكتبة">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link"  href="<?= BASE_URL ?>user/dashboard.php" > لوحة التحكم </a>
                    </li>
                    <?php if($_SESSION['user_type'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="<?= BASE_URL ?>admin/manage_books.php">الإدارة 
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>logout.php">تسجيل الخروج</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>login.php">تسجيل الدخول</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>register.php">تسجيل جديد</a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="text-white me-3">
                    <span>مرحبًا، <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span class="badge bg-light text-dark ms-2">
                        <?= ($_SESSION['user_type'] == 'admin') ? 'مدير' : 'مستخدم' ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

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

<main class="flex-grow-1 container my-5">