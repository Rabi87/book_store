
<?php
session_start();
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
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
<style>
        .sidebar {
            background: linear-gradient(to left, #f8f9fa, #e9ecef);
            min-height: 100vh;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .btn {
            text-align: right;
            width: 100%;
            margin: 8px 0;
            padding: 12px;
            border: 1px solid #dee2e6;
            color: #2c3e50;
            transition: all 0.3s;
        }
        
        .sidebar .btn.active,
        .sidebar .btn:hover {
            background: #2c3e50;
            color: white;
            transform: translateX(-5px);
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        }
        
        .content-section {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        h4 {
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
    </style>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <div class="col-md-3 sidebar p-4">
                <div class="d-grid gap-2">
                    <button onclick="showSection('operations')" class="btn btn-outline-primary active">
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
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 p-4">
                <!-- قسم عمليات الإعارة -->
                <div id="operations" class="content-section active">
                    <h4 class="mb-4">🔄 الملف الشخصي</h4>
                    <div class="card">
                    <div class="card-body">
                          <?php include 'profile.php'; ?>
                    </div>
                    </div>

                    <div class="welcome-message">
                        <h2>مرحبًا، <?php echo $_SESSION['user_name']; ?></h2>
                        <p>اختر أحد الخيارات من لوحة التحكم لبدء الإدارة</p>
                    </div>
                </div>

                <!-- قسم إدارة الكتب -->
                <div id="books" class="content-section">
                    <h4 class="mb-4">📚 إدارة الكتب</h4>
                    <?php include 'dashboard.php'; ?>
                </div>

                <!-- قسم إدارة الكتب -->
                <div id="ops" class="content-section">
                    <h4 class="mb-4">📚 إدارة العمليات</h4>
                    <?php include 'manage_books.php'; ?>
                </div>

                <!-- قسم إدارة المبيعات -->
                <div id="sales" class="content-section">
                    <h4 class="mb-4">💰 إدارة المبيعات</h4>
                    <?php include 'manage_sales.php'; ?>
                </div>

                <!-- قسم إدارة المستخدمين -->
                <div id="users" class="content-section">
                    <h4 class="mb-4">👥 إدارة المستخدمين</h4>
                    <?php include 'manage_users.php'; ?>
                </div>
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

    <?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
