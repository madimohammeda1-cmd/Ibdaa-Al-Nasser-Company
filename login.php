<?php
/**
 * نظام المبيعات - صفحة تسجيل الدخول
 */

session_start();
require_once 'config.php';

$error = '';
$success = '';

// إذا كان المستخدم مسجلاً بالفعل
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . 'dashboard.php');
    exit();
}

// معالجة طلب تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // التحقق من البيانات المدخلة
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        $conn = Database::connect();
        
        // البحث عن المستخدم
        $stmt = $conn->prepare("
            SELECT id, username, password, full_name, role, status 
            FROM users 
            WHERE username = ? AND status = 'active'
        ");
        
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // التحقق من كلمة المرور
            if (password_verify($password, $user['password'])) {
                // تسجيل الدخول الناجح
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // تسجيل العملية
                log_audit($user['id'], 'login', 'auth', null, null, ['timestamp' => date('Y-m-d H:i:s')]);
                
                // إعادة التوجيه إلى لوحة التحكم
                header('Location: ' . SITE_URL . 'dashboard.php');
                exit();
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'اسم المستخدم غير موجود أو تم تعطيل الحساب';
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - تسجيل الدخول</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            direction: rtl;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 5px;
            padding: 12px;
            margin-top: 20px;
            font-size: 12px;
            color: #004085;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><?php echo SITE_NAME; ?></h1>
            <p>نظام إدارة المبيعات المتكامل</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['expired'])): ?>
            <div class="alert alert-error">
                انتهت مهلة الجلسة. يرجى تسجيل الدخول مرة أخرى.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">تسجيل الدخول</button>
        </form>
        
        <div class="info-box">
            <strong>بيانات المسؤول الافتراضية:</strong><br>
            اسم المستخدم: <code>admin</code><br>
            كلمة المرور: <code>admin</code><br>
            <small style="display: block; margin-top: 8px; color: #d00;">يرجى تغيير بيانات المسؤول فور الدخول للأمان</small>
        </div>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
