<?php
require_once '../config.php';
check_session();

$conn = Database::connect();

// الحصول على الزبائن والمنتجات
$stmt = $conn->prepare("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name");
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT id, name, selling_price FROM products WHERE status = 'active' ORDER BY name");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
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

        .section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .section h3 {
            margin-bottom: 1.5rem;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

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
        }

        .form-group input,
        .form-group select {
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
            border-bottom: 2px solid #eee;
        }

        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .items-table input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 5px;
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

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .summary-row.total {
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            padding-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plus-circle"></i> إنشاء فاتورة جديدة</h1>
            <a href="list.php" class="btn" style="background: #f0f0f0; color: #333;">
                <i class="fas fa-arrow-right"></i> عودة
            </a>
        </div>

        <div id="messages"></div>

        <form id="invoiceForm">
            <!-- معلومات الفاتورة -->
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> معلومات الفاتورة</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer">الزبون *</label>
                        <select id="customer" name="customer_id" required>
                            <option value="">-- اختر زبون --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo htmlspecialchars($customer['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="invoiceDate">تاريخ الفاتورة *</label>
                        <input type="date" id="invoiceDate" name="invoice_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dueDate">تاريخ الاستحقاق</label>
                        <input type="date" id="dueDate" name="due_date">
                    </div>

                    <div class="form-group">
                        <label for="status">الحالة</label>
                        <select id="status" name="status">
                            <option value="draft">مسودة</option>
                            <option value="issued" selected>مصدرة</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- المنتجات -->
            <div class="section">
                <h3><i class="fas fa-boxes"></i> المنتجات</h3>
                <button type="button" class="btn btn-small" onclick="addItemRow()">
                    <i class="fas fa-plus"></i> إضافة منتج
                </button>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">الترتيب</th>
                            <th style="width: 40%;">المنتج</th>
                            <th style="width: 10%;">الكمية</th>
                            <th style="width: 15%;">السعر</th>
                            <th style="width: 15%;">الخصم</th>
                            <th style="width: 10%;">الضريبة</th>
                            <th style="width: 5%;">حذف</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                </table>
            </div>

            <!-- الملاحظات -->
            <div class="section">
                <h3><i class="fas fa-sticky-note"></i> ملاحظات إضافية</h3>
                <div class="form-group">
                    <label for="notes">ملاحظات:</label>
                    <textarea id="notes" name="notes" rows="3" style="padding: 0.8rem; border: 2px solid #eee; border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
                </div>
            </div>

            <!-- الملخص -->
            <div class="summary">
                <div class="summary-row">
                    <span>الإجمالي الجزئي:</span>
                    <span id="subtotal">0.00 IQD</span>
                </div>
                <div class="summary-row">
                    <span>الخصم الكلي:</span>
                    <span id="discount">0.00 IQD</span>
                </div>
                <div class="summary-row">
                    <span>الضريبة الكلية:</span>
                    <span id="tax">0.00 IQD</span>
                </div>
                <div class="summary-row total">
                    <span>الإجمالي النهائي:</span>
                    <span id="total">0.00 IQD</span>
                </div>
            </div>

            <!-- الأزرار -->
            <div class="section">
                <div class="actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> حفظ الفاتورة
                    </button>
                    <button type="reset" class="btn" style="background: #f0f0f0; color: #333;">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </button>
                    <a href="list.php" class="btn" style="background: #f0f0f0; color: #333;">
                        <i class="fas fa-times"></i> إلغاء
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        const products = <?php echo $products_json; ?>;
        let itemCount = 0;

        function addItemRow() {
            itemCount++;
            const row = document.createElement('tr');
            row.id = `item-${itemCount}`;
            row.innerHTML = `
                <td>${itemCount}</td>
                <td>
                    <select onchange="updateRow(this)">
                        <option value="">-- اختر --</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.selling_price}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="number" value="1" min="1" onchange="updateRow(this)"></td>
                <td><input type="number" readonly></td>
                <td><input type="number" value="0" step="0.01" onchange="updateRow(this)"></td>
                <td><input type="number" value="0" step="0.01" onchange="updateRow(this)"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-small" onclick="this.closest('tr').remove(); updateTotals()">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            document.getElementById('itemsBody').appendChild(row);
        }

        function updateRow(el) {
            const row = el.closest('tr');
            const select = row.querySelector('select');
            const inputs = row.querySelectorAll('input');
            
            const qty = parseFloat(inputs[0].value) || 0;
            const price = parseFloat(select.selectedOptions[0]?.dataset.price) || 0;
            const discount = parseFloat(inputs[1].value) || 0;
            const tax = parseFloat(inputs[2].value) || 0;
            
            const subtotal = (qty * price) - discount;
            const total = subtotal + tax;
            
            inputs[0].value = price.toFixed(2);
            inputs[0].readonly = true;
            inputs[3].value = total.toFixed(2);
            
            updateTotals();
        }

        function updateTotals() {
            let subtotal = 0, discount = 0, tax = 0;
            
            document.querySelectorAll('#itemsBody tr').forEach(row => {
                const inputs = row.querySelectorAll('input');
                const qty = parseFloat(inputs[0].value) || 0;
                const price = parseFloat(inputs[0].value) || 0;
                const disc = parseFloat(inputs[1].value) || 0;
                const t = parseFloat(inputs[2].value) || 0;
                
                subtotal += (qty * price) - disc;
                discount += disc;
                tax += t;
            });
            
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' IQD';
            document.getElementById('discount').textContent = discount.toFixed(2) + ' IQD';
            document.getElementById('tax').textContent = tax.toFixed(2) + ' IQD';
            document.getElementById('total').textContent = total.toFixed(2) + ' IQD';
        }

        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const items = [];
            document.querySelectorAll('#itemsBody tr').forEach(row => {
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
                showMessage('يجب إضافة منتج واحد على الأقل', 'danger');
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
            
            fetch('../api/create_invoice.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showMessage('تم إنشاء الفاتورة بنجاح!', 'success');
                    setTimeout(() => {
                        window.location.href = `view.php?id=${res.invoice_id}`;
                    }, 1500);
                } else {
                    showMessage(res.message, 'danger');
                }
            })
            .catch(err => showMessage(err.message, 'danger'));
        });

        function showMessage(msg, type) {
            const div = document.createElement('div');
            div.className = `alert alert-${type}`;
            div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
            document.getElementById('messages').appendChild(div);
            setTimeout(() => div.remove(), 5000);
        }

        addItemRow();
    </script>
</body>
</html>
