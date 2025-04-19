<?php
// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
require __DIR__ . '/../includes/config.php';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مكتبة الكترونية</title>
    <!-- استدعاء Font Awesome لاستخدام الأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/test.css" rel="stylesheet">
</head>

<body>
    <!-- الهيدر -->
    <header>
        <div class="top-nav">
            <!-- شعار الموقع كرابط للصفحة الرئيسية -->
            <a href="index.php" class="logo">
                <img src="<?= BASE_URL ?>assets/images/logo3.png" alt="شعار الموقع" width="120" height="80">
            </a>

            <!-- زر القائمة المنسدلة للجوال -->
            <button class="menu-toggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>

            <!-- أيقونة المستخدم -->
            <div class="user-menu">
                <?php if(isset($_SESSION['user_id'])): ?>
                <!-- حالة مسجل الدخول -->
                <button class="user-toggle">
                    <i class="fas fa-user"></i>
                    <span style="font-weight: 600; color:rgb(0, 0, 0);">
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                    </span>
                    <span style="font-weight: 600; color:rgb(0, 0, 0);">
                        <i>|</i>
                        <?= ($_SESSION['user_type'] == 'admin') ? 'مدير النظام' : 'مستخدم' ?>
                    </span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="logout.php">تسجيل الخروج</a></li>
                </ul>
                <?php else: ?>
                <!-- حالة غير مسجل الدخول -->
                <a href="login.php" class="user-toggle">
                    <i class="fas fa-user"></i>
                </a>
                <?php endif; ?>
            </div>

        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="#">الرئيسية</a></li>
                <li><a href="#">التصنيفات</a></li>
                <li><a href="#">المفضلة</a></li>
                <li><a href="#">تواصل معنا</a></li>
            </ul>
        </nav>
    </header>
    <script>
    // تشغيل قائمة المستخدم المنسدلة
    document.querySelector('.user-toggle').addEventListener('click', function() {
        const dropdownMenu = document.querySelector('.dropdown-menu');
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    // إغلاق قائمة المستخدم عند النقر خارجها
    document.addEventListener('click', function(e) {
        const dropdownMenu = document.querySelector('.dropdown-menu');
        const userToggle = document.querySelector('.user-toggle');

        if (!userToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
    </script>