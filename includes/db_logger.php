<?php
// ملف: includes/db_logger.php

require_once __DIR__ . '/config.php';

class DatabaseLogger {
    private static $table = 'activity_logs'; // اسم الجدول
    
    /**
     * تسجيل حدث جديد في قاعدة البيانات
     * @param string $eventType نوع الحدث (مثال: login_success)
     * @param string $username اسم المستخدم
     * @param string $details تفاصيل الحدث
     * @param string|null $ip عنوان IP المستخدم (اختياري)
     */
    public static function log($eventType, $username, $details, $ip = null) {
        global $conn;
        
        // 1. إنشاء الجدول تلقائيًا إذا لم يكن موجودًا
        self::createTableIfNotExists();
        
        // 2. الحصول على عنوان IP
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        // 3. إعداد استعلام الإدخال مع معالجة الأخطاء
        $sql = "
            INSERT INTO " . self::$table . " 
            (event_type, user, details, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        // 4. تحضير الاستعلام والتحقق من الأخطاء
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("فشل في تحضير الاستعلام: " . $conn->error);
            return;
        }
        
        // 5. ربط المعلمات وتنفيذ الاستعلام
        $stmt->bind_param("ssss", $eventType, $username, $details, $ip);
        if (!$stmt->execute()) {
            error_log("فشل في تسجيل النشاط: " . $stmt->error);
        }
    }
    
    /**
     * قراءة السجلات من قاعدة البيانات
     * @param int $limit عدد السجلات
     * @return array
     */
    public static function readLogs($limit = 100) {
        global $conn;
        
        $query = "
            SELECT * FROM " . self::$table . " 
            ORDER BY id DESC 
            LIMIT " . (int)$limit;
        
        $result = $conn->query($query);
        return ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * إنشاء الجدول إذا غير موجود
     */
    private static function createTableIfNotExists() {
        global $conn;
        
        $query = "
            CREATE TABLE IF NOT EXISTS " . self::$table . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                user VARCHAR(100) NOT NULL,
                details TEXT NOT NULL,
                ip_address VARCHAR(45),
                created_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        if (!$conn->query($query)) {
            error_log("فشل في إنشاء الجدول: " . $conn->error);
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// مثال للاستخدام:
// DatabaseLogger::log('login_failed', 'user123', 'محاولة دخول فاشلة');
// $logs = DatabaseLogger::readLogs(50);
// ─────────────────────────────────────────────────────────────────────────────