<?php

// يجب أن تكون هذه أول سطور الملف
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // بدء الجلسة هنا قبل أي استخدام للـ $_SESSION
}

error_log("======== بدء معالجة الطلب ========");
error_log("بيانات POST: " . print_r($_POST, true));
error_log("بيانات GET: " . print_r($_GET, true));
error_log("جلسة المستخدم: " . print_r($_SESSION, true));

// تعريف الثوابت الأساسية
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/header.php';

// تمكين عرض الأخطاء للتطوير (يجب تعطيله في الإنتاج)
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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // التوجيه الصحيح
            header("Location: " . BASE_URL . ($user['user_type'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
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
        
        $stmt = $conn->prepare("
            INSERT INTO books 
            (title, author, type, quantity, price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssid", $title, $author, $type, $quantity, $price);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "تمت إضافة الكتاب بنجاح!";
        } else {
            $_SESSION['error'] = "فشل في إضافة الكتاب!";
        }
        
        header("Location: " . BASE_URL . "admin/manage_books.php");
        exit();
        
    } catch (Exception $e) {
        error_log("Add Book Error: " . $e->getMessage());
        $_SESSION['error'] = "حدث خطأ تقني!";
        header("Location: " . BASE_URL . "admin/manage_books.php");
        exit();
    }
}


// معالجة طلب الاستعارة
if(isset($_POST['borrow_book'])) {
    try {
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
        
    }catch (Exception $e) {
            error_log("Borrow Error: " . $e->getMessage());
            $_SESSION['error'] = "فشل في عملية الاستعارة: " . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    
    header("Location: " . BASE_URL . "index.php");
    exit();
}

require __DIR__ . '/includes/footer.php';
?>