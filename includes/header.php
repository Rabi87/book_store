<?php
// بدء الجلسة مرة واحدة فقط
if (session_status() === PHP_SESSION_NONE) {
    session_start();}

require __DIR__ . '/../includes/config.php';
// بعد بدء الجلسة مباشرة
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>المكتبة الذكية</title>
    <!-- الخطوط العربية -->
    <link href="<?= BASE_URL ?>assets/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


</head>

<body class="d-flex flex-column min-vh-100">
    <header >
        <div class="container">
            <nav class="navbar navbar-expand-lg" style="
                    background: linear-gradient(to right, #f8f9fa, #e9ecef);
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div class="container-fluid">
                    <!-- الشعار على اليسار -->
                    <a class="navbar-brand" href="<?= BASE_URL ?>home.php">
                        <img src="<?= BASE_URL ?>assets/images/logo3.png" class="logo-hover" alt="شعار المكتبة"
                            style="height: 80px; width: 120px;">
                    </a>
                    <!-- زر القائمة المنسدلة للجوال و الخاصة بالمستخدم-->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-user-circle fa-lg"></i>
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

                                </div>

                                <!-- قائمة الخروج المخفية -->
                                <div id="logoutDropdown" class="dropdown-menu-custom1">
                                    <a href="<?= BASE_URL ?>logout.php"
                                        class="logout-link1 d-flex align-items-center gap-2 text-decoration-none">
                                        <i class="fas fa-sign-out-alt"></i>

                                    </a>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- زر الدخول -->
                            <div class="dropdown-menu-custom2">
                                <a href="<?= BASE_URL ?>login.php" class="login-icon text-decoration-none">
                                    <i class="fas fa-sign-in-alt"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <script>
                        function toggleDropdown() {
                            const dropdown = document.getElementById('logoutDropdown');
                            dropdown.style.display = dropdown.style.display === 'flex' ? 'none' :
                            'flex'; // تغيير إلى flex
                        }
                        window.onclick = function(event) {
                            if (!event.target.closest('.user-dropdown')) {
                                document.getElementById('logoutDropdown').style.display = 'none';
                            }
                        }
                        </script>
                    </div>
                </div>
            </nav>

            <nav class="navbar navbar-expand-lg navbar-dark sticky-top">

                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item"> <a class="nav-link" href="<?= BASE_URL ?>home.php">المكتبة</a> </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"> <a class="nav-link" href="<?= BASE_URL ?>index.php">اختبار</a> </li>
                        <li class="nav-item"> <a class="nav-link"
                                href="<?= BASE_URL ?>Forum/manage_groups.php">المنتدى</a> </li>
                        <li class="nav-item"> <a class="nav-link"
                                href="<?= BASE_URL . ($_SESSION['user_type'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php') ?>">
                                لوحة التحكم
                            </a> </li>
                        <?php endif; ?>
                    </ul>
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
        </div>
    </header>

    <main class="flex-grow-1 container my-2">