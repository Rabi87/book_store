<?php

require __DIR__ . '/../includes/config.php';
// التحقق من تسجيل الدخول

// ✅ التصحيح (تحقق من user_id أولاً)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['user_type'] != 'user') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// جلب تصنيفات المستخدم المختارة
$user_categories = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("
        SELECT category_id 
        FROM user_categories 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_categories[] = $row['category_id'];
    }
}

// جلب جميع التصنيفات المتاحة
$categories = $conn->query("SELECT * FROM categories");
?>

<!-- واجهة اختيار التصنيفات -->
<form method="POST" action="save_categories.php">
    <h4 class="mt-4">اختر تخصصاتك المفضلة</h4>
    <div class="row">
        <?php while ($cat = $categories->fetch_assoc()): ?>
        <div class="col-md-4 mb-3">
            <div class="form-check">
                <input 
                    type="checkbox" 
                    name="categories[]" 
                    value="<?= $cat['category_id'] ?>" 
                    <?= in_array($cat['category_id'], $user_categories) ? 'checked' : '' ?>
                >
                <label class="form-check-label">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </label>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <button type="submit" class="btn btn-primary">حفظ التفضيلات</button>
</form>
