<?php

// إنشاء CSRF Token في بداية الجلسة
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__)); // المسار المادي للجذر
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/autolibrary/'); // الموقع الأساس
//echo BASE_URL;

$host = "localhost";
$user = "rabi";
$password = "Asd@123@123";
$dbname = "library_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>