<?php
// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<header class="header-outer">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المكتبة الذكية</title>
    <!-- الخطوط العربية -->
    <link href="<?= BASE_URL ?>assets/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</header>

<body class="d-flex flex-column min-vh-100">
    <div class="container">
        <nav class="navbar navbar-expand-lg" style="
                    background: linear-gradient(to right, #f8f9fa, #e9ecef);
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="container-fluid">
                <!-- الشعار على اليسار -->
                <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
                    <img src="<?= BASE_URL ?>assets/images/logo3.png" class="logo-hover" alt="شعار المكتبة"
                        style="height: 80px; width: 120px;">
                </a>

                <!-- زر القائمة المنسدلة للجوال -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- العناصر على اليمين -->
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <div class="d-flex align-items-center gap-3">
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- معلومات المستخدم مع Dropdown -->
                        <div class="user-dropdown position-relative">
                            <!-- العنصر الرئيسي الذي يتم النقر عليه -->
                            <div class="d-flex align-items-center gap-2 cursor-pointer" onclick="toggleDropdown()">
                                <i class="fas fa-user-circle fa-2x" style="color: #2c3e50;"></i>
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 600; color: #2c3e50;">
                                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                                    </span>
                                    <span style="font-size: 0.9em; color: #7f8c8d;">
                                        <i class="fas fa-shield-alt"></i>
                                        <?= ($_SESSION['user_type'] == 'admin') ? 'مدير النظام' : 'مستخدم' ?>
                                    </span>
                                </div>
                                <i class="fas fa-caret-down ms-1"></i>
                            </div>

                            <!-- قائمة الخروج المخفية -->
                            <div id="logoutDropdown" class="dropdown-menu-custom">
                                <a href="<?= BASE_URL ?>logout.php"
                                    class="logout-link d-flex align-items-center gap-2 text-decoration-none p-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="logout-text">تسجيل الخروج</span>
                                </a>
                            </div>
                        </div>

                        <?php else: ?>
                        <!-- زر الدخول -->
                        <a href="<?= BASE_URL ?>login.php"
                            class="btn btn-outline-success btn-sm d-flex align-items-center gap-2"
                            style="color: #2c3e50;">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="d-none d-md-inline">تسجيل الدخول</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <script>
                    function toggleDropdown() {
                        const dropdown = document.getElementById('logoutDropdown');
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    }

                    // إغلاق القائمة عند النقر خارجها
                    window.onclick = function(event) {
                        if (!event.target.closest('.user-dropdown')) {
                            document.getElementById('logoutDropdown').style.display = 'none';
                        }
                    }
                    </script>
                </div>
        </nav>
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container">


                </a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item"> <a class="nav-link" href="<?= BASE_URL ?>index.php">المكتبة</a> </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"> <a class="nav-link" href="#">المنتدى</a> </li>
                        <li class="nav-item"> <a class="nav-link" href="#">التعاملات</a> </li>
                        <li class="nav-item"> <a class="nav-link" href="<?= BASE_URL ?>
                        <?= ($_SESSION['user_type'] == 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php' ?>">
                                لوحة التحكم </a> </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
          
        </nav>
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

    <main class="flex-grow-1 container my-5">