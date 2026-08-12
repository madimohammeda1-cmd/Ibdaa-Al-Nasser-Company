# نظام المبيعات المتكامل
## Integrated Sales Management System

نظام متطور وشامل لإدارة المبيعات والفواتير والمخزون والحسابات، مبني باستخدام PHP و JavaScript مع قاعدة بيانات MySQL.

---

## 🎯 المميزات الرئيسية

### 1. **إدارة الفواتير** 📄
- ✅ إنشاء وتعديل الفواتير
- ✅ نظام الأقساط (Installments)
- ✅ فواتير مرتجعة (Return Invoices)
- ✅ معاينة وطباعة الفواتير
- ✅ حفظ كمسودة
- ✅ تتبع حالة الدفع
- ✅ نظام الخصم (نسبة مئوية أو مبلغ ثابت)
- ✅ حساب الضريبة التلقائي

### 2. **إدارة الزبائن** 👥
- ✅ إنشاء وتعديل حسابات الزبائن
- ✅ تتبع الرصيد والحد الائتماني
- ✅ سجل المعاملات
- ✅ تصنيفات الزبائن

### 3. **إدارة المنتجات والمخزون** 📦
- ✅ إدارة المنتجات (إضافة، تعديل، حذف)
- ✅ تتبع الكميات والمستودعات المتعددة
- ✅ الأذونات المخزنية (input/output/transfer)
- ✅ نظام الجرد والعد
- ✅ تنبيهات الحد الأدنى للمخزون

### 4. **نظام الحسابات** 💰
- ✅ الحسابات المدينة والدائنة
- ✅ تتبع الحركات المالية
- ✅ تقارير الربح والخسارة
- ✅ قائمة الدخل
- ✅ الميزانية العمومية
- ✅ سجل الدفعات

### 5. **نظام الصلاحيات والأدوار** 🔐
- ✅ ثلاثة أدوار أساسية:
  - **Owner (المالك)**: صلاحيات كاملة
  - **Admin (مدير)**: صلاحيات إدارية كاملة (ما عدا المستخدمين والإعدادات)
  - **User (مستخدم عادي)**: صلاحيات محدودة
- ✅ نظام الصلاحيات المرن
- ✅ تسجيل العمليات (Audit Log)

### 6. **التقارير والإحصائيات** 📊
- ✅ تقارير الحسابات العامة
- ✅ الحركة المالية
- ✅ الربح والخسارة
- ✅ قائمة الدخل
- ✅ الميزانية العمومية
- ✅ تقارير المبيعات
- ✅ إحصائيات العملاء

### 7. **النظام المالي** 💳
- ✅ سجل الدفعات
- ✅ طرق الدفع المتعددة (نقد، شيك، تحويل بنكي، بطاقة)
- ✅ نظام الأقساط
- ✅ تتبع الفواتير المستحقة
- ✅ تنبيهات الدفع المتأخر

---

## 📋 البنية الأساسية

```
sales-system/
├── config.php                 # ملف الإعدادات والاتصال بقاعدة البيانات
├── login.php                  # صفحة تسجيل الدخول
├── logout.php                 # تسجيل الخروج
├── dashboard.php              # لوحة التحكم الرئيسية
│
├── api/                       # ملفات API
│   ├── invoices.php          # API إدارة الفواتير
│   ├── customers.php         # API إدارة الزبائن
│   ├── products.php          # API إدارة المنتجات
│   ├── payments.php          # API إدارة الدفعات
│   └── reports.php           # API التقارير
│
├── invoices/                  # إدارة الفواتير
│   ├── list.php              # قائمة الفواتير
│   ├── create.php            # إنشاء فاتورة جديدة
│   ├── edit.php              # تعديل الفاتورة
│   ├── view.php              # عرض تفاصيل الفاتورة
│   ├── print.php             # طباعة الفاتورة
│   └── return.php            # إنشاء فاتورة مرتجعة
│
├── customers/                 # إدارة الزبائن
│   ├── list.php              # قائمة الزبائن
│   ├── create.php            # إضافة زبون جديد
│   ├── edit.php              # تعديل بيانات الزبون
│   └── view.php              # عرض تفاصيل الزبون
│
├── products/                  # إدارة المنتجات
│   ├── list.php              # قائمة المنتجات
│   ├── create.php            # إضافة منتج جديد
│   └── edit.php              # تعديل المنتج
│
├── inventory/                 # إدارة المخزون
│   ├── index.php             # لوحة المخزون
│   ├── permits.php           # الأذونات المخزنية
│   ├── count.php             # الجرد والعد
│   └── warehouses.php        # إدارة المستودعات
│
├── accounts/                  # إدارة الحسابات
│   ├── index.php             # الحسابات
│   ├── customer_accounts.php  # حسابات العملاء
│   ├── chart_of_accounts.php  # دليل الحسابات
│   └── movements.php         # الحركات المالية
│
├── reports/                   # التقارير
│   ├── index.php             # التقارير الرئيسية
│   ├── income_statement.php   # قائمة الدخل
│   ├── balance_sheet.php      # الميزانية العمومية
│   ├── profit_loss.php        # الربح والخسارة
│   ├── sales_report.php       # تقارير المبيعات
│   └── customer_report.php    # تقارير العملاء
│
├── settings/                  # الإعدادات
│   ├── index.php             # الإعدادات الرئيسية
│   ├── users.php             # إدارة المستخدمين
│   ├── permissions.php       # إدارة الصلاحيات
│   ├── company.php           # بيانات الشركة
│   └── backup.php            # النسخ الاحتياطي
│
├── assets/                    # الملفات الثابتة
│   ├── css/
│   │   ├── style.css         # الأنماط الرئيسية
│   │   └── print.css         # أنماط الطباعة
│   ├── js/
│   │   ├── app.js            # تطبيق JavaScript الرئيسي
│   │   ├── invoices.js       # وظائف الفواتير
│   │   ├── customers.js      # وظائف الزبائن
│   │   └── utils.js          # دوال مساعدة
│   └── img/
│       └── logo.png          # شعار الشركة
│
├── uploads/                   # المرفقات والملفات المرفوعة
├── logs/                      # ملفات السجلات
├── templates/                 # نماذج البريد والطباعة
│
└── database.sql              # ملف قاعدة البيانات

```

---

## 🔧 متطلبات التثبيت

### المتطلبات الأساسية:
- **PHP >= 7.4**
- **MySQL >= 5.7**
- **Web Server** (Apache, Nginx)
- **Composer** (اختياري للمكتبات الإضافية)

### المكتبات المطلوبة:
```bash
# TCPDF - لطباعة الفواتير
composer require tecnickcom/tcpdf

# PHPMailer - لإرسال البريد
composer require phpmailer/phpmailer

# JWT - للمصادقة المتقدمة
composer require firebase/php-jwt
```

---

## 📥 خطوات التثبيت

### 1. استنساخ المستودع
```bash
git clone https://github.com/yourusername/sales-system.git
cd sales-system
```

### 2. تثبيت المكتبات
```bash
composer install
```

### 3. إنشاء قاعدة البيانات
```bash
# اتصل بـ MySQL
mysql -u root -p

# أنشئ قاعدة البيانات
CREATE DATABASE sales_system;
USE sales_system;

# استورد ملف قاعدة البيانات
SOURCE database.sql;
```

### 4. تعديل ملف الإعدادات
```php
// config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sales_system');
define('SITE_URL', 'http://localhost/sales-system/');
```

### 5. إنشاء المجلدات المطلوبة
```bash
mkdir -p uploads logs templates
chmod 755 uploads logs templates
```

### 6. الوصول إلى النظام
```
URL: http://localhost/sales-system/
اسم المستخدم: admin
كلمة المرور: admin
```

---

## 🔐 بيانات الدخول الافتراضية

```
اسم المستخدم: admin
كلمة المرور: admin
الدور: Owner (مالك)
```

⚠️ **هام**: يجب تغيير بيانات المسؤول الافتراضية فور الدخول الأول!

---

## 📊 نماذج قاعدة البيانات الرئيسية

### جدول المستخدمين (users)
```sql
- id: المعرف الفريد
- username: اسم المستخدم
- email: البريد الإلكتروني
- password: كلمة المرور (مشفرة)
- full_name: الاسم الكامل
- role: الدور (owner/admin/user)
- status: الحالة (active/inactive)
```

### جدول الفواتير (invoices)
```sql
- id: المعرف الفريد
- invoice_number: رقم الفاتورة الفريد
- customer_id: رقم الزبون
- invoice_date: تاريخ الفاتورة
- due_date: تاريخ الاستحقاق
- subtotal: الإجمالي الجزئي
- discount: الخصم
- tax: الضريبة
- total: الإجمالي النهائي
- status: حالة الفاتورة (draft/issued/paid/partial/returned)
- payment_status: حالة الدفع (unpaid/partial/paid)
```

### جدول تفاصيل الفواتير (invoice_items)
```sql
- id: المعرف الفريد
- invoice_id: رقم الفاتورة
- product_id: رقم المنتج
- item_sequence: التسلسل في الفاتورة
- quantity: الكمية
- unit_price: السعر الواحد
- discount: الخصم
- tax: الضريبة
- subtotal: الإجمالي الجزئي
```

### جدول الزبائن (customers)
```sql
- id: المعرف الفريد
- name: اسم الزبون
- phone: رقم الهاتف
- email: البريد الإلكتروني
- address: العنوان
- city: المدينة
- tax_id: رقم الضريبة
- credit_limit: حد الائتمان
- balance: الرصيد الحالي
```

### جدول المنتجات (products)
```sql
- id: المعرف الفريد
- name: اسم المنتج
- sku: رمز المنتج
- category: الفئة
- purchase_price: سعر الشراء
- selling_price: سعر البيع
- quantity: الكمية المتاحة
- warehouse_id: رقم المستودع
```

---

## 🔄 سير العمل الأساسي

### عملية المبيعات:
```
1. إنشاء فاتورة جديدة
   ├─ تحديد الزبون
   ├─ إضافة المنتجات
   ├─ حساب الخصم والضريبة
   └─ حفظ الفاتورة (مسودة أو مصدرة)

2. إدارة الفاتورة
   ├─ عرض التفاصيل
   ├─ تعديل (إذا كانت مسودة)
   ├─ معاينة
   └─ طباعة

3. تسجيل الدفع
   ├─ تحديد المبلغ
   ├─ تحديد طريقة الدفع
   └─ حفظ السجل

4. التقارير
   ├─ تقارير المبيعات
   ├─ تقارير الحسابات
   └─ تقارير المخزون
```

### عملية الأذونات المخزنية:
```
1. إنشاء أذن مخزني
   ├─ تحديد النوع (دخول/خروج/تحويل)
   └─ إضافة المنتجات

2. الموافقة على الأذن
   └─ يتم التعديل تلقائياً على المخزون

3. تحديث المخزون
   └─ تحديث الكميات في المستودع
```

---

## 💾 النسخ الاحتياطي والاستعادة

### النسخ الاحتياطي:
```bash
# النسخ الاحتياطي من MySQL
mysqldump -u root -p sales_system > backup_$(date +%Y%m%d).sql

# النسخ الاحتياطي كامل المشروع
tar -czf sales-system_backup_$(date +%Y%m%d).tar.gz sales-system/
```

### الاستعادة:
```bash
# استعادة قاعدة البيانات
mysql -u root -p sales_system < backup_20240101.sql

# استعادة المشروع كاملاً
tar -xzf sales-system_backup_20240101.tar.gz
```

---

## 🚀 النشر على GitHub

### الخطوات:
```bash
# 1. تهيئة المستودع المحلي
git init
git add .
git commit -m "Initial commit: Complete sales system"

# 2. إضافة المستودع البعيد
git remote add origin https://github.com/yourusername/sales-system.git

# 3. دفع الملفات
git branch -M main
git push -u origin main

# 4. إضافة ملف .gitignore
cat > .gitignore << EOF
config.php
uploads/*
logs/*
.DS_Store
vendor/
composer.lock
EOF

# 5. تحديث الملفات
git add .gitignore
git commit -m "Add gitignore"
git push
```

### ملف .gitignore:
```
# ملفات الإعدادات الحساسة
config.php

# المجلدات المحلية
uploads/
logs/
vendor/
node_modules/

# ملفات النظام
.DS_Store
Thumbs.db
*.swp
*.swo

# ملفات البيئة
.env
.env.local

# ملفات النسخ الاحتياطي
*.bak
*.sql

# ملفات المحرر
.vscode/
.idea/
*.sublime-workspace
```

---

## 🔐 الأمان والنصائح الهامة

### 1. تأمين كلمات المرور:
- ✅ استخدام hashing (SHA2, bcrypt)
- ✅ فرض كلمات مرور قوية
- ✅ تنبيهات تغيير كلمة المرور دورياً

### 2. تأمين قاعدة البيانات:
- ✅ استخدام Prepared Statements
- ✅ التحقق من الإدخالات
- ✅ تشفير البيانات الحساسة

### 3. التحكم في الوصول:
- ✅ التحقق من الصلاحيات في كل صفحة
- ✅ تسجيل العمليات (Audit Log)
- ✅ تتبع محاولات الوصول غير المصرح

### 4. النسخ الاحتياطي:
- ✅ نسخ احتياطية يومية
- ✅ اختبار الاستعادة بانتظام
- ✅ تخزين آمن للنسخ الاحتياطية

---

## 📞 الدعم والمساهمة

### الإبلاغ عن المشاكل:
يرجى فتح issue على GitHub مع:
- وصف المشكلة
- خطوات إعادة الإنتاج
- نسخة PHP و MySQL
- الرسائل الخطأ

### المساهمة:
```bash
# 1. انسخ المستودع (Fork)
# 2. أنشئ فرع جديد
git checkout -b feature/my-feature

# 3. قم بالتعديلات والـ commits
# 4. ادفع التغييرات
git push origin feature/my-feature

# 5. أنشئ Pull Request
```

---

## 📄 الترخيص

هذا المشروع مرخص تحت **MIT License**.

---

## 👨‍💻 المطور

تم تطوير هذا النظام لتوفير حل متكامل وشامل لإدارة المبيعات والفواتير.

---

## 🎉 شكراً لاستخدامك النظام!

للمزيد من المعلومات والدعم، تفضل بزيارة المستودع على GitHub.

**آخر تحديث:** 2024/01/01
