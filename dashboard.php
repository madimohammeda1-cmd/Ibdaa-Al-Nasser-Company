<?php
require_once 'config.php';
check_session();

$conn = Database::connect();
$user = get_current_user();

// الحصول على الإحصائيات
$today = date('Y-m-d');

// فواتير اليوم
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(total) as total FROM invoices WHERE DATE(invoice_date) = ? AND status != 'draft'");
$stmt->bind_param('s', $today);
$stmt->execute();
$today_invoices = $stmt->get_result()->fetch_assoc();
$stmt->close();

// المدفوعات اليوم
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payment_records WHERE DATE(payment_date) = ?");
$stmt->bind_param('s', $today);
$stmt->execute();
$today_payments = $stmt->get_result()->fetch_assoc();
$stmt->close();

// الفواتير المستحقة
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(total - paid_amount) as amount FROM invoices WHERE payment_status IN ('unpaid', 'partial') AND status != 'draft'");
$stmt->execute();
$due_invoices = $stmt->get_result()->fetch_assoc();
$stmt->close();

// عدد الزبائن والمنتجات
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE status = 'active'");
$stmt->execute();
$customers = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'active'");
$stmt->execute();
$products = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// أحدث الفواتير
$stmt = $conn->prepare("
    SELECT i.id, i.invoice_number, c.name, i.total, i.payment_status, i.invoice_date 
    FROM invoices i 
    JOIN customers c ON i.customer_id = c.id
    WHERE i.status != 'draft'
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            direction: rtl;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar-logo i {
            font-size: 2rem;
        }

        .menu {
            list-style: none;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .user-info {
            position: absolute;
            bottom: 2rem;
            left: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            color: #333;
        }

        .btn-logout {
            padding: 0.7rem 1.5rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.danger {
            border-left-color: #e74c3c;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .stat-change {
            font-size: 0.85rem;
            color: #27ae60;
            margin-top: 0.5rem;
        }

        /* Chart */
        .chart-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .chart-card h3 {
            margin-bottom: 1rem;
            color: #333;
        }

        /* Table */
        .table-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: right;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #eee;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-paid {
            background: #d4edda;
            color: #155724;
        }

        .badge-unpaid {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-partial {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem 0.5rem;
            }

            .main-content {
                margin-left: 70px;
            }

            .sidebar-logo i {
                font-size: 1.5rem;
            }

            .menu-item a span {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-chart-line"></i>
                <div style="font-size: 0.8rem; margin-top: 0.5rem;">نظام المبيعات</div>
            </div>

            <ul class="menu">
                <li class="menu-item">
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>لوحة التحكم</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="invoices/list.php">
                        <i class="fas fa-file-invoice"></i>
                        <span>الفواتير</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="customers/list.php">
                        <i class="fas fa-users"></i>
                        <span>الزبائن</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="products/list.php">
                        <i class="fas fa-box"></i>
                        <span>المنتجات</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="inventory/index.php">
                        <i class="fas fa-warehouse"></i>
                        <span>المخزون</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="reports/index.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>التقارير</span>
                    </a>
                </li>
            </ul>

            <div class="user-info">
                <div style="font-weight: 600; margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </div>
                <a href="logout.php" class="btn-logout" style="display: block; text-align: center;">
                    <i class="fas fa-sign-out-alt"></i> خروج
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>لوحة التحكم</h1>
                    <p style="color: #999; margin-top: 0.5rem;">أهلاً بك في نظام المبيعات المتكامل</p>
                </div>
                <div>
                    <a href="invoices/create.php" class="btn-logout" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: inline-block;">
                        <i class="fas fa-plus"></i> فاتورة جديدة
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-title">فواتير اليوم</div>
                    <div class="stat-value"><?php echo $today_invoices['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> <?php echo format_currency($today_invoices['total'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-title">المدفوعات اليوم</div>
                    <div class="stat-value"><?php echo $today_payments['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> <?php echo format_currency($today_payments['total'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-title">فواتير مستحقة</div>
                    <div class="stat-value"><?php echo $due_invoices['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> <?php echo format_currency($due_invoices['amount'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-title">الزبائن النشطين</div>
                    <div class="stat-value"><?php echo $customers; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> نشط
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">المنتجات</div>
                    <div class="stat-value"><?php echo $products; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> متاح
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="table-card">
                <h3><i class="fas fa-file-invoice"></i> آخر الفواتير</h3>
                <table>
                    <thead>
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>الزبون</th>
                            <th>المبلغ</th>
                            <th>حالة الدفع</th>
                            <th>التاريخ</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_invoices as $invoice): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($invoice['name']); ?></td>
                            <td><?php echo format_currency($invoice['total']); ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-' . $invoice['payment_status'];
                                $status_text = '';
                                if ($invoice['payment_status'] == 'paid') $status_text = 'مدفوعة';
                                elseif ($invoice['payment_status'] == 'unpaid') $status_text = 'غير مدفوعة';
                                else $status_text = 'دفع جزئي';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                            <td><?php echo format_date($invoice['invoice_date']); ?></td>
                            <td>
                                <a href="invoices/view.php?id=<?php echo $invoice['id']; ?>" style="color: #667eea; text-decoration: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
