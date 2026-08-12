-- =============================================
-- نظام المبيعات المتكامل - قاعدة البيانات
-- Integrated Sales System Database
-- =============================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS sales_system;
USE sales_system;

-- =============================================
-- جدول المستخدمين
-- =============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    role ENUM('owner', 'admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الزبائن
-- =============================================
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    tax_id VARCHAR(50),
    credit_limit DECIMAL(12,2) DEFAULT 0,
    current_balance DECIMAL(12,2) DEFAULT 0,
    address TEXT,
    city VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول المنتجات
-- =============================================
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    description TEXT,
    category VARCHAR(50),
    purchase_price DECIMAL(12,2) NOT NULL,
    selling_price DECIMAL(12,2) NOT NULL,
    quantity INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_sku (sku),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الفواتير
-- =============================================
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    status ENUM('draft', 'issued', 'cancelled') DEFAULT 'issued',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_number (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_date (invoice_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول بنود الفواتير
-- =============================================
CREATE TABLE invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    line_total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول سجلات الدفع
-- =============================================
CREATE TABLE payment_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'check', 'transfer', 'card') DEFAULT 'cash',
    reference_number VARCHAR(100),
    notes TEXT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    INDEX idx_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول المستودعات
-- =============================================
CREATE TABLE warehouses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    manager_name VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الجرد
-- =============================================
CREATE TABLE inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity INT DEFAULT 0,
    last_counted DATE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    UNIQUE KEY unique_product_warehouse (product_id, warehouse_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الحركات المالية
-- =============================================
CREATE TABLE financial_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    movement_type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    movement_date DATE NOT NULL,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    INDEX idx_date (movement_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الأقساط
-- =============================================
CREATE TABLE installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    installment_number INT,
    amount DECIMAL(12,2) NOT NULL,
    due_date DATE,
    paid_date DATE,
    status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول سجل التدقيق
-- =============================================
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    table_name VARCHAR(50),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- إدراج بيانات افتراضية
-- =============================================

-- المستخدم الافتراضي (admin/admin)
INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES
('admin', '$2y$10$slYQmyNdGzin7olVN3qu2OPST9/PgBkqquzi.Ge7RtVgzjQvCI45m', 'مدير النظام', 'admin@sales.local', '07700000000', 'owner', 'active');

-- المستودع الافتراضي
INSERT INTO warehouses (name, location, manager_name, status) VALUES
('المستودع الرئيسي', 'بغداد', 'مدير المستودع', 'active');

-- منتجات افتراضية
INSERT INTO products (name, sku, category, purchase_price, selling_price, quantity, status) VALUES
('لابتوب Dell', 'LAP-001', 'الإلكترونيات', 500000, 750000, 10, 'active'),
('ماوس اللاسلكي', 'MOU-001', 'الإكسسوارات', 20000, 35000, 50, 'active'),
('لوحة المفاتيح', 'KEY-001', 'الإكسسوارات', 50000, 80000, 30, 'active'),
('شاشة LED 24', 'MON-001', 'الشاشات', 200000, 320000, 15, 'active'),
('طابعة HP', 'PRI-001', 'الطابعات', 300000, 450000, 8, 'active');

-- زبائن افتراضيين
INSERT INTO customers (name, email, phone, tax_id, credit_limit, address, city, status) VALUES
('أحمد علي', 'ahmed@example.com', '07912345678', '123456789', 1000000, 'بغداد - الكاظمية', 'بغداد', 'active'),
('فاطمة محمد', 'fatima@example.com', '07987654321', '987654321', 500000, 'بغداد - الجادرية', 'بغداد', 'active'),
('علي سامي', 'ali@example.com', '07711111111', '111111111', 1500000, 'بغداد - البورصة', 'بغداد', 'active');

-- تم بنجاح!
