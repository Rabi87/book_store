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
    <link href="<?= BASE_URL ?>assets/bootstrap/css/bootstrap.css" rel="stylesheet" >
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet" >

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    .navbar-brand img {
        height: 60px;
    }

    /* التعديلات الجديدة */
    .navbar {
        background-color: rgb(72, 23, 29) !important;
        /* اللون العنابي */
        padding-top: 0.05rem;
        padding-bottom: 0.05rem;
        /* تقليل ارتفاع الهيدر */
    }

    .nav-link,
    .navbar-brand,
    .text-warning,
    .navbar .text-white {
        color: #D4AF37 !important;
        /* اللون الذهبي */
    }

    .nav-link:hover {
        color: #FFD700 !important;
        /* ذهبي فاتح عند hover */
    }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
                <img src="<?= BASE_URL ?>assets/images/logo.png" alt="شعار المكتبة">
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>index.php"> المكتبة </a>
                    </li>

                    <?php if(isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>about.php"> المنتدى </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>about.php"> التعاملات </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="<?= BASE_URL ?><?= ($_SESSION['user_type'] == 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">
                            لوحة التحكم
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link "
                            href="<?= BASE_URL ?><?= ($_SESSION['user_type'] == 'admin') ?'admin/manage_books.php': 'user/dashboard.php' ?>">الإدارة</a>
                    </li>
                    </u>
                    <ul class="navbar-nav ms-auto gap-3">

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
                    <div class="text-warning me-3">
                        <span class="badge bg-light text-dark ms-2">
                            <?= htmlspecialchars($_SESSION['user_name']) ?></span>
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
        