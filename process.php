<?php
session_start();
require __DIR__ . '/includes/db_logger.php';
require __DIR__ . '/includes/config.php';

//if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من وجود CSRF Token في الطلب والجلسة
 //   if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  //      die("Invalid CSRF Token");
  //  }
//}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// التحقق من صلاحيات الأدمن
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// ======== معالجة تسجيل المستخدم ========
if (isset($_POST['register'])) {

    try {
        // التحقق من البيانات الأساسية
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // التحقق من البريد الإلكتروني
        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check->num_rows > 0) {
            throw new Exception("البريد الإلكتروني مسجل مسبقًا");
        }

        // إضافة المستخدم
        $conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
        $user_id = $conn->insert_id;

        // إضافة التصنيفات المفضلة
        if (isset($_POST['categories'])) {
            foreach ($_POST['categories'] as $category_id) {
                $category_id = (int)$category_id;
                $conn->query("INSERT INTO user_categories (user_id, category_id) VALUES ($user_id, $category_id)");
            }
        }

        $_SESSION['success'] = "تم التسجيل بنجاح!";
        header("Location: login.php");

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: register.php");
    }
    exit();
}




// ======== معالجة تسجيل الدخول ========
if (isset($_POST['login'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $password = $_POST['password'];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("المستخدم غير موجود");
        }
        
        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password'])) {
            throw new Exception("كلمة المرور خاطئة");
        }
        
        // تعيين بيانات الجلسة
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        
        DatabaseLogger::log(
            'login_success',
            $user['name'],
            'تم تسجيل الدخول بنجاح'
        );
        
        header("Location: " . BASE_URL . ($user['user_type'] == 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
        
    } catch (Exception $e) {
        DatabaseLogger::log(
            'login_failed',
            $name,
            $e->getMessage()
        );
        $_SESSION['error'] = "بيانات الدخول غير صحيحة";
        header("Location: login.php");
    }
    exit();
}

// ======== إضافة كتب (للمسؤولين فقط) ========
if (isset($_POST['add_book']) && isAdmin()) {
    try {
        // التحقق من البيانات
       
        $title = htmlspecialchars($_POST['title']);
        $author = htmlspecialchars($_POST['author']);
        $type = in_array($_POST['type'], ['physical', 'e-book']) ? $_POST['type'] : 'physical';
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $evaluation = (float)$_POST['evaluation'];
        $description = htmlspecialchars($_POST['description']);

        
        // معالجة تحميل الصورة
        if (!isset($_FILES['cover_image']['error']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('يجب اختيار صورة غلاف');
        }
        
        $upload_dir = 'assets/images/books/';
        $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '_' . date('YmdHis') . '.' . $extension;
        $target_path = $upload_dir . $new_filename;
        
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_path)) {
            throw new Exception('فشل في حفظ الصورة');
        }

        // ━━━━━━━━━━ معالجة تحميل الملف (file_path) ━━━━━━━━━━
        if (!isset($_FILES['file_path']['error']) || $_FILES['file_path']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('يجب رفع ملف الكتاب');
        }
        
        $file_upload_dir = 'assets/files/'; // مجلد تخزين الملفات
        $file_extension = pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION);
        $file_new_name = uniqid() . '_' . date('YmdHis') . '.' . $file_extension;
        $file_target_path = $file_upload_dir . $file_new_name;
        
        if (!is_dir($file_upload_dir)) mkdir($file_upload_dir, 0755, true);
        if (!move_uploaded_file($_FILES['file_path']['tmp_name'], $file_target_path)) {
            throw new Exception('فشل في حفظ الملف');
        }

        
        // إدخال البيانات
        $stmt = $conn->prepare("
            INSERT INTO books 
            (title, author, type, quantity, price, cover_image, category_id,file_path, evaluation, description)   
            VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?)
        ");
        
        if (!$stmt) {
            throw new Exception("خطأ في إعداد الاستعلام: " . $conn->error);
        }
        
        $stmt->bind_param("sssidsisss", $title, $author, $type, $quantity, $price, $new_filename, $category_id, $file_target_path, $evaluation, $description);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "تمت إضافة الكتاب بنجاح!";
        } else {
            throw new Exception("فشل في إضافة الكتاب");
        }
        
        header("Location: " . BASE_URL . "admin/dashboard.php");
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . BASE_URL . "admin/manage_books.php");
    }
    exit();
}

// ======== معالجة تحديث الكتب (مع دعم الملفات) ========
if (isset($_POST['update_book']) && isAdmin()) {
    try {
        // التحقق من CSRF Token
        //if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
         //   throw new Exception('طلب غير مصرح به');
       // }

        // جلب البيانات الأساسية
        $book_id = (int)$_POST['book_id'];
        $title = htmlspecialchars($_POST['title']);
        $author = htmlspecialchars($_POST['author']);
        $type = in_array($_POST['type'], ['physical', 'e-book']) ? $_POST['type'] : 'physical';
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $evaluation = (float)$_POST['evaluation'];
        $description = htmlspecialchars($_POST['description']);

        // جلب البيانات الحالية
        $stmt = $conn->prepare("SELECT cover_image, file_path FROM books WHERE id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $current_data = $stmt->get_result()->fetch_assoc();

        // ━━━━━━━ معالجة صورة الغلاف ━━━━━━━
        $cover_image = $current_data['cover_image'];
        if (!empty($_FILES['cover_image']['name'])) {
            $upload_dir = 'assets/images/books/';
            $extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . date('YmdHis') . '.' . $extension;
            
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $new_filename)) {
                throw new Exception('فشل في حفظ الصورة');
            }
            $cover_image = $upload_dir . $new_filename;
        }

        // ━━━━━━━ معالجة ملف الكتاب ━━━━━━━
        $file_path = $current_data['file_path'];
        if (!empty($_FILES['file_path']['name'])) {
            $file_upload_dir = 'assets/files/';
            $file_extension = pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION);
            $file_new_name = uniqid() . '_' . date('YmdHis') . '.' . $file_extension;
            
            if (!is_dir($file_upload_dir)) mkdir($file_upload_dir, 0755, true);
            if (!move_uploaded_file($_FILES['file_path']['tmp_name'], $file_upload_dir . $file_new_name)) {
                throw new Exception('فشل في حفظ الملف');
            }
            $file_path = $file_upload_dir . $file_new_name;
        }

        // تحديث البيانات في قاعدة البيانات
        $stmt = $conn->prepare("
            UPDATE books SET 
            title = ?, 
            author = ?, 
            type = ?, 
            quantity = ?, 
            price = ?, 
            category_id = ?,
            cover_image = ?,
            file_path = ? ,
            description=?,
            evaluation=?
            WHERE id = ?
        ");
        
        $stmt->bind_param("sssidissssi", 
            $title, 
            $author, 
            $type, 
            $quantity, 
            $price, 
            $category_id,
            $cover_image,
            $file_path,
            $description,
            $evaluation,
            $book_id
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ تم تحديث الكتاب بنجاح!";
        } 
        else
         {
            throw new Exception("❌ فشل في التحديث: " . $stmt->error);
        }

        header("Location: " . BASE_URL . "admin/dashboard.php");

    } 
    catch (Exception $e) 
    {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . BASE_URL . "admin/edit_book.php?id=" . $book_id);
      
    }
    exit();
}



// ======== معالجة طلب الاستعارة/الشراء ========
if (isset($_POST['action'])) {
    try {
        // التحقق من CSRF Token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('طلب غير مصرح به');
        }
        
        // تحديد نوع العملية والمبلغ المطلوب
        $action = $_POST['action'];
        $required_amount = ($action === 'borrow') ? 5000 : 25000;
        $book_id = (int)$_POST['book_id'];
        
        // التحقق من الرصيد
        $stmt_wallet = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
        $stmt_wallet->bind_param("i", $_SESSION['user_id']);
        $stmt_wallet->execute();
        $wallet = $stmt_wallet->get_result()->fetch_assoc();
        
        if ($wallet['balance'] < $required_amount) {
            $_SESSION['required_amount'] = $required_amount;
            $_SESSION['book_id'] = $book_id;
            $_SESSION['action'] = $action;
            header("Location: add_funds.php");
            exit();
        }
        
        // خصم المبلغ
        //$stmt_deduct = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
        //$stmt_deduct->bind_param("di", $required_amount, $_SESSION['user_id']);
       // $stmt_deduct->execute();
        
        // إرسال الطلب إلى المدير
        $stmt_request = $conn->prepare("INSERT INTO borrow_requests (user_id, book_id, type, amount) VALUES (?, ?, ?, ?)");
        $stmt_request->bind_param("iisd", $_SESSION['user_id'], $book_id, $action, $required_amount);
        $stmt_request->execute();
        
        $_SESSION['success'] = "تم إرسال الطلب بنجاح!";
        header("Location: index.php");
        
    } catch (Exception $e) {
        $_SESSION['error'] = "خطأ: " . $e->getMessage();
        header("Location: index.php");
    }
    exit();
}

// إذا لم يتم التعرف على أي عملية
die("طلب غير معروف");