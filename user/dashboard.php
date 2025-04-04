<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/user_auth.php';

// جلب البيانات
$user_id = $_SESSION['user_id'];

// الكتب المستعارة
$stmt = $conn->prepare("
    SELECT b.title, b.author, br.request_date, br.due_date, 
    DATEDIFF(br.due_date, CURDATE()) AS remaining_days
    FROM borrow_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'approved'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$borrowed_books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// الطلبات المعلقة
$stmt = $conn->prepare("
    SELECT b.title, b.author, br.request_date 
    FROM borrow_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'pending'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <title>لوحة التحكم - المستخدم</title>
    <style>
        .sidebar { background: #f8f9fa; min-height: 100vh; }
        .sidebar .btn { text-align: right; width: 100%; margin: 5px 0; }
        .content-section { display: none; }
        .content-section.active { display: block; }
        .overdue { background-color: #ffe6e6; }
        .due-soon { background-color: #fff3cd; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- الشريط الجانبي -->
            <div class="col-md-3 sidebar p-4">
                <div class="d-grid gap-2">
                    <button onclick="showSection('profile')" class="btn btn-outline-primary active">
                        <i class="fas fa-user"></i> الملف الشخصي
                    </button>
                    
                    <button onclick="showSection('borrowed')" class="btn btn-outline-success">
                        <i class="fas fa-book"></i> الكتب المستعارة
                    </button>
                    
                    <button onclick="showSection('pending')" class="btn btn-outline-warning">
                        <i class="fas fa-clock"></i> الطلبات المعلقة
                    </button>
                </div>
            </div>

            <!-- المحتوى الرئيسي -->
            <div class="col-md-9 p-4">
                <!-- قسم الملف الشخصي -->
                <div id="profile" class="content-section active">
                    <h4 class="mb-4">👤 الملف الشخصي</h4>
                    <div class="card">
                    <div class="card-body">
                         <?php require __DIR__ . '/profile.php'; ?>
                    </div>
                    </div>
                </div>

                <!-- قسم الكتب المستعارة -->
                <div id="borrowed" class="content-section">
                    <h4 class="mb-4">📚 الكتب المستعارة</h4>
                    <?php if(count($borrowed_books) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>المؤلف</th>
                                    <th>تاريخ الاستعارة</th>
                                    <th>تاريخ الاستحقاق</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowed_books as $book): 
                                    $remaining = $book['remaining_days'];
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    if ($remaining < 0) {
                                        $status_class = 'overdue';
                                        $status_text = '<span class="text-danger">متأخر ' . abs($remaining) . ' يوم</span>';
                                    } elseif ($remaining <= 3) {
                                        $status_class = 'due-soon';
                                        $status_text = '<span class="text-warning">' . $remaining . ' أيام</span>';
                                    } else {
                                        $status_text = $remaining . ' يوم';
                                    }
                                ?>
                                <tr class="<?= $status_class ?>">
                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><?= date('Y/m/d', strtotime($book['request_date'])) ?></td>
                                    <td><?= date('Y/m/d', strtotime($book['due_date'])) ?></td>
                                    <td><?= $status_text ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">لا يوجد كتب مستعارة حالياً</div>
                    <?php endif; ?>
                </div>

                <!-- قسم الطلبات المعلقة -->
                <div id="pending" class="content-section">
                    <h4 class="mb-4">⏳ الطلبات المعلقة</h4>
                    <?php if(count($pending_requests) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>العنوان</th>
                                    <th>المؤلف</th>
                                    <th>تاريخ الطلب</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_requests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['title']) ?></td>
                                    <td><?= htmlspecialchars($request['author']) ?></td>
                                    <td><?= date('Y/m/d', strtotime($request['request_date'])) ?></td>
                                    <td><span class="badge bg-warning">قيد المراجعة</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">لا توجد طلبات معلقة</div>
                    <?php endif; ?>
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