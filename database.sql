-- نظام المبيعات - قاعدة البيانات
CREATE DATABASE IF NOT EXISTS sales_system;
USE sales_system;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الزبائن
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    tax_id VARCHAR(50),
    credit_limit DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المنتجات
CREATE TABLE IF NOT EXISTS products (
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الفواتير
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    status ENUM('draft', 'issued', 'cancelled') DEFAULT 'issued',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول بنود الفواتير
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول الدفعات
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'check', 'transfer', 'card') DEFAULT 'cash',
    reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- إدراج بيانات افتراضية
INSERT INTO users (username, password, name, email, role) VALUES
('admin', '$2y$10$slYQmyNdGzin7olVN3qu2OPST9/PgBkqquzi.Ge7RtVgzjQvCI45m', 'مدير النظام', 'admin@sales.local', 'admin');

INSERT INTO customers (name, email, phone, tax_id, address, credit_limit, status) VALUES
('أحمد محمد', 'ahmed@example.com', '07912345678', '123456789', 'بغداد - الكاظمية', 1000000, 'active'),
('فاطمة علي', 'fatima@example.com', '07987654321', '987654321', 'بغداد - الجادرية', 500000, 'active'),
('علي سامي', 'ali@example.com', '07711111111', '111111111', 'بغداد - البورصة', 1500000, 'active');

INSERT INTO products (name, sku, category, purchase_price, selling_price, quantity, status) VALUES
('لابتوب Dell', 'LAP-001', 'إلكترونيات', 500000, 750000, 10, 'active'),
('ماوس اللاسلكي', 'MOU-001', 'إكسسوارات', 20000, 35000, 50, 'active'),
('لوحة المفاتيح', 'KEY-001', 'إكسسوارات', 50000, 80000, 30, 'active'),
('شاشة LED 24', 'MON-001', 'شاشات', 200000, 320000, 15, 'active'),
('طابعة HP', 'PRI-001', 'طابعات', 300000, 450000, 8, 'active');
