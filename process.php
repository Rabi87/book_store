<?php
// يجب أن يكون هذا أول محتوى في الملف
session_start();
// تعريف الثوابت الأساسية
require __DIR__ . '/includes/config.php';
// توليد CSRF Token إذا لم يكن موجود
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// كشف الأخطاء للمبرمج .. بجب أن يتم حذفها او تبديل ال 1 الى0 عند النشر
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// التحقق من الصلاحيات للعمليات الإدارية
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin';
}
// معالجة تسجيل المستخدم الجديد
if (isset($_POST['register'])) {
    try {
        $name = htmlspecialchars($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        // التحقق من البريد الإلكتروني
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();       
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "البريد الإلكتروني مسجل مسبقًا!";
            header("Location: register.php");
            exit();
        }
        // إضافة مستخدم جديد
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "تم التسجيل بنجاح!";
            header("Location: login.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        $_SESSION['error'] = "حدث خطأ أثناء التسجيل";
        header("Location: register.php");
        exit();
    }
}
// تسجيل الدخول
if(isset($_POST['login'])){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];   
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();   
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            // تخزين جميع البيانات في الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email']; // <-- إضافة البريد
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['created_at'] = $user['created_at']; // 
            // التوجيه الصحيح
            header("Location: " . BASE_URL . ($user['user_type'] == 'admin' ? 'admin/frame.php' : 'user/dashboard.php'));
            exit();
        }
    }    
    $_SESSION['error'] = "بيانات الدخول غير صحيحة!";
    header("Location: " . BASE_URL . "loging.php");
    exit();
}
// معالجة إضافة الكتب (للمسؤولين فقط)
if (isset($_POST['add_book']) && isAdmin()) {
    try {
        $title = htmlspecialchars($_POST['title']);
        $author = htmlspecialchars($_POST['author']);
        $type = in_array($_POST['type'], ['physical', 'e-book']) ? $_POST['type'] : 'physical';
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];   
        $category_id = (int)$_POST['category_id'];     
         // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // معالجة تحميل الصورة
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        $upload_dir = 'assets/images/books/';
        
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('يجب اختيار صورة غلاف للكتاب');
        }

        // إنشاء اسم فريد للصورة
        $original_name = $_FILES['cover_image']['name'];
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . date('YmdHis') . '.' . $extension;
        $target_path = $upload_dir . $new_filename;

        // إنشاء المجلد إذا لم يكن موجودًا
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // نقل الملف إلى المجلد المحدد
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
            throw new Exception('فشل في حفظ الصورة!');
        }

        $stmt = $conn->prepare("
        INSERT INTO books 
        (title, author, type, quantity, price, cover_image, category_id)  
        VALUES (?, ?, ?, ?, ?, ?,?)
    ");
        if (!$stmt) {
            die("خطأ في تحضير الاستعلام: " . $conn->error);
        }
        var_dump($title, $author, $type, $quantity, $price, $new_filename);
        //انتبه هنا sssids 
        //s: سلسلة نصية (title, author, type, cover_image, category_id).
        //i: عدد صحيح (quantity).
        //d: عدد عشري (price).
        $stmt->bind_param("sssidsi", $title, $author, $type, $quantity, $price, $new_filename,  $category_id);     
        if ($stmt->execute()) {
            $_SESSION['success'] = "تمت إضافة الكتاب بنجاح!";
        } else {
            $_SESSION['error'] = "فشل في إضافة الكتاب!";

        }        
        header("Location: " . BASE_URL . "admin/frame.php");
        exit();        
    } catch (Exception $e) {
        error_log("Add Book Error: " . $e->getMessage());
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
        header("Location: " . BASE_URL . "admin/manage_books.php");
        exit();
    }
}

// معالجة تحديث الكتاب
if (isset($_POST['update_book']) && isAdmin()) {
    try {
        $book_id = (int)$_POST['book_id'];
        $title = htmlspecialchars($_POST['title']);
        $author = htmlspecialchars($_POST['author']);
        $type = in_array($_POST['type'], ['physical', 'e-book']) ? $_POST['type'] : 'physical';
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];

        $stmt = $conn->prepare("
            UPDATE books SET 
            title = ?, 
            author = ?, 
            type = ?, 
            quantity = ?, 
            price = ?, 
            category_id = ? 
            WHERE id = ?
        ");
        
        $stmt->bind_param("sssidii", $title, $author, $type, $quantity, $price, $category_id, $book_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "تم تحديث الكتاب بنجاح!";
        } else {
            $_SESSION['error'] = "فشل في التحديث!";
        }
        
        header("Location: " . BASE_URL . "admin/frame.php");
        exit();
        
    } catch (Exception $e) {
        error_log("Update Error: " . $e->getMessage());
        $_SESSION['error'] = "حدث خطأ أثناء التحديث";
        header("Location: " . BASE_URL . "admin/manage_books.php");
        exit();
    }}

// معالجة طلب الاستعارة
if(isset($_POST['borrow_book'])) {
    try 
    {
        // التحقق من CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('طلب غير مصرح به');
        }

        // التحقق من بيانات المستخدم
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('يجب تسجيل الدخول أولاً');
        }

        // التحقق من صحة ID الكتاب
        $book_id = (int)$_POST['book_id'];
        if ($book_id <= 0) {
            throw new Exception('معرّف الكتاب غير صالح');
        }
        // بدء transaction
        $conn->begin_transaction();
        // التحقق من توفر الكتاب
        $stmt = $conn->prepare("SELECT quantity FROM books WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('الكتاب غير موجود');
        }

        $book = $result->fetch_assoc();
        if ($book['quantity'] <= 0) {
            throw new Exception('الكتاب غير متاح للاستعارة');
        }

        // إضافة طلب الاستعارة
        $stmt = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
        
        if (!$stmt->execute()) {
            throw new Exception('فشل في إرسال الطلب');
        }

        // تحديث الكمية باستخدام prepared statement
        $stmt = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        // تأكيد العملية
        $conn->commit();

        $_SESSION['success'] = "تم إرسال طلب الاستعارة بنجاح!";
        
    }
    catch (Exception $e)
    {
            error_log("Borrow Error: " . $e->getMessage());
            $_SESSION['error'] = "فشل في عملية الاستعارة: " . $e->getMessage();
            header("Location: index.php");
            exit();
    }
   // إذا وصلنا هنا بدون معالجة صحيحة
header("Location: " . BASE_URL . "index.php");
exit();
}
die("طلب غير معروف");
require __DIR__ . '/includes/footer.php';
?>