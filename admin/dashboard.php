
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/config.php';
// تحقق من الشروط وعدّل الرأس أولًا (( أمن....................................))
// قمت بوضع الشرط أعلى استدعاء ال header dashboared دون اذن
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
require __DIR__ . '/../includes/header.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  
    <title>لوحة التحكم - الإدارة</title>
    <style>
        .sidebar { background: #f8f9fa; min-height: 100vh; }
        .sidebar .btn { text-align: right; width: 100%; margin: 5px 0; }
        .content-section { display: none; }
        .content-section.active { display: block; }
        .dashboard-container { padding: 20px; }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <div class="col-md-3 sidebar p-4">
                <div class="d-grid gap-2">
                <button onclick="showSection('personal')" class="btn btn-outline-primary active">
                        <i class="fas fa-user"></i> الرئيسية
                    </button>

                    <button onclick="showSection('operations')" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt"></i>الملف الشخصي
                    </button>
                    
                    <button onclick="showSection('books')" class="btn btn-outline-success">
                        <i class="fas fa-book"></i> إدارة الكتب
                    </button>
                    
                    <button onclick="showSection('ops')" class="btn btn-outline-success">
                        <i class="fas fa-book"></i> إدارة العمليات
                    </button>
                    
                    <button onclick="showSection('sales')" class="btn btn-outline-warning">
                        <i class="fas fa-coins"></i> إدارة المبيعات
                    </button>
                    
                    <button onclick="showSection('users')" class="btn btn-outline-info">
                        <i class="fas fa-users"></i> إدارة المستخدمين
                    </button>

                    <button onclick="showSection('bk')" class="btn btn-outline-info">
                        <i class="fas fa-hdd"></i> النسخ الأحتياطي و الإسترجاع
                    </button>

                    <button onclick="showSection('logs')" class="btn btn-outline-info">
                        <i class="fas fa-hdd"></i> سجلات النشاطات
                    </button>

                    <button onclick="showSection('payment')" class="btn btn-outline-info">
                        <i class="fas fa-hdd"></i> سجلات الدفع
                    </button>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 p-4">
                <!-- قسم عمليات الإعارة -->
                

                    <div id="personal" class="content-section active">
                    <h4 class="mb-4">🔄  الرئيسية</h4>
                    <div class="card">
                    <div class="card-body">
                          <?php include 'personal.php'; ?>
                    </div>
                    </div>
                </div>
                
                <div id="operations" class="content-section">
                    <h4 class="mb-4">🔄 الملف الشخصي</h4>
                    <div class="card">
                    <div class="card-body">
                          <?php include 'profile.php'; ?>
                    </div>
                    </div>
                </div>

                <!-- قسم إدارة العمليات -->
                <div id="ops" class="content-section">
                    <h4 class="mb-4">📚 إدارة العمليات</h4>
                    <div class="card">
                    <div class="card-body">
                    <?php include 'manage_loan.php'; ?>
</div>
                </div></div>

                <!-- قسم إدارة الكتب -->
                <div id="books" class="content-section">
                    <h4 class="mb-4">📚 إدارة الكتب</h4>
                    <div class="card">
                    <div class="card-body">
                    <?php include 'manage_books.php'; ?>
                </div>
                </div></div>

                <!-- قسم إدارة المبيعات -->
                <div id="sales" class="content-section">
                    <h4 class="mb-4">💰 إدارة المبيعات</h4>
                    <?php include 'manage_sales.php'; ?>
                </div>

                <!-- قسم إدارة المستخدمين -->
                <div id="users" class="content-section">
                    <h4 class="mb-4">👥 إدارة المستخدمين</h4>
                    <div class="card">
                    <div class="card-body">
                    <?php include 'manage_users.php'; ?>
                </div>
                </div></div>

                <!-- قسم إدارة النسخ الاحتياطي -->
                <div id="bk" class="content-section">
                    <h4 class="mb-4"> النسخ الاحتياطي</h4>
                    <div class="card">
                    <div class="card-body">
                    <?php include 'backup_restore.php'; ?>
                </div>
                </div></div>

                 <!-- قسم إدارة   -->
                 <div id="logs" class="content-section">
                    <h4 class="mb-4">  سجلات النشاطات</h4>
                    <div class="card">
                    <div class="card-body">
                    <?php include 'manage_logs.php'; ?>
                </div>
                </div></div>

           
               


            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // إزالة النشاط من جميع الأزرار
            document.querySelectorAll('.sidebar .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // إخفاء جميع الأقسام
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // إظهار القسم المحدد وإضافة النشاط للزر
            document.getElementById(sectionId).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
    <?php require __DIR__ . '/../includes/footer.php'; ?>


