
<?php
// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/header.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) ){
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SESSION['user_type'] != 'admin') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}


// جلب جميع طلبات الاستعارة مع تفاصيل المستخدم والكتاب
$stmt = $conn->prepare("
    SELECT 
        br.id AS request_id,
        u.name AS user_name,
        b.title AS book_title,
        br.request_date,
        br.status 
    FROM 
        borrow_requests br
    JOIN 
        users u ON br.user_id = u.id
    JOIN 
        books b ON br.book_id = b.id
    ORDER BY 
        br.request_date DESC
");
$stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// دوال مساعدة
require __DIR__ . '/../includes/functions.php';
?>


<div class="container mt-5">
    <h2 class="mb-4">لوحة التحكم الإدارية</h2>
    
    <!-- إضافة كتاب جديد -->
    <form action="<?= BASE_URL ?>process.php" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="title" placeholder="عنوان الكتاب" class="form-control" required>
            </div>
            <div class="col-md-6">
                <input type="text" name="author" placeholder="المؤلف" class="form-control" required>
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select" required>
                    <option value="physical">كتاب فيزيائي</option>
                    <option value="e-book">كتاب إلكتروني</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="quantity" placeholder="الكمية" class="form-control" required>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" name="price" placeholder="السعر" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add_book" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>إضافة كتاب
                </button>
            </div>
        </div>
    </form>
</div>


    <title>لوحة التحكم - طلبات الاستعارة</title>
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">إدارة طلبات الاستعارة</h2>
        
        <?php include __DIR__ . '/../includes/alerts.php'; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>الكتاب</th>
                        <th>تاريخ الطلب</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $index => $request): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($request['user_name']) ?></td>
                        <td><?= htmlspecialchars($request['book_title']) ?></td>
                        <td><?= date('Y/m/d H:i', strtotime($request['request_date'])) ?></td>
                        <td>
                            <span class="badge bg-<?= getStatusColor($request['status']) ?>">
                                <?= getStatusText($request['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>admin/process_request.php" class="d-flex gap-2">
                                <input type="hidden" name="request_id" value="<?= $request['request_id'] ?>">
                                <select name="action" class="form-select form-select-sm" required>
                                    <option value="approve" <?= $request['status'] === 'approved' ? 'disabled' : '' ?>>موافقة</option>
                                    <option value="reject" <?= $request['status'] === 'rejected' ? 'disabled' : '' ?>>رفض</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>