<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'اسم المستخدم وكلمة المرور مطلوبة';
    } else {
        $conn = Database::connect();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (verify_password($password, $user['password'])) {
                // تحديث آخر دخول
                $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update->bind_param('i', $user['id']);
                $update->execute();
                $update->close();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                redirect(SITE_URL . 'dashboard.php');
            } else {
                $error = 'كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'اسم المستخدم غير موجود';
        }
        
        $stmt->close();
    }
}

function clean_input($data) {
    return htmlspecialchars(trim($data));
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            align-items: center;
            justify-content: center;
            direction: rtl;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo i {
            font-size: 3rem;
            color: #667eea;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: #333;
            margin-top: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .login-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        .login-info strong {
            color: #333;
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-chart-line"></i>
            <h1><?php echo SITE_NAME; ?></h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" placeholder="أدخل كلمة المرور" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>

        <div class="login-info">
            <strong>بيانات الاختبار:</strong>
            <div>اسم المستخدم: <code>admin</code></div>
            <div>كلمة المرور: <code>admin</code></div>
        </div>
    </div>
</body>
</html>
