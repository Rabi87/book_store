<?php
session_start();
// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
?>




<div class="dashboard-container">
    <!-- القسم اليميني -->
    <aside class="control-panel">
        <h3>أدوات التحكم</h3>
        <ul class="control-buttons">
            <li class="control-button">
                <a href="manage_books.php" class="control-link">📚 إدارة الكتب</a>
            </li>
            <li class="control-button">
                <a href="dashboard.php" class="control-link">🔄 عمليات الإعارة</a>
            </li>
            <li class="control-button">
                <a href="manage_sales.php" class="control-link">💰 إدارة المبيعات</a>
            </li>
            <li class="control-button">
                <a href="manage_users.php" class="control-link">👥 إدارة المستخدمين</a>
            </li>
        </ul>
    </aside>

    <!-- القسم اليساري -->
    <main class="content-panel" id="contentPanel">
        <div class="welcome-message">
            <h2>مرحبًا، <?php echo $_SESSION['user_name']; ?></h2>
            <p>اختر أحد الخيارات من لوحة التحكم لبدء الإدارة</p>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const controlLinks = document.querySelectorAll('.control-link');
    const contentPanel = document.getElementById('contentPanel');
    
    // وظيفة تحميل المحتوى
    const loadContent = (url) => {
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('خطأ في الشبكة');
                return response.text();
            })
            .then(data => {
                contentPanel.innerHTML = data;
                adjustPanelHeights();
            })
            .catch(error => {
                contentPanel.innerHTML = `<div class="alert error">${error.message}</div>`;
            });
    };

    // ضبط ارتفاع الأقسام
    const adjustPanelHeights = () => {
        const containerHeight = document.querySelector('.dashboard-container').offsetHeight;
        contentPanel.style.height = `${containerHeight}px`;
    };

    // معالجة النقر على الروابط
    controlLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // إزالة النشاط من جميع العناصر
            controlLinks.forEach(l => l.classList.remove('active'));
            
            // إضافة النشاط للعنصر الحالي
            this.classList.add('active');
            
            // تحميل المحتوى
            loadContent(this.href);
        });
    });

    // ضبط الأبعاد عند تغيير حجم النافذة
    window.addEventListener('resize', adjustPanelHeights);
    
   
});

// عزل الأحداث عن الروابط الأخرى
document.querySelectorAll('a:not(.control-link)').forEach(link => {
    link.addEventListener('click', (e) => {
        window.location.href = link.href;
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>