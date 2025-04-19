<?php
session_start();
require __DIR__ . '/includes/config.php';


// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ━━━━━━━━━━ معالجة إرسال التقييم ━━━━━━━━━━
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    try {
        $request_id = (int)$_POST['request_id'];
        $rating = (int)$_POST['rating'];
        $comment = htmlspecialchars($_POST['comment']);

        // جلب book_id من جدول الاستعارات
        $stmt = $conn->prepare("SELECT book_id FROM borrow_requests WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $book_id = $result['book_id'];

        // ━━━━━━━━━━ التحقق من التقييم المكرر ━━━━━━━━━━
        // 1. عبر الجلسة (Session)
        if (isset($_SESSION['rated_books']) && in_array($book_id, $_SESSION['rated_books'])) {
            $_SESSION['error'] = "لقد قمت بتقييم هذا الكتاب مسبقاً في هذه الجلسة!";
            header("Location: read_book.php?request_id=" . $request_id);
            exit();
        }

        // 2. عبر قاعدة البيانات (اختياري)
        $stmt_check = $conn->prepare("SELECT id FROM book_ratings WHERE user_id = ? AND book_id = ?");
        $stmt_check->bind_param("ii", $_SESSION['user_id'], $book_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $_SESSION['error'] = "لقد قمت بتقييم هذا الكتاب مسبقاً!";
            header("Location: read_book.php?request_id=" . $request_id);
            exit();
        }

        // ━━━━━━━━━━ إدخال التقييم الجديد ━━━━━━━━━━
        $stmt_insert = $conn->prepare("
            INSERT INTO book_ratings 
            (user_id, book_id, rating, comment, request_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("iiisi", 
            $_SESSION['user_id'], 
            $book_id, 
            $rating, 
            $comment, 
            $request_id
        );
        $stmt_insert->execute();

        // ━━━━━━━━━━ تحديث متوسط التقييم في جدول الكتب ━━━━━━━━━━
        $stmt_avg = $conn->prepare("
            UPDATE books 
            SET evaluation = (
                SELECT ROUND(AVG(rating), 2) 
                FROM book_ratings 
                WHERE book_id = ?
            ) 
            WHERE id = ?
        ");
        $stmt_avg->bind_param("ii", $book_id, $book_id);
        $stmt_avg->execute();

        // إضافة book_id إلى الجلسة لمنع التقييم المكرر
        $_SESSION['rated_books'][] = $book_id;
        $_SESSION['success'] = "تم إرسال التقييم بنجاح!";

        header("Location: read_book.php?request_id=" . $request_id);
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "حدث خطأ: " . $e->getMessage();
        header("Location: read_book.php?request_id=" . $request_id);
        exit();
    }
}

// ━━━━━━━━━━ جلب بيانات الكتاب وعرضه ━━━━━━━━━━
$request_id = (int)$_GET['request_id'];
$user_id = $_SESSION['user_id'];

try {
    // جلب بيانات الكتاب
    $stmt_book = $conn->prepare("
        SELECT b.*, br.status 
        FROM borrow_requests br
        JOIN books b ON br.book_id = b.id
        WHERE br.id = ? AND br.user_id = ?
    ");
    $stmt_book->bind_param("ii", $request_id, $user_id);
    $stmt_book->execute();
    $book = $stmt_book->get_result()->fetch_assoc();

    if (!$book) {
        die("الطلب غير موجود أو ليس لديك صلاحية الوصول!");
    }

    // التحقق من وجود الملف
    $file_path = $book['file_path'];
    if (!file_exists($file_path)) {
        die("الملف غير موجود على الخادم!");
    }

} catch (Exception $e) {
    die("حدث خطأ: " . $e->getMessage());
}

// ━━━━━━━━━━ عرض الصفحة ━━━━━━━━━━
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .book-container {
            margin: 20px auto;
            max-width: 800px;
        }
        .rating-form {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container book-container">
        <!-- عرض الملف -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($book['title']) ?></h3>
                <embed 
                    src="<?= htmlspecialchars($file_path) ?>" 
                    type="application/pdf" 
                    width="100%" 
                    height="600px"
                >
            </div>
        </div>

        <!-- نموذج التقييم -->
        <div class="rating-form">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="request_id" value="<?= $request_id ?>">
                
                <div class="mb-3">
                    <label class="form-label">التقييم:</label>
                    <select name="rating" class="form-select" required>
                        <option value="5">★★★★★</option>
                        <option value="4">★★★★</option>
                        <option value="3">★★★</option>
                        <option value="2">★★</option>
                        <option value="1">★</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">تعليقك:</label>
                    <textarea name="comment" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" name="submit_rating" class="btn btn-primary w-100">
                    إرسال التقييم
                </button>
            </form>
        </div>
    </div>
</body>
</html>