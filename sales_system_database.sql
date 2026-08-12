-- نظام المبيعات المتكامل - قاعدة البيانات
-- ============================================

-- جدول المستخدمين
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('owner', 'admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الزبائن
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(150),
    address TEXT,
    city VARCHAR(100),
    tax_id VARCHAR(50),
    credit_limit DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2) DEFAULT 0,
    notes TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    sku VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(100),
    purchase_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    min_quantity INT DEFAULT 10,
    warehouse_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);

-- جدول المستودعات
CREATE TABLE warehouses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(200),
    manager_id INT,
    capacity INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- جدول الفواتير
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft', 'issued', 'paid', 'partial', 'returned') DEFAULT 'draft',
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    paid_amount DECIMAL(12,2) DEFAULT 0,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX (invoice_number),
    INDEX (invoice_date)
);

-- جدول تفاصيل الفواتير
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    item_sequence INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    discount_type ENUM('fixed', 'percentage') DEFAULT 'fixed',
    tax DECIMAL(10,2) DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول الفواتير المرتجعة
CREATE TABLE return_invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_number VARCHAR(50) UNIQUE NOT NULL,
    original_invoice_id INT NOT NULL,
    customer_id INT NOT NULL,
    return_date DATE NOT NULL,
    subtotal DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    reason TEXT,
    status ENUM('draft', 'approved', 'completed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (original_invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول تفاصيل الفواتير المرتجعة
CREATE TABLE return_invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_invoice_id INT NOT NULL,
    original_item_id INT NOT NULL,
    quantity INT NOT NULL,
    reason VARCHAR(200),
    FOREIGN KEY (return_invoice_id) REFERENCES return_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (original_item_id) REFERENCES invoice_items(id)
);

-- جدول نظام الأقساط
CREATE TABLE installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    installment_number INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    payment_date DATE,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- جدول سجل الدفع
CREATE TABLE payment_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'card') DEFAULT 'cash',
    reference_number VARCHAR(100),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول الأذونات المخزنية
CREATE TABLE warehouse_permits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permit_number VARCHAR(50) UNIQUE NOT NULL,
    permit_date DATE NOT NULL,
    permit_type ENUM('input', 'output', 'transfer') DEFAULT 'output',
    from_warehouse INT,
    to_warehouse INT,
    status ENUM('pending', 'approved', 'completed') DEFAULT 'pending',
    approval_by INT,
    approved_at DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_warehouse) REFERENCES warehouses(id),
    FOREIGN KEY (to_warehouse) REFERENCES warehouses(id),
    FOREIGN KEY (approval_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول تفاصيل الأذونات المخزنية
CREATE TABLE permit_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permit_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (permit_id) REFERENCES warehouse_permits(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول الجرد
CREATE TABLE inventory_counts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    count_number VARCHAR(50) UNIQUE NOT NULL,
    warehouse_id INT NOT NULL,
    count_date DATE NOT NULL,
    status ENUM('draft', 'completed') DEFAULT 'draft',
    counted_by INT NOT NULL,
    completed_by INT,
    completed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (counted_by) REFERENCES users(id),
    FOREIGN KEY (completed_by) REFERENCES users(id)
);

-- جدول تفاصيل الجرد
CREATE TABLE inventory_count_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    count_id INT NOT NULL,
    product_id INT NOT NULL,
    system_quantity INT NOT NULL,
    physical_quantity INT NOT NULL,
    difference INT,
    notes TEXT,
    FOREIGN KEY (count_id) REFERENCES inventory_counts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول الحركات المالية
CREATE TABLE financial_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movement_type ENUM('invoice', 'payment', 'return', 'adjustment') DEFAULT 'invoice',
    reference_id INT,
    account_id INT,
    amount DECIMAL(12,2) NOT NULL,
    movement_direction ENUM('debit', 'credit'),
    movement_date DATE NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول حسابات العملاء/الموردين (الحسابات الدائنة والمدينة)
CREATE TABLE customer_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    opening_balance DECIMAL(12,2) DEFAULT 0,
    current_balance DECIMAL(12,2) DEFAULT 0,
    account_type ENUM('receivable', 'payable') DEFAULT 'receivable',
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    UNIQUE KEY unique_customer_type (customer_id, account_type)
);

-- جدول الصلاحيات
CREATE TABLE permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    module VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول ربط الأدوار بالصلاحيات
CREATE TABLE role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('owner', 'admin', 'user') NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permission_id) REFERENCES permissions(id),
    UNIQUE KEY unique_role_permission (role, permission_id)
);

-- جدول سجل العمليات (Audit Log)
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100),
    module VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- إدراج المستخدم الافتراضي (admin)
INSERT INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@sales.local', SHA2('admin', 256), 'مدير النظام', 'owner', 'active');

-- إدراج الصلاحيات الأساسية
INSERT INTO permissions (name, description, module) VALUES
('view_dashboard', 'عرض لوحة التحكم', 'dashboard'),
('view_invoices', 'عرض الفواتير', 'invoices'),
('create_invoices', 'إنشاء فواتير', 'invoices'),
('edit_invoices', 'تعديل الفواتير', 'invoices'),
('delete_invoices', 'حذف الفواتير', 'invoices'),
('print_invoices', 'طباعة الفواتير', 'invoices'),
('view_customers', 'عرض الزبائن', 'customers'),
('create_customers', 'إنشاء زبائن', 'customers'),
('edit_customers', 'تعديل الزبائن', 'customers'),
('view_products', 'عرض المنتجات', 'products'),
('manage_products', 'إدارة المنتجات', 'products'),
('view_inventory', 'عرض المخزون', 'inventory'),
('manage_inventory', 'إدارة المخزون', 'inventory'),
('view_reports', 'عرض التقارير', 'reports'),
('view_accounts', 'عرض الحسابات', 'accounts'),
('manage_accounts', 'إدارة الحسابات', 'accounts'),
('manage_users', 'إدارة المستخدمين', 'users'),
('manage_settings', 'إدارة الإعدادات', 'settings');

-- ربط الصلاحيات بالأدوار (Owner لديه جميع الصلاحيات)
INSERT INTO role_permissions (role, permission_id) 
SELECT 'owner', id FROM permissions;

-- صلاحيات Admin
INSERT INTO role_permissions (role, permission_id) 
SELECT 'admin', id FROM permissions WHERE name NOT IN ('manage_users', 'manage_settings');

-- صلاحيات User العادي
INSERT INTO role_permissions (role, permission_id) 
SELECT 'user', id FROM permissions WHERE name IN (
    'view_dashboard', 'view_invoices', 'create_invoices', 'print_invoices',
    'view_customers', 'view_products', 'view_inventory', 'view_reports'
);
