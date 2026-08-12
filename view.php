<?php
require_once '../config.php';
check_session();

$conn = Database::connect();
$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($invoice_id <= 0) {
    redirect('../invoices/list.php');
}

// الحصول على بيانات الفاتورة
$stmt = $conn->prepare("
    SELECT i.*, c.name as customer_name, c.email, c.phone, c.tax_id, c.address
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    WHERE i.id = ?
");
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    redirect('../invoices/list.php');
}

// الحصول على بنود الفاتورة
$stmt = $conn->prepare("
    SELECT * FROM invoice_items WHERE invoice_id = ?
");
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// الحصول على سجلات الدفع
$stmt = $conn->prepare("
    SELECT * FROM payment_records WHERE invoice_id = ? ORDER BY payment_date DESC
");
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفاتورة <?php echo $invoice['invoice_number']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #eee;
        }

        .company-info h2 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .invoice-info {
            text-align: left;
        }

        .invoice-info p {
            margin-bottom: 0.5rem;
        }

        .customer-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .section-title {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: right;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
            gap: 3rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .total-row {
            display: flex;
            gap: 2rem;
        }

        .total-label {
            font-weight: 600;
            color: #666;
        }

        .total-value {
            min-width: 120px;
            text-align: left;
        }

        .total-final {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
        }

        .total-final .total-label,
        .total-final .total-value {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .payments {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #eee;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-print {
            background: #27ae60;
        }

        .btn-print:hover {
            background: #229954;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .header {
                display: none;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header no-print">
            <h1>الفاتورة <?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
            <div>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> طباعة
                </button>
                <a href="list.php" class="btn" style="background: #f0f0f0; color: #333; margin-right: 1rem;">
                    <i class="fas fa-arrow-right"></i> عودة
                </a>
            </div>
        </div>

        <div class="invoice">
            <div class="invoice-header">
                <div class="company-info">
                    <h2><?php echo SITE_NAME; ?></h2>
                    <p>نظام متكامل لإدارة المبيعات</p>
                </div>
                <div class="invoice-info">
                    <p><strong>رقم الفاتورة:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                    <p><strong>التاريخ:</strong> <?php echo format_date($invoice['invoice_date']); ?></p>
                    <p><strong>تاريخ الاستحقاق:</strong> <?php echo format_date($invoice['due_date']); ?></p>
                </div>
            </div>

            <div class="customer-section">
                <div>
                    <div class="section-title">بيانات الزبون:</div>
                    <p><strong><?php echo htmlspecialchars($invoice['customer_name']); ?></strong></p>
                    <p>الهاتف: <?php echo htmlspecialchars($invoice['phone']); ?></p>
                    <p>البريد: <?php echo htmlspecialchars($invoice['email'] ?? '-'); ?></p>
                    <?php if (!empty($invoice['tax_id'])): ?>
                    <p>رقم الضريبة: <?php echo htmlspecialchars($invoice['tax_id']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($invoice['address'])): ?>
                    <p>العنوان: <?php echo htmlspecialchars($invoice['address']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">المنتج</th>
                        <th style="width: 10%;">الكمية</th>
                        <th style="width: 15%;">السعر الواحد</th>
                        <th style="width: 15%;">الخصم</th>
                        <th style="width: 15%;">الضريبة</th>
                        <th style="width: 15%;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo format_currency($item['unit_price']); ?></td>
                        <td><?php echo format_currency($item['discount']); ?></td>
                        <td><?php echo format_currency($item['tax']); ?></td>
                        <td><?php echo format_currency($item['line_total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals">
                <div class="total-row">
                    <div class="total-label">الإجمالي الجزئي:</div>
                    <div class="total-value"><?php echo format_currency($invoice['subtotal']); ?></div>
                </div>
                <div class="total-row">
                    <div class="total-label">الخصم:</div>
                    <div class="total-value"><?php echo format_currency($invoice['discount_amount']); ?></div>
                </div>
                <div class="total-row">
                    <div class="total-label">الضريبة:</div>
                    <div class="total-value"><?php echo format_currency($invoice['tax_amount']); ?></div>
                </div>
            </div>

            <div class="totals">
                <div class="total-final">
                    <div class="total-row">
                        <div class="total-label">الإجمالي النهائي:</div>
                        <div class="total-value"><?php echo format_currency($invoice['total']); ?></div>
                    </div>
                </div>
            </div>

            <?php if (count($payments) > 0): ?>
            <div class="payments">
                <h4 style="margin-bottom: 1rem;">سجل الدفعات:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>الملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo format_date($payment['payment_date']); ?></td>
                            <td><?php echo format_currency($payment['amount']); ?></td>
                            <td><?php echo $payment['payment_method']; ?></td>
                            <td><?php echo htmlspecialchars($payment['notes'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if (!empty($invoice['notes'])): ?>
            <div class="payments">
                <h4 style="margin-bottom: 1rem;">ملاحظات:</h4>
                <p><?php echo htmlspecialchars($invoice['notes']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
