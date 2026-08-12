/**
 * نظام المبيعات - JavaScript الرئيسي
 */

// الإعدادات العامة
const API_URL = 'http://localhost/complete-sales/api/';
const SITE_URL = 'http://localhost/complete-sales/';

// ========== وظائف الرسائل ==========
function showAlert(message, type = 'info', duration = 4000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${getIcon(type)}"></i>
        <span>${message}</span>
    `;

    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.remove();
    }, duration);
}

function getIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// ========== وظائف التنسيق ==========
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-IQ', {
        style: 'currency',
        currency: 'IQD'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('ar-IQ');
}

function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString('ar-IQ');
}

// ========== وظائف النوافذ المنبثقة ==========
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// إغلاق النافذة المنبثقة عند النقر على الخلفية
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
    }
});

// إغلاق النافذة المنبثقة عند النقر على زر الإغلاق
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-close')) {
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.classList.remove('show');
        }
    }
});

// ========== وظائف الجدول ==========
function deleteRow(id, table, message = 'هل أنت متأكد من الحذف؟') {
    if (confirm(message)) {
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
        }
        // يمكن إضافة طلب حذف من الخادم هنا
    }
}

// ========== وظائف البحث ==========
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    input.addEventListener('keyup', () => {
        const filter = input.value.toUpperCase();

        for (let row of rows) {
            const text = row.textContent.toUpperCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
}

// ========== وظائف الفرز ==========
function sortTable(tableId, columnIndex, ascending = true) {
    const table = document.getElementById(tableId);
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));

    rows.sort((a, b) => {
        const cellA = a.getElementsByTagName('td')[columnIndex].textContent;
        const cellB = b.getElementsByTagName('td')[columnIndex].textContent;

        if (isNaN(cellA) || isNaN(cellB)) {
            return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        } else {
            return ascending ? cellA - cellB : cellB - cellA;
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

// ========== وظائف الحسابات ==========
function calculateInvoiceTotal() {
    const items = document.querySelectorAll('tbody tr');
    let subtotal = 0;

    items.forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity')?.value) || 0;
        const price = parseFloat(row.querySelector('.price')?.value) || 0;
        const discount = parseFloat(row.querySelector('.discount')?.value) || 0;

        subtotal += (quantity * price) - discount;
    });

    const taxRate = 15;
    const tax = (subtotal * taxRate) / 100;
    const total = subtotal + tax;

    if (document.getElementById('subtotal')) {
        document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    }
    if (document.getElementById('tax')) {
        document.getElementById('tax').textContent = formatCurrency(tax);
    }
    if (document.getElementById('total')) {
        document.getElementById('total').textContent = formatCurrency(total);
    }

    return { subtotal, tax, total };
}

// ========== وظائف الحفظ ==========
function saveFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const data = new FormData(form);
    const object = {};
    data.forEach(key => {
        object[key[0]] = key[1];
    });

    localStorage.setItem(`form_${formId}`, JSON.stringify(object));
}

function loadFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const data = localStorage.getItem(`form_${formId}`);
    if (data) {
        const object = JSON.parse(data);
        Object.keys(object).forEach(key => {
            const field = form.elements[key];
            if (field) {
                field.value = object[key];
            }
        });
    }
}

// ========== حفظ تلقائي ==========
function autoSaveForm(formId, interval = 30000) {
    const form = document.getElementById(formId);
    if (!form) return;

    setInterval(() => {
        saveFormData(formId);
        console.log('✓ تم الحفظ التلقائي');
    }, interval);
}

// ========== الطباعة ==========
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '', 'height=400,width=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>طباعة</title>
            <link rel="stylesheet" href="${SITE_URL}assets/css/style.css">
            <style>
                body { direction: rtl; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            ${element.innerHTML}
            <script>
                window.print();
                window.close();
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// ========== التصدير إلى CSV ==========
function exportToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        if (cells.length === 0) continue;

        let rowData = [];
        for (let cell of cells) {
            rowData.push('"' + cell.textContent.trim() + '"');
        }
        csv.push(rowData.join(','));
    }

    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// ========== التحقق من النماذج ==========
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    return isValid;
}

// ========== تنسيق الأرقام ==========
function formatNumber(input) {
    input.addEventListener('keyup', () => {
        input.value = input.value.replace(/[^0-9.]/g, '');
    });
}

function formatPhone(input) {
    input.addEventListener('keyup', () => {
        input.value = input.value.replace(/[^0-9]/g, '');
    });
}

// ========== تحديث الأسعار ==========
function updatePrice(elementId, productId) {
    // يمكن استخدام هذا لجلب السعر من الخادم
    console.log('تحديث السعر للمنتج:', productId);
}

// ========== تحديث الكميات ==========
function updateQuantity(elementId) {
    calculateInvoiceTotal();
}

// ========== الرجوع ==========
function goBack() {
    window.history.back();
}

// ========== إغلاق الجلسة ==========
function logout() {
    if (confirm('هل تريد تسجيل الخروج؟')) {
        fetch(`${API_URL}logout.php`)
            .then(() => {
                window.location.href = `${SITE_URL}login.html`;
            });
    }
}

// ========== معالجة الخطأ ==========
function handleError(error) {
    console.error('❌ خطأ:', error);
    showAlert('حدث خطأ: ' + error.message, 'error');
}

// ========== تهيئة الصفحة ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('✓ تم تحميل النظام');

    // تنسيق حقول الأرقام
    document.querySelectorAll('[type="number"]').forEach(input => {
        formatNumber(input);
    });

    // تنسيق حقول الهواتف
    document.querySelectorAll('[data-phone]').forEach(input => {
        formatPhone(input);
    });

    // الحفظ التلقائي للنماذج
    document.querySelectorAll('form[data-auto-save]').forEach(form => {
        autoSaveForm(form.id);
    });
});
