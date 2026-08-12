<?php
/**
 * نظام المبيعات المتكامل - ملف الإعدادات
 * Configuration File
 */

// تحديد المنطقة الزمنية
date_default_timezone_set('Asia/Baghdad');

// بيانات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sales_system');

// إعدادات الموقع
define('SITE_NAME', 'نظام المبيعات المتكامل');
define('SITE_URL', 'http://localhost/sales-system/');
define('COMPANY_NAME', 'الشركة');

// إعدادات البريد الإلكتروني
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-password');
define('MAIL_FROM', 'noreply@sales.local');

// إعدادات الجلسة
define('SESSION_TIMEOUT', 3600); // ساعة واحدة
define('SESSION_NAME', 'sales_system_session');

// إعدادات النظام
define('ITEMS_PER_PAGE', 20);
define('CURRENCY', 'IQD');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// إعدادات الضريبة
define('DEFAULT_TAX_RATE', 15); // 15%
define('TAX_NUMBER_FORMAT', '###-#');

// إعدادات الفواتير
define('INVOICE_PREFIX', 'INV');
define('RETURN_INVOICE_PREFIX', 'RET');
define('PERMIT_PREFIX', 'PRM');
define('COUNT_PREFIX', 'CNT');
define('INVOICE_FOOTER', 'شكراً لتعاملكم معنا');

// مسارات النظام
define('UPLOADS_DIR', __DIR__ . '/uploads/');
define('LOGS_DIR', __DIR__ . '/logs/');
define('TEMPLATES_DIR', __DIR__ . '/templates/');

// إنشاء المجلدات إذا لم تكن موجودة
if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);
if (!is_dir(LOGS_DIR)) mkdir(LOGS_DIR, 0755, true);
if (!is_dir(TEMPLATES_DIR)) mkdir(TEMPLATES_DIR, 0755, true);

// دالة الاتصال بقاعدة البيانات
class Database {
    private static $conn = null;
    
    public static function connect() {
        if (self::$conn === null) {
            try {
                self::$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                
                if (self::$conn->connect_error) {
                    die('خطأ في الاتصال: ' . self::$conn->connect_error);
                }
                
                // ضبط الترميز على UTF-8
                self::$conn->set_charset('utf8mb4');
            } catch (Exception $e) {
                die('خطأ: ' . $e->getMessage());
            }
        }
        
        return self::$conn;
    }
    
    public static function disconnect() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}

// دالة تسجيل الأخطاء
function log_error($message, $error_type = 'ERROR') {
    $log_file = LOGS_DIR . 'error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] [$error_type] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// دالة تسجيل العمليات (Audit)
function log_audit($user_id, $action, $module, $record_id = null, $old_values = null, $new_values = null) {
    $conn = Database::connect();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $old_values_json = json_encode($old_values);
    $new_values_json = json_encode($new_values);
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, record_id, old_values, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issiiss', $user_id, $action, $module, $record_id, $old_values_json, $new_values_json, $ip_address);
    $stmt->execute();
    $stmt->close();
}

// دالة التحقق من الصلاحيات
function check_permission($user_id, $permission_name) {
    $conn = Database::connect();
    
    $stmt = $conn->prepare("
        SELECT rp.id FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN users u ON u.role = rp.role
        WHERE u.id = ? AND p.name = ?
    ");
    
    $stmt->bind_param('is', $user_id, $permission_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->num_rows > 0;
}

// دالة فحص الجلسة
function check_session() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
    
    // التحقق من انتهاء الجلسة
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: ' . SITE_URL . 'login.php?expired=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

// دالة الحصول على بيانات المستخدم الحالي
function get_current_user() {
    $conn = Database::connect();
    
    $stmt = $conn->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

// دالة توليد رقم فاتورة فريد
function generate_invoice_number() {
    $conn = Database::connect();
    $prefix = INVOICE_PREFIX;
    $date_code = date('Ymd');
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoices WHERE invoice_number LIKE ?");
    $like_pattern = $prefix . $date_code . '%';
    $stmt->bind_param('s', $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = ($row['count'] + 1);
    $stmt->close();
    
    return $prefix . $date_code . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// دالة التحقق من صحة البريد الإلكتروني
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// دالة تشفير كلمة المرور
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// دالة التحقق من كلمة المرور
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// دالة تنسيق العملة
function format_currency($amount) {
    return number_format($amount, 2, '.', ',') . ' ' . CURRENCY;
}

// دالة تنسيق التاريخ
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

// دالة التحويل من التاريخ العربي إلى الميلادي (إذا كان مطلوباً)
function ar_to_en_digits($str) {
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($arabic, $english, $str);
}

// دالة الحصول على صلاحيات المستخدم
function get_user_permissions($user_id) {
    $conn = Database::connect();
    $permissions = [];
    
    $stmt = $conn->prepare("
        SELECT p.name FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN users u ON u.role = rp.role
        WHERE u.id = ?
    ");
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['name'];
    }
    
    $stmt->close();
    return $permissions;
}

// معالج الأخطاء العام
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_error("Error ($errno) in $errfile:$errline - $errstr", 'PHP_ERROR');
});

// معالج الاستثناءات
set_exception_handler(function($exception) {
    log_error($exception->getMessage(), 'EXCEPTION');
    die('حدث خطأ في النظام. يرجى المحاولة لاحقاً.');
});
?>
