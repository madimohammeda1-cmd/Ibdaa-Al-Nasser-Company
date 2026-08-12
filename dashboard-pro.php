<?php
/**
 * لوحة التحكم الاحترافية
 * Professional Dashboard with Charts and Advanced Analytics
 */

session_start();
require_once 'config.php';

check_session();

$conn = Database::connect();
$user = get_current_user();
$permissions = get_user_permissions($_SESSION['user_id']);

if (!in_array('view_dashboard', $permissions)) {
    die('ليس لديك صلاحية للوصول إلى لوحة التحكم');
}

// الحصول على البيانات الإحصائية
$today = date('Y-m-d');
$this_month = date('Y-m');
$last_month = date('Y-m', strtotime('-1 month'));

// فواتير اليوم
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(total) as total 
    FROM invoices 
    WHERE DATE(invoice_date) = ? AND status != 'draft'
");
$stmt->bind_param('s', $today);
$stmt->execute();
$today_invoices = $stmt->get_result()->fetch_assoc();
$stmt->close();

// فواتير هذا الشهر
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(total) as total 
    FROM invoices 
    WHERE DATE_FORMAT(invoice_date, '%Y-%m') = ? AND status != 'draft'
");
$stmt->bind_param('s', $this_month);
$stmt->execute();
$month_invoices = $stmt->get_result()->fetch_assoc();
$stmt->close();

// المدفوعات اليوم
$stmt = $conn->prepare("
    SELECT COUNT(*) as count, SUM(amount) as total 
    FROM payment_records 
    WHERE DATE(payment_date) = ?
");
$stmt->bind_param('s', $today);
$stmt->execute();
$today_payments = $stmt->get_result()->fetch_assoc();
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

// أكثر الزبائن نشاطاً
$stmt = $conn->prepare("
    SELECT c.id, c.name, COUNT(i.id) as invoice_count, SUM(i.total) as total_amount
    FROM customers c
    LEFT JOIN invoices i ON c.id = i.customer_id
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY total_amount DESC
    LIMIT 5
");
$stmt->execute();
$top_customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// بيانات الرسم البياني (آخر 7 أيام)
$stats_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(CASE WHEN status != 'draft' THEN 1 END) as invoices,
            SUM(CASE WHEN status != 'draft' THEN total ELSE 0 END) as amount,
            SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid
        FROM invoices 
        WHERE DATE(invoice_date) = ?
    ");
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $stats_data[] = [
        'date' => date('d/m', strtotime($date)),
        'invoices' => $result['invoices'] ?? 0,
        'amount' => $result['amount'] ?? 0,
        'paid' => $result['paid'] ?? 0
    ];
}

$stats_json = json_encode($stats_data);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar-logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: bold;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .menu {
            list-style: none;
        }

        .menu-item {
            margin-bottom: 0.5rem;
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

        .menu-item i {
            font-size: 1.2rem;
            width: 1.5rem;
        }

        .user-profile {
            position: absolute;
            bottom: 2rem;
            left: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }

        .user-profile p {
            font-size: 0.9rem;
            margin-top: 0.5rem;
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

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
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
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), transparent);
            border-radius: 50%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.danger {
            border-left-color: #e74c3c;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #999;
            font-weight: 500;
        }

        .stat-icon {
            font-size: 1.8rem;
            color: #667eea;
            opacity: 0.3;
        }

        .stat-card.warning .stat-icon {
            color: #f39c12;
        }

        .stat-card.success .stat-icon {
            color: #27ae60;
        }

        .stat-card.danger .stat-icon {
            color: #e74c3c;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .stat-change {
            font-size: 0.85rem;
            color: #27ae60;
        }

        .stat-change.negative {
            color: #e74c3c;
        }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.2rem;
        }

        canvas {
            max-height: 300px;
        }

        /* Tables */
        .table-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .table-card h3 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.2rem;
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
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 200px;
                padding: 1rem 0.5rem;
            }

            .main-content {
                margin-left: 200px;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -200px;
                width: 200px;
                transition: left 0.3s;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .header-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        .logout-btn {
            background: #e74c3c;
            width: 100%;
            text-align: center;
            margin-top: 1rem;
        }

        .logout-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fas fa-chart-line"></i>
                <div style="font-size: 1rem; margin-top: 0.5rem;">نظام المبيعات</div>
            </div>

            <ul class="menu">
                <li class="menu-item">
                    <a href="dashboard-pro.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>لوحة التحكم</span>
                    </a>
                </li>

                <?php if (in_array('view_invoices', $permissions)): ?>
                <li class="menu-item">
                    <a href="invoices/list.php">
                        <i class="fas fa-file-invoice"></i>
                        <span>الفواتير</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('view_customers', $permissions)): ?>
                <li class="menu-item">
                    <a href="customers/list.php">
                        <i class="fas fa-users"></i>
                        <span>الزبائن</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('view_products', $permissions)): ?>
                <li class="menu-item">
                    <a href="products/list.php">
                        <i class="fas fa-box"></i>
                        <span>المنتجات</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('view_inventory', $permissions)): ?>
                <li class="menu-item">
                    <a href="inventory/index.php">
                        <i class="fas fa-warehouse"></i>
                        <span>المخزون</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('view_reports', $permissions)): ?>
                <li class="menu-item">
                    <a href="reports/index.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>التقارير</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('manage_settings', $permissions)): ?>
                <li class="menu-item">
                    <a href="settings/index.php">
                        <i class="fas fa-cog"></i>
                        <span>الإعدادات</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="user-profile">
                <div style="font-weight: 600;"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <p><?php echo htmlspecialchars($user['role']); ?></p>
                <a href="logout.php" class="btn logout-btn" style="font-size: 0.85rem;">
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
                <div class="header-actions">
                    <?php if (in_array('create_invoices', $permissions)): ?>
                    <a href="invoices/create.php" class="btn">
                        <i class="fas fa-plus"></i> فاتورة جديدة
                    </a>
                    <?php endif; ?>

                    <?php if (in_array('create_customers', $permissions)): ?>
                    <a href="customers/create.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> زبون جديد
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-title">فواتير اليوم</div>
                        <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $today_invoices['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 
                        <?php echo format_currency($today_invoices['total'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <div class="stat-title">المدفوعات اليوم</div>
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $today_payments['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo format_currency($today_payments['total'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">فواتير الشهر</div>
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $month_invoices['count'] ?? 0; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo format_currency($month_invoices['total'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <div class="stat-title">فواتير مستحقة</div>
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $due_invoices['count'] ?? 0; ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo format_currency($due_invoices['amount'] ?? 0); ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">إجمالي الزبائن</div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $customers_count; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> نشط
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <div class="stat-title">إجمالي المنتجات</div>
                        <div class="stat-icon"><i class="fas fa-box"></i></div>
                    </div>
                    <div class="stat-value"><?php echo $products_count; ?></div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> متاح
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> المبيعات (آخر 7 أيام)</h3>
                    <canvas id="salesChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> عدد الفواتير (آخر 7 أيام)</h3>
                    <canvas id="invoicesChart"></canvas>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="table-card">
                <h3><i class="fas fa-star"></i> أكثر الزبائن نشاطاً</h3>
                <table>
                    <thead>
                        <tr>
                            <th>اسم الزبون</th>
                            <th>عدد الفواتير</th>
                            <th>إجمالي المشتريات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_customers as $customer): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo $customer['invoice_count']; ?></span>
                            </td>
                            <td><?php echo format_currency($customer['total_amount'] ?? 0); ?></td>
                            <td>
                                <span class="badge badge-success">نشط</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // البيانات الإحصائية
        const statsData = <?php echo $stats_json; ?>;

        // رسم بياني للمبيعات
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: statsData.map(s => s.date),
                datasets: [
                    {
                        label: 'إجمالي المبيعات',
                        data: statsData.map(s => s.amount),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'المدفوع',
                        data: statsData.map(s => s.paid),
                        borderColor: '#27ae60',
                        backgroundColor: 'rgba(39, 174, 96, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#27ae60'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: { font: { size: 12 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // رسم بياني لعدد الفواتير
        const invoicesCtx = document.getElementById('invoicesChart').getContext('2d');
        new Chart(invoicesCtx, {
            type: 'bar',
            data: {
                labels: statsData.map(s => s.date),
                datasets: [{
                    label: 'عدد الفواتير',
                    data: statsData.map(s => s.invoices),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(118, 75, 162, 0.8)',
                        'rgba(102, 126, 234, 0.8)'
                    ],
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
