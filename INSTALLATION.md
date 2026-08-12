# دليل التثبيت السريع
## Quick Installation Guide

---

## 📋 المتطلبات

### المتطلبات الأساسية:
- **PHP >= 7.4**
- **MySQL >= 5.7** أو **MariaDB >= 10.3**
- **Apache** أو **Nginx**
- **Git** (للاستنساخ من GitHub)

### المتطلبات الإضافية:
- **Composer** (لتثبيت المكتبات)
- **Node.js** (اختياري)

---

## 🚀 خطوات التثبيت

### الخطوة 1️⃣: استنساخ المستودع

```bash
# من سطر الأوامر
git clone https://github.com/yourusername/sales-system.git
cd sales-system

# أو تحميل الملفات مباشرة
# اضغط على Download ZIP من GitHub وفك الضغط
```

### الخطوة 2️⃣: تثبيت المكتبات

```bash
# إذا كان لديك Composer مثبتاً
composer install

# إذا لم تكن لديك Composer، حمله من:
# https://getcomposer.org/download/

# ثم شغل الأمر التالي
php composer.phar install
```

### الخطوة 3️⃣: إنشاء قاعدة البيانات

#### الطريقة 1️⃣: باستخدام phpMyAdmin (الأسهل)

1. افتح phpMyAdmin (عادة على `http://localhost/phpmyadmin`)
2. اضغط على "New" لإنشاء قاعدة بيانات جديدة
3. أدخل اسم قاعدة البيانات: `sales_system`
4. اختر "Collation": `utf8mb4_unicode_ci`
5. اضغط "Create"
6. اذهب إلى القسم "Import"
7. اختر الملف `database.sql` من المشروع
8. اضغط "Import"

#### الطريقة 2️⃣: باستخدام سطر الأوامر

```bash
# تسجيل الدخول إلى MySQL
mysql -u root -p

# إنشاء قاعدة البيانات
CREATE DATABASE sales_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sales_system;

# استيراد الملف
SOURCE /path/to/database.sql;

# التحقق من الجداول
SHOW TABLES;

# الخروج
EXIT;
```

### الخطوة 4️⃣: تعديل ملف الإعدادات

```bash
# انسخ ملف الإعدادات الافتراضي
cp config.php.example config.php

# أو أنشئ ملف config.php جديد بالمحرر:
# ثم أضف البيانات التالية:
```

**محتوى ملف config.php:**

```php
<?php
// بيانات قاعدة البيانات
define('DB_HOST', 'localhost');        // اسم المضيف
define('DB_USER', 'root');             // اسم المستخدم
define('DB_PASS', '');                 // كلمة المرور (اتركها فارغة إذا كنت تستخدم root بدون كلمة)
define('DB_NAME', 'sales_system');     // اسم قاعدة البيانات

// إعدادات الموقع
define('SITE_URL', 'http://localhost/sales-system/');

// ملاحظة: تأكد من أن الملف غير موجود في GitHub!
?>
```

### الخطوة 5️⃣: إنشاء المجلدات المطلوبة

```bash
# في Linux/Mac
mkdir -p uploads logs templates
chmod 755 uploads logs templates

# في Windows (استخدم File Explorer)
# - انقر بزر الماوس الأيمن > New Folder
# - أنشئ المجلدات: uploads, logs, templates
```

### الخطوة 6️⃣: إعداد صلاحيات الملفات (Linux/Mac فقط)

```bash
# منح الصلاحيات للملفات
chmod 644 *.php
chmod 644 assets/*
chmod 755 uploads logs templates
chmod 755 api
```

### الخطوة 7️⃣: الوصول للنظام

افتح المتصفح وانتقل إلى:

```
http://localhost/sales-system/
```

أو إذا استخدمت نطاق مختلف:

```
http://yourdomain.com/sales-system/
```

---

## 🔐 بيانات الدخول الافتراضية

```
اسم المستخدم: admin
كلمة المرور: admin
الدور: Owner (صاحب)
```

⚠️ **تحذير أمني:**

**يجب تغيير بيانات المسؤول الافتراضية فوراً!**

الخطوات:
1. سجل الدخول بـ admin/admin
2. اذهب إلى الإعدادات → إدارة المستخدمين
3. اختر حسابك
4. اضغط "تغيير كلمة المرور"
5. أدخل كلمة مرور قوية جديدة
6. احفظ التغييرات

---

## ⚙️ الإعدادات المهمة

### تغيير البيانات الأساسية

```php
// في ملف config.php

// اسم الشركة
define('COMPANY_NAME', 'اسم شركتك');

// البريد الإلكتروني
define('MAIL_FROM', 'your-email@gmail.com');

// المنطقة الزمنية
date_default_timezone_set('Asia/Baghdad');

// الضريبة الافتراضية
define('DEFAULT_TAX_RATE', 15);

// العملة
define('CURRENCY', 'IQD');
```

### إعدادات البريد الإلكتروني

```php
// في ملف config.php

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-app-password');
define('MAIL_FROM', 'noreply@your-company.com');

// ملاحظة: استخدم "App Password" من Gmail بدلاً من كلمة المرور العادية
```

---

## 🔧 استكشاف الأخطاء

### الخطأ: "خطأ في الاتصال بقاعدة البيانات"

**الحل:**
1. تأكد من تشغيل MySQL
2. تحقق من بيانات الاتصال في config.php
3. تأكد من إنشاء قاعدة البيانات
4. تحقق من صلاحيات المستخدم

```bash
# اختبر الاتصال:
mysql -h localhost -u root -p sales_system -e "SELECT 1;"
```

### الخطأ: "الملف config.php غير موجود"

**الحل:**
1. تأكد من وجود ملف config.php في المجلد الرئيسي
2. إذا لم يكن موجوداً، انسخ config.php.example أو أنشئ واحداً جديداً

### الخطأ: "صلاحيات غير كافية للكتابة"

**الحل:**
```bash
# Linux/Mac:
chmod 755 uploads logs templates

# Windows:
# انقر بزر الماوس الأيمن > Properties > Security > Edit > Full Control
```

### الخطأ: "الصفحة البيضاء أو Not Found"

**الحل:**
1. تأكد من تمكين الـ Rewrite Module (Apache)
2. تحقق من موقع النظام في المتصفح
3. شاهل السجلات في مجلد logs

---

## 📝 ملف .htaccess (للـ Apache)

أنشئ ملف `.htaccess` في المجلد الرئيسي:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # منع الوصول المباشر للملفات الحساسة
    <FilesMatch "\.(env|sql|txt|log)$">
        Deny from all
    </FilesMatch>
    
    # إعادة التوجيه للملفات والمجلدات الموجودة
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # إعادة التوجيه للملفات الأخرى
    RewriteRule ^(.*)$ index.php?request=$1 [QSA,L]
</IfModule>

# تحسين الأداء
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>
```

---

## 📦 نسخ احتياطية وضمان الأمان

### إنشاء نسخة احتياطية دورية

```bash
# نسخة احتياطية من قاعدة البيانات فقط
mysqldump -u root -p sales_system > backup_$(date +%Y%m%d).sql

# نسخة احتياطية كاملة (قاعدة + الملفات)
tar -czf sales-system_backup_$(date +%Y%m%d).tar.gz \
  --exclude=uploads \
  --exclude=logs \
  --exclude=vendor \
  --exclude=.git \
  /path/to/sales-system/
```

### استعادة النسخة الاحتياطية

```bash
# استعادة قاعدة البيانات
mysql -u root -p sales_system < backup_20240101.sql

# استعادة المشروع كاملاً
tar -xzf sales-system_backup_20240101.tar.gz
```

---

## 🌐 النشر على الإنترنت

### متطلبات الاستضافة الموصى بها:

- **PHP >= 7.4**
- **MySQL >= 5.7**
- **Disk Space >= 500 MB**
- **RAM >= 512 MB**
- **SSL Certificate**

### الخطوات:

1. **اختر مزود الاستضافة** (Bluehost, HostGator, AWS, إلخ)

2. **حمّل الملفات** عبر FTP:
   ```bash
   ftp your-domain.com
   # استخدم FileZilla أو Cyberduck للواجهة الرسومية
   ```

3. **أنشئ قاعدة البيانات** عبر cPanel

4. **عدّل config.php** مع بيانات الاستضافة

5. **اختبر النظام** من المتصفح

---

## ✅ قائمة التحقق النهائية

- [ ] تثبيت PHP و MySQL
- [ ] استنساخ المشروع
- [ ] تثبيت المكتبات (Composer)
- [ ] إنشاء قاعدة البيانات
- [ ] إنشاء ملف config.php
- [ ] إنشاء المجلدات (uploads, logs)
- [ ] تعديل صلاحيات الملفات
- [ ] الوصول إلى النظام
- [ ] تغيير كلمة المرور الافتراضية
- [ ] اختبار جميع الميزات الرئيسية

---

## 📞 الدعم والمساعدة

إذا واجهت مشاكل:

1. **تحقق من السجلات**:
   ```bash
   tail -f logs/error_*.log
   ```

2. **اختبر الاتصال بقاعدة البيانات**:
   ```bash
   mysql -u root -p -e "SELECT VERSION();"
   ```

3. **تحقق من نسخة PHP**:
   ```bash
   php -v
   ```

4. **قم بزيارة المشروع على GitHub**:
   https://github.com/yourusername/sales-system

---

## 🎉 تم التثبيت بنجاح!

الآن يمكنك البدء في استخدام النظام. اقرأ الوثائق الرئيسية في README.md لمزيد من المعلومات.

**شكراً لاستخدام نظام المبيعات المتكامل!**
