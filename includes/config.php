<?php
// إنشاء CSRF Token في بداية الجلسة
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// تعريف الثوابت بشرط عدم وجودها مسبقًا
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/var/www/html/autolibrary/');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/autolibrary/');
}
$host = "localhost";
$user = "rabi";
$password = "Asd@123@123";
$dbname = "library_db";
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>