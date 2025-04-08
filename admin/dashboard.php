
<?php

// ملف admin/dashboard.php
require __DIR__ . '/../includes/config.php';


// التحقق من وجود الجلسة و نوع المستخدم
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
<body>
    <div class="container py-5">
        
        
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

</body>
</html>