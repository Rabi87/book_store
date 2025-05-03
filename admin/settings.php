<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}



require __DIR__ . '/../includes/config.php';

// ------ معالجة تحديث الإعدادات ------ //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['success'] = "تم تحديث الإعدادات eeeeeeeeeeeeبنجاح!";
    $errors = [];
    
    foreach ($_POST['settings'] as $name => $value) {
        // التحقق من صحة القيمة
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        try {
            $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = ?");
            if (!$stmt) {
                throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
            }
            
            $stmt->bind_param("ds", $value, $name);
            if (!$stmt->execute()) {
                throw new Exception("فشل في تحديث {$name}: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // تحديد رسالة الجلسة بعد معالجة جميع الإعدادات
    if (empty($errors)) {
        $_SESSION['success'] = "تم تحديث الإعدادات بنجاح!";
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }

    // التوجيه إلى قسم الإعدادات فقط مرة واحدة
   
}

// جلب الإعدادات الحالية
$settings = [];
$result = $conn->query("SELECT name, value FROM settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['name']] = $row['value'];
    }
    $result->close();
} else {
    die("خطأ في جلب الإعدادات: " . $conn->error);
}
$conn->close();
ob_end_flush();
?>

<!-- عرض الرسائل -->
<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'خطأ!',
    text: '<?= addslashes($_SESSION['error']) ?>'
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'نجاح!',
    text: '<?= addslashes($_SESSION['success']) ?>'
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- بقية كود HTML -->


    <h1>الإعدادات</h1>
    <?php if (isset($success)) echo "<p style='color:green'>$success</p>"; ?>
    
    <form method="POST">
        <table border="1">
            <tr>
                <th>الإعداد</th>
                <th>القيمة (ريال)</th>
            </tr>
            <tr>
                <td>سعر الشراء</td>
                <td><input type="number" step="0.01" name="settings[purchase_price]" value="<?= $settings['purchase_price'] ?>"></td>
            </tr>
            <tr>
                <td>سعر الإعارة</td>
                <td><input type="number" step="0.01" name="settings[rental_price]" value="<?= $settings['rental_price'] ?>"></td>
            </tr>
            <tr>
                <td>غرامة التأخير/اليوم</td>
                <td><input type="number" step="0.01" name="settings[late_fee]" value="<?= $settings['late_fee'] ?>"></td>
            </tr>
        </table>
        <button type="submit">حفظ التغييرات</button>
    </form>
