<?php
// بدء الجلسة وإدارة المخرجات
ob_start(); // تخزين الإخراج في المخزن المؤقت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';

// ------ التحقق من صلاحيات المستخدم ------ //
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    
    exit();
}

// ------ تحديد القسم النشط ------ //
$active_section = isset($_GET['section']) ? htmlspecialchars($_GET['section']) : 'personal';

?>


<!-- عرض الرسائل التحذيرية -->
<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'انتبه.. !',
    text: '<?= $_SESSION['error']?>'
});
</script>
<?php unset($_SESSION['error']);?>
<?php endif;?>

<?php if (isset($_SESSION['success'])):?>
<script>
Swal.fire({
    icon: 'success',
    title: 'احسنت.. !',
    text: '<?= $_SESSION['success']?>'
});
</script>
<?php unset($_SESSION['success']);?>
<?php endif;?>
<div class="container-fluid">
    <div class="row">
        <!-- زر الشريط الجانبي للجوال -->
        <button class="btn btn-primary sidebar-toggler d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- الشريط الجانبي -->
        <div class="col-md-3 sidebar p-4">
            <div class="d-grid gap-2">
                <button onclick="showSection('personal')"
                    class="btn btn-outline-primary <?= ($active_section == 'personal') ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> الرئيسية
                </button>

                <button onclick="showSection('operations')"
                    class="btn btn-outline-info <?= ($active_section == 'operations') ? 'active' : '' ?>">
                    <i class="fas fa-sync-alt"></i> الملف الشخصي
                </button>             
            
                <button onclick="showSection('books')"
                    class="btn btn-outline-success <?= ($active_section == 'books') ? 'active' : '' ?>">
                    <i class="fas fa-book"></i> إدارة الكتب
                </button>

                <button onclick="showSection('ops')"
                    class="btn btn-outline-danger <?= ($active_section == 'ops') ? 'active' : '' ?>">
                    <i class="fas fa-book"></i> إدارة الطلبات
                </button>
            
                <button onclick="showSection('sales')"
                    class="btn btn-outline-warning <?= ($active_section == 'sales') ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i> إدارة المبيعات
                </button>

                <button onclick="showSection('users')"
                    class="btn btn-outline-info <?= ($active_section == 'users') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> إدارة المستخدمين
                </button>

                <button onclick="showSection('bk')"
                    class="btn btn-outline-info <?= ($active_section == 'bk') ? 'active' : '' ?>">
                    <i class="fas fa-hdd"></i> النسخ الاحتياطي
                </button>

                <button onclick="showSection('logs')"
                    class="btn btn-outline-info <?= ($active_section == 'logs') ? 'active' : '' ?>">
                    <i class="fas fa-hdd"></i> سجلات النشاطات
                </button>

                <button onclick="showSection('payment')"
                    class="btn btn-outline-info <?= ($active_section == 'payment') ? 'active' : '' ?>">
                    <i class="fas fa-hdd"></i> سجلات الدفع
                </button>

                <button onclick="showSection('complaints')"
                    class="btn btn-outline-info <?= ($active_section == 'complaints') ? 'active' : '' ?>">
                    <i class="fas fa-exclamation-circle"></i> إدارة الشكاوى
                </button>

                <button onclick="showSection('slider')"
                    class="btn btn-outline-info <?= ($active_section == 'slider') ? 'active' : '' ?>">
                    <i class="fas fa-images"></i> إدارة السلايدر
                </button>

                <button onclick="showSection('news_ticker')"
                    class="btn btn-outline-info <?= ($active_section == 'news_ticker') ? 'active' : '' ?>">
                    <i class="fas fa-newspaper"></i> الشريط الأخباري
                </button>

                <button onclick="showSection('categories')"
                    class="btn btn-outline-warning <?= ($active_section == 'categories') ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> إدارة التصنيفات
                </button>

                <button onclick="showSection('settings')"
                    class="btn btn-outline-primary <?= ($active_section == 'settings') ? 'active' : '' ?>">
                    <i class="fas fa-sync-alt"></i>  الإعدادات
                </button>

               

               
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="col-md-9 p-4">
            <!-- قسم الرئيسية -->
            <div id="personal" class="content-section <?= ($active_section == 'personal') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/personal.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم الملف الشخصي -->
            <div id="operations" class="content-section <?= ($active_section == 'operations') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/profile.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم إدارة الطلبات -->
            <div id="ops" class="content-section <?= ($active_section == 'ops') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_loan.php'; ?>
                    </div>
                </div>
            </div>

            <div id="categories" class="content-section <?= ($active_section == 'categories') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_categories.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم إدارة الكتب -->
            <div id="books" class="content-section <?= ($active_section == 'books') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_books.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم إدارة المبيعات -->
            <div id="sales" class="content-section <?= ($active_section == 'sales') ? 'active' : '' ?>">
                <?php require __DIR__ . '/manage_ops.php'; ?>
            </div>

            <!-- قسم إدارة المستخدمين -->
            <div id="users" class="content-section <?= ($active_section == 'users') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_users.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم النسخ الاحتياطي -->
            <div id="bk" class="content-section <?= ($active_section == 'bk') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/backup_restore.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم سجلات النشاطات -->
            <div id="logs" class="content-section <?= ($active_section == 'logs') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_logs.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم سجلات الدفع -->
            <div id="payment" class="content-section <?= ($active_section == 'payment') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/payment_logs.php'; ?>
                    </div>
                </div>
            </div>

            <!-- قسم إدارة الشكاوى -->
            <div id="complaints" class="content-section <?= ($active_section == 'complaints') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/manage_complaints.php'; ?>
                    </div>
                </div>
            </div>

            <!--قسم السلايدر -->
            <div id="slider" class="content-section <?= ($active_section == 'slider') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <h4>إدارة صور السلايدر</h4>

                        <!-- نموذج رفع صورة -->
                        <form action="process_slider.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input type="file" name="slider_image" class="form-control" required>
                            </div>
                            <button type="submit" name="upload_image" class="btn btn-primary">رفع الصورة</button>
                        </form>

                        <!-- قائمة الصور -->
                        <div class="mt-4">
                            <?php
                                    $images = $conn->query("SELECT * FROM slider_images");
                                    while ($image = $images->fetch_assoc()):
                                    ?>
                            <div class="card mb-3">
                                <img src="<?= BASE_URL . $image['image_path'] ?>" class="card-img-top"
                                    style="height: 200px;">
                                <div class="card-body">
                                    <form action="process_slider.php" method="POST">
                                        <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                        <button type="submit" name="delete_image" class="btn btn-danger">حذف</button>
                                    </form>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="news_ticker" class="content-section <?= ($active_section == 'news_ticker') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <h4>إدارة الأخبار</h4>

                        <!-- إضافة خبر جديد -->
                        <form action="process_news.php" method="POST">
                            <div class="mb-3">
                                <textarea name="content" class="form-control" placeholder="محتوى الخبر"
                                    required></textarea>
                            </div>
                            <button type="submit" name="add_news" class="btn btn-primary">إضافة</button>
                        </form>

                        <!-- قائمة الأخبار -->
                        <div class="mt-4">
                            <?php
                $news = $conn->query("SELECT * FROM news_ticker");
                while ($item = $news->fetch_assoc()):
                ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form action="process_news.php" method="POST">
                                        <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                        <textarea name="content"
                                            class="form-control mb-2"><?= $item['content'] ?></textarea>
                                        <button type="submit" name="update_news" class="btn btn-warning">تحديث</button>
                                        <button type="submit" name="delete_news" class="btn btn-danger">حذف</button>
                                    </form>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- قسم  الاعدادات -->
            <div id="settings" class="content-section <?= ($active_section == 'settings') ? 'active' : '' ?>">
                <div class="card">
                    <div class="card-body">
                        <?php require __DIR__ . '/settings.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// دالة إظهار القسم وتحديث الرابط
function showSection(sectionId) {
    // تحديث الرابط بإضافة المعلمة section
    const url = new URL(window.location.href);
    url.searchParams.set('section', sectionId);
    window.history.replaceState({}, '', url);

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

// دالة التحكم بالشريط الجانبي
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>

<?php 
ob_end_flush();
require __DIR__ . '/../includes/footer.php'; ?>