<?php
/**
 * نظام المبيعات المتكامل
 * Integrated Sales System
 * Config File
 */

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sales_system');

// إعدادات الموقع
define('SITE_NAME', 'نظام المبيعات المتكامل');
define('SITE_URL', 'http://localhost/complete-sales/');
define('CURRENCY', 'IQD');
define('TAX_RATE', 15);

// إعدادات الأمان
session_name('sales_system');
session_start();

// الاتصال بقاعدة البيانات
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('❌ خطأ في الاتصال: ' . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// دوال مساعدة
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . 'login.html');
        exit;
    }
}

function format_currency($amount) {
    return number_format($amount, 2, '.', ',') . ' ' . CURRENCY;
}

function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

function clean_input($data) {
    return htmlspecialchars(trim($data));
}

function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}
?>
