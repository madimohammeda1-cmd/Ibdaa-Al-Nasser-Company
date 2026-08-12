<?php
/**
 * صفحة إنشاء الفواتير الاحترافية
 * Professional Invoice Creation Page
 */

session_start();
require_once '../config.php';

check_session();

$conn = Database::connect();
$user = get_current_user();

if (!check_permission($_SESSION['user_id'], 'create_invoices')) {
    die('ليس لديك صلاحية لإنشاء فواتير');
}

// الحصول على قائمة الزبائن
$stmt = $conn->prepare("SELECT id, name, tax_id, credit_limit FROM customers WHERE status = 'active' ORDER BY name");
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// الحصول على قائمة المنتجات
$stmt = $conn->prepare("SELECT id, name, sku, selling_price, quantity FROM products WHERE status = 'active' ORDER BY name");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$customers_json = json_encode($customers);
$products_json = json_encode($products);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء فاتورة - <?php echo SITE_NAME; ?></title>
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
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
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

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        /* Form Sections */
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #667eea;
            font-size: 1.4rem;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Items Table */
        .items-section {
            margin-top: 2rem;
        }

        .add-item-btn {
            margin-bottom: 1.5rem;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .items-table thead {
            background: #f8f9fa;
        }

        .items-table th {
            padding: 1rem;
            text-align: right;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #eee;
        }

        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .items-table tbody tr:hover {
            background: #f9f9f9;
        }

        .item-input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .item-input:focus {
            outline: none;
            border-color: #667eea;
        }

        /* Summary */
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .summary-row.total {
            font-size: 1.5rem;
            font-weight: 700;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .summary-label {
            font-weight: 600;
        }

        .summary-value {
            text-align: left;
        }

        /* Messages */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
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

            .form-grid {
                grid-template-columns: 1fr;
            }

            .items-table {
                font-size: 0.85rem;
            }

            .items-table th,
            .items-table td {
                padding: 0.5rem;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }

        .spinner.show {
            display: block;
        }

        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
        }

        .spinner-overlay.show {
            display: block;
        }

        /* Autocomplete */
        .autocomplete-list {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 10;
            display: none;
        }

        .autocomplete-list.show {
            display: block;
        }

        .autocomplete-item {
            padding: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .autocomplete-item:hover {
            background: #f0f0f0;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><i class="fas fa-plus-circle"></i> إنشاء فاتورة جديدة</h1>
                <p style="color: #999; margin-top: 0.5rem;">إنشاء وتسجيل فاتورة جديدة للزبون</p>
            </div>
            <div class="header-actions">
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>

        <!-- Messages -->
        <div id="messages"></div>

        <!-- Form -->
        <form id="invoiceForm" method="POST" action="../api/invoices.php?action=create">
            <!-- Customer & Date Info -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> معلومات الفاتورة
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer">الزبون *</label>
                        <select id="customer" name="customer_id" required onchange="updateCustomerInfo()">
                            <option value="">-- اختر زبون --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" data-credit="<?php echo $customer['credit_limit']; ?>">
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>رقم الضريبة</label>
                        <input type="text" id="taxId" readonly>
                    </div>

                    <div class="form-group">
                        <label for="invoiceDate">تاريخ الفاتورة *</label>
                        <input type="date" id="invoiceDate" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dueDate">تاريخ الاستحقاق</label>
                        <input type="date" id="dueDate" name="due_date">
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="form-section items-section">
                <div class="section-title">
                    <i class="fas fa-boxes"></i> المنتجات
                </div>

                <button type="button" class="btn btn-primary btn-small add-item-btn" onclick="addItemRow()">
                    <i class="fas fa-plus"></i> إضافة منتج
                </button>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">الترتيب</th>
                            <th style="width: 30%;">المنتج</th>
                            <th style="width: 12%;">الكمية</th>
                            <th style="width: 15%;">السعر الواحد</th>
                            <th style="width: 15%;">الخصم</th>
                            <th style="width: 10%;">الضريبة</th>
                            <th style="width: 15%;">الإجمالي</th>
                            <th style="width: 5%;">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
            </div>

            <!-- Totals & Options -->
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">حالة الفاتورة</label>
                        <select id="status" name="status">
                            <option value="draft">مسودة</option>
                            <option value="issued" selected>مصدرة</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">ملاحظات</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="أضف ملاحظات إضافية..."></textarea>
                    </div>
                </div>

                <!-- Summary Box -->
                <div class="summary-box">
                    <div class="summary-row">
                        <span class="summary-label">الإجمالي الجزئي:</span>
                        <span class="summary-value" id="subtotal">0.00 IQD</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">الخصم:</span>
                        <span class="summary-value" id="discount">0.00 IQD</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">الضريبة:</span>
                        <span class="summary-value" id="tax">0.00 IQD</span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">الإجمالي النهائي:</span>
                        <span class="summary-value" id="total">0.00 IQD</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> حفظ الفاتورة
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </button>
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        // البيانات
        const customers = <?php echo $customers_json; ?>;
        const products = <?php echo $products_json; ?>;

        let itemCount = 0;

        // إضافة صف جديد
        function addItemRow() {
            itemCount++;
            const row = document.createElement('tr');
            row.id = `item-${itemCount}`;
            row.innerHTML = `
                <td>${itemCount}</td>
                <td>
                    <select class="item-input" onchange="updateRowTotal(this)">
                        <option value="">-- اختر منتج --</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" class="item-input" value="1" min="1" onchange="updateRowTotal(this)">
                </td>
                <td>
                    <input type="number" class="item-input" step="0.01" placeholder="0.00" readonly>
                </td>
                <td>
                    <input type="number" class="item-input" value="0" step="0.01" onchange="updateRowTotal(this)">
                </td>
                <td>
                    <input type="number" class="item-input" value="0" step="0.01" onchange="updateRowTotal(this)">
                </td>
                <td>
                    <input type="number" class="item-input" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-small" onclick="this.closest('tr').remove(); updateTotals()">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            document.getElementById('itemsBody').appendChild(row);
        }

        // تحديث إجمالي الصف
        function updateRowTotal(element) {
            const row = element.closest('tr');
            const select = row.querySelector('select');
            const quantity = parseFloat(row.querySelectorAll('input')[0].value) || 0;
            const discount = parseFloat(row.querySelectorAll('input')[1].value) || 0;
            const tax = parseFloat(row.querySelectorAll('input')[2].value) || 0;
            
            const price = parseFloat(select.selectedOptions[0]?.dataset.price) || 0;
            const priceInput = row.querySelectorAll('input')[0];
            
            row.querySelectorAll('input')[0].value = price.toFixed(2);
            
            const subtotal = (quantity * price) - discount;
            const total = subtotal + tax;
            
            row.querySelectorAll('input')[3].value = total.toFixed(2);
            
            updateTotals();
        }

        // تحديث الإجماليات
        function updateTotals() {
            let subtotal = 0, discount = 0, tax = 0;
            
            document.querySelectorAll('#itemsBody tr').forEach(row => {
                const quantity = parseFloat(row.querySelectorAll('input')[0].value) || 0;
                const price = parseFloat(row.querySelectorAll('input')[0].value) || 0;
                const disc = parseFloat(row.querySelectorAll('input')[1].value) || 0;
                const t = parseFloat(row.querySelectorAll('input')[2].value) || 0;
                
                subtotal += (quantity * price) - disc;
                discount += disc;
                tax += t;
            });
            
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' IQD';
            document.getElementById('discount').textContent = discount.toFixed(2) + ' IQD';
            document.getElementById('tax').textContent = tax.toFixed(2) + ' IQD';
            document.getElementById('total').textContent = total.toFixed(2) + ' IQD';
        }

        // تحديث معلومات الزبون
        function updateCustomerInfo() {
            const select = document.getElementById('customer');
            const option = select.selectedOptions[0];
            document.getElementById('taxId').value = option.dataset.taxId || '';
        }

        // معالج الإرسال
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const items = [];
            document.querySelectorAll('#itemsBody tr').forEach((row, index) => {
                const select = row.querySelector('select');
                const inputs = row.querySelectorAll('input');
                
                items.push({
                    product_id: select.value,
                    quantity: parseFloat(inputs[0].value),
                    unit_price: parseFloat(inputs[0].value),
                    discount: parseFloat(inputs[1].value),
                    tax: parseFloat(inputs[2].value)
                });
            });
            
            if (items.length === 0) {
                showMessage('يجب إضافة منتج واحد على الأقل', 'error');
                return;
            }
            
            const data = {
                customer_id: document.getElementById('customer').value,
                invoice_date: document.getElementById('invoiceDate').value,
                due_date: document.getElementById('dueDate').value,
                status: document.getElementById('status').value,
                notes: document.getElementById('notes').value,
                items: items
            };
            
            fetch('../api/invoices.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage('تم إنشاء الفاتورة بنجاح!', 'success');
                    setTimeout(() => {
                        window.location.href = `view.php?id=${data.invoice_id}`;
                    }, 1500);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(err => showMessage(err.message, 'error'));
        });

        // عرض الرسائل
        function showMessage(msg, type) {
            const div = document.createElement('div');
            div.className = `alert alert-${type}`;
            div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
            document.getElementById('messages').appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }

        // إضافة صف أول افتراضي
        addItemRow();
    </script>
</body>
</html>
