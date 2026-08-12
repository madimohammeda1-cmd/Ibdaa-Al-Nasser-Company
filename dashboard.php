<?php
/**
 * نظام المبيعات - لوحة التحكم
 */

session_start();
require_once 'config.php';

check_session();

$conn = Database::connect();
$user = get_current_user();
$permissions = get_user_permissions($_SESSION['user_id']);

// إذا لم يكن لديه صلاحية عرض لوحة التحكم
if (!in_array('view_dashboard', $permissions)) {
    die('ليس لديك صلاحية للوصول إلى لوحة التحكم');
}

// الحصول على إحصائيات اليوم
$today = date('Y-m-d');

// عدد الفواتير اليوم
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(total) as total 
    FROM invoices 
    WHERE DATE(invoice_date) = ?
");
$stmt->bind_param('s', $today);
$stmt->execute();
$invoices_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// إجمالي الدفعات اليوم
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(amount) as total 
    FROM payment_records 
    WHERE DATE(payment_date) = ?
");
$stmt->bind_param('s', $today);
$stmt->execute();
$payments_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// عدد الزبائن
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE status = 'active'");
$stmt->execute();
$customers_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// عدد المنتجات
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$stmt->execute();
$products_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// الفواتير المستحقة
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(total - paid_amount) as amount 
    FROM invoices 
    WHERE payment_status IN ('unpaid', 'partial') AND status != 'draft'
");
$stmt->execute();
$due_invoices = $stmt->get_result()->fetch_assoc();
$stmt->close();

// آخر الفواتير
$stmt = $conn->prepare("
    SELECT i.id, i.invoice_number, i.total, i.status, i.payment_status, 
           c.name as customer_name, i.invoice_date
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    ORDER BY i.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_invoices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            direction: rtl;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 18px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .header h1 {
            font-size: 28px;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info span {
            color: #666;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-right: 4px solid #667eea;
        }
        
        .stat-card.warning {
            border-right-color: #f39c12;
        }
        
        .stat-card.success {
            border-right-color: #27ae60;
        }
        
        .stat-card.danger {
            border-right-color: #e74c3c;
        }
        
        .stat-card .icon {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-card.warning .icon {
            color: #f39c12;
        }
        
        .stat-card.success .icon {
            color: #27ae60;
        }
        
        .stat-card.danger .icon {
            color: #e74c3c;
        }
        
        .stat-card .label {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card .subtext {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        table th {
            background-color: #f9f9f9;
            padding: 12px;
            text-align: right;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        table tr:hover {
            background-color: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #667eea;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-small:hover {
            background: #764ba2;
        }
        
        .logout-btn {
            background: #e74c3c;
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 12px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <h2>القائمة الرئيسية</h2>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-chart-line"></i> لوحة التحكم
                    </a>
                </li>
                
                <?php if (in_array('view_invoices', $permissions)): ?>
                <li>
                    <a href="invoices/list.php">
                        <i class="fas fa-file-invoice"></i> الفواتير
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('view_customers', $permissions)): ?>
                <li>
                    <a href="customers/list.php">
                        <i class="fas fa-users"></i> الزبائن
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('view_products', $permissions)): ?>
                <li>
                    <a href="products/list.php">
                        <i class="fas fa-box"></i> المنتجات
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('view_inventory', $permissions)): ?>
                <li>
                    <a href="inventory/index.php">
                        <i class="fas fa-warehouse"></i> المخزون
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('view_accounts', $permissions)): ?>
                <li>
                    <a href="accounts/index.php">
                        <i class="fas fa-book"></i> الحسابات
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('view_reports', $permissions)): ?>
                <li>
                    <a href="reports/index.php">
                        <i class="fas fa-chart-bar"></i> التقارير
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('manage_settings', $permissions)): ?>
                <li>
                    <a href="settings/index.php">
                        <i class="fas fa-cog"></i> الإعدادات
                    </a>
                </li>
                <?php endif; ?>
                
                <li style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px;">
                    <a href="logout.php" style="color: #ff6b6b;">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h1>لوحة التحكم</h1>
                    <p style="color: #999; font-size: 14px; margin-top: 5px;">
                        <?php echo date('l, d F Y', strtotime($today)); ?>
                    </p>
                </div>
                <div class="user-info">
                    <div>
                        <span style="display: block; font-weight: 600;">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </span>
                        <span style="display: block; font-size: 12px; margin-top: 5px;">
                            <?php 
                            $roles = ['owner' => 'مالك', 'admin' => 'مدير', 'user' => 'مستخدم'];
                            echo $roles[$user['role']] ?? '';
                            ?>
                        </span>
                    </div>
                    <div>
                        <i class="fas fa-user-circle" style="font-size: 32px;"></i>
                    </div>
                </div>
            </div>
            
            <!-- الإحصائيات -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="label">فواتير اليوم</div>
                    <div class="value"><?php echo $invoices_stats['count'] ?? 0; ?></div>
                    <div class="subtext">
                        <?php echo format_currency($invoices_stats['total'] ?? 0); ?>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="label">المدفوعات اليوم</div>
                    <div class="value"><?php echo $payments_stats['count'] ?? 0; ?></div>
                    <div class="subtext">
                        <?php echo format_currency($payments_stats['total'] ?? 0); ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="label">الزبائن النشطين</div>
                    <div class="value"><?php echo $customers_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <div class="label">المنتجات</div>
                    <div class="value"><?php echo $products_count; ?></div>
                </div>
                
                <div class="stat-card danger">
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="label">الفواتير المستحقة</div>
                    <div class="value"><?php echo $due_invoices['count'] ?? 0; ?></div>
                    <div class="subtext">
                        <?php echo format_currency($due_invoices['amount'] ?? 0); ?>
                    </div>
                </div>
            </div>
            
            <!-- آخر الفواتير -->
            <div class="section">
                <h2>آخر الفواتير</h2>
                
                <?php if (!empty($recent_invoices)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>الزبون</th>
                                <th>التاريخ</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>حالة الدفع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_invoices as $invoice): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                                    <td><?php echo format_date($invoice['invoice_date']); ?></td>
                                    <td><?php echo format_currency($invoice['total']); ?></td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'draft' => 'مسودة',
                                            'issued' => 'مصدرة',
                                            'paid' => 'مدفوعة',
                                            'returned' => 'مرتجعة'
                                        ];
                                        $status_class = $invoice['status'] === 'paid' ? 'success' : ($invoice['status'] === 'draft' ? 'warning' : 'info');
                                        ?>
                                        <span class="badge badge-<?php echo $status_class; ?>">
                                            <?php echo $status_badges[$invoice['status']] ?? $invoice['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $payment_badges = [
                                            'paid' => 'مدفوعة',
                                            'unpaid' => 'غير مدفوعة',
                                            'partial' => 'دفع جزئي'
                                        ];
                                        $payment_class = $invoice['payment_status'] === 'paid' ? 'success' : ($invoice['payment_status'] === 'partial' ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge badge-<?php echo $payment_class; ?>">
                                            <?php echo $payment_badges[$invoice['payment_status']] ?? $invoice['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="invoices/view.php?id=<?php echo $invoice['id']; ?>" class="btn-small">
                                                عرض
                                            </a>
                                            <?php if (in_array('edit_invoices', $permissions) && $invoice['status'] === 'draft'): ?>
                                            <a href="invoices/edit.php?id=<?php echo $invoice['id']; ?>" class="btn-small">
                                                تعديل
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 20px;">
                        لا توجد فواتير حتى الآن
                    </p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // تحديث الإحصائيات كل 30 ثانية
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
