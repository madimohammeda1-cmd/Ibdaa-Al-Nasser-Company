<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام المبيعات المتكامل - Integrated Sales System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            overflow-x: hidden;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-login, .btn-signup {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-login {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-login:hover {
            background: #667eea;
            color: white;
        }

        .btn-signup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }

        .hero-content {
            z-index: 10;
            text-align: center;
            max-width: 800px;
            padding: 2rem;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            font-weight: 800;
            animation: slideInDown 0.8s ease-out;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: slideInUp 0.8s ease-out;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: slideInUp 0.8s ease-out 0.2s both;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: white;
            color: #667eea;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 3rem;
            font-weight: 700;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        .stat-item h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Pricing Section */
        .pricing {
            padding: 6rem 2rem;
            background: #f8f9fa;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            position: relative;
        }

        .pricing-card.featured {
            border: 3px solid #667eea;
            transform: scale(1.05);
        }

        .pricing-card h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .price {
            font-size: 2.5rem;
            color: #667eea;
            font-weight: 700;
            margin: 1rem 0;
        }

        .price-period {
            color: #666;
            font-size: 0.9rem;
        }

        .features-list {
            list-style: none;
            margin: 2rem 0;
            text-align: right;
        }

        .features-list li {
            padding: 0.5rem 0;
            color: #666;
            border-bottom: 1px solid #eee;
        }

        .features-list li:before {
            content: '✓ ';
            color: #27ae60;
            font-weight: bold;
            margin-left: 0.5rem;
        }

        .pricing-btn {
            width: 100%;
            padding: 1rem;
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 1.5rem;
        }

        .pricing-btn:hover {
            background: #667eea;
            color: white;
        }

        /* Footer */
        footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
            text-align: right;
        }

        .footer-section h4 {
            margin-bottom: 1rem;
            color: #667eea;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #667eea;
        }

        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 2rem;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .nav-links {
                display: none;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .pricing-card.featured {
                transform: scale(1);
            }

            .features-grid,
            .pricing-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-chart-line"></i> نظام المبيعات
        </div>
        <ul class="nav-links">
            <li><a href="#features">المميزات</a></li>
            <li><a href="#pricing">الأسعار</a></li>
            <li><a href="#contact">التواصل</a></li>
        </ul>
        <div class="nav-buttons">
            <a href="login.php" class="btn-login">دخول</a>
            <a href="login.php" class="btn-signup">ابدأ الآن</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>نظام المبيعات المتكامل</h1>
            <p>حل شامل لإدارة مبيعاتك وفواتيرك وزبائنك بسهولة وكفاءة عالية</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-arrow-left"></i> ادخل الآن
                </a>
                <a href="#features" class="btn-secondary">
                    <i class="fas fa-info-circle"></i> اعرف أكثر
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">المميزات الرئيسية</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3>إدارة الفواتير</h3>
                <p>إنشاء وتعديل الفواتير بسهولة مع نظام خصم وضريبة متقدم</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>إدارة الزبائن</h3>
                <p>تتبع كامل لزبائنك والرصيد والحد الائتماني والمعاملات</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-box"></i>
                </div>
                <h3>إدارة المخزون</h3>
                <p>تحكم كامل بالمنتجات والمستودعات والأذونات المخزنية</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>التقارير المتقدمة</h3>
                <p>تقارير شاملة عن المبيعات والأرباح والخسائر والحسابات</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>أمان عالي</h3>
                <p>نظام صلاحيات متطور وتسجيل لجميع العمليات والتحديثات</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>متوافق مع جميع الأجهزة</h3>
                <p>استخدم الموقع على الكمبيوتر أو الهاتف بدون مشاكل</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h2>19+</h2>
                <p>جدول قاعدة بيانات</p>
            </div>
            <div class="stat-item">
                <h2>50+</h2>
                <p>صفحة متقدمة</p>
            </div>
            <div class="stat-item">
                <h2>100%</h2>
                <p>احترافي وآمن</p>
            </div>
            <div class="stat-item">
                <h2>24/7</h2>
                <p>متاح دائماً</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <h2 class="section-title">الأسعار والخطط</h2>
        <div class="pricing-grid">
            <div class="pricing-card">
                <h3>خطة المبتدئين</h3>
                <div class="price">مجاني<span class="price-period">/الشهر</span></div>
                <ul class="features-list">
                    <li>إدارة الفواتير الأساسية</li>
                    <li>إدارة 50 زبون</li>
                    <li>100 فاتورة شهرياً</li>
                    <li>تقارير بسيطة</li>
                    <li>دعم عبر البريد</li>
                </ul>
                <button class="pricing-btn">ابدأ الآن</button>
            </div>

            <div class="pricing-card featured">
                <h3>خطة الاحترافي ⭐</h3>
                <div class="price">29,900<span class="price-period"> IQD/الشهر</span></div>
                <ul class="features-list">
                    <li>إدارة فواتير متقدمة</li>
                    <li>إدارة عدد غير محدود من الزبائن</li>
                    <li>فواتير غير محدودة</li>
                    <li>تقارير شاملة</li>
                    <li>إدارة المخزون الكاملة</li>
                    <li>نظام الأقساط</li>
                    <li>دعم أولويتي</li>
                </ul>
                <button class="pricing-btn">اختر هذه الخطة</button>
            </div>

            <div class="pricing-card">
                <h3>خطة المؤسسات</h3>
                <div class="price">79,900<span class="price-period"> IQD/الشهر</span></div>
                <ul class="features-list">
                    <li>كل مميزات الخطة الاحترافية</li>
                    <li>حسابات متعددة للموظفين</li>
                    <li>تقارير متقدمة جداً</li>
                    <li>API متقدمة</li>
                    <li>تدريب مجاني</li>
                    <li>دعم 24/7</li>
                    <li>نسخ احتياطية يومية</li>
                </ul>
                <button class="pricing-btn">اتصل بنا</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h4>عن النظام</h4>
                <p>نظام مبيعات متكامل يساعدك في إدارة مبيعاتك وفواتيرك بسهولة</p>
            </div>
            <div class="footer-section">
                <h4>الروابط السريعة</h4>
                <ul>
                    <li><a href="login.php">تسجيل الدخول</a></li>
                    <li><a href="#features">المميزات</a></li>
                    <li><a href="#pricing">الأسعار</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>التواصل</h4>
                <ul>
                    <li><a href="mailto:info@sales.local">البريد الإلكتروني</a></li>
                    <li><a href="tel:+964">الهاتف</a></li>
                    <li><a href="#">العنوان</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 نظام المبيعات المتكامل. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'slideInUp 0.6s ease-out';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .pricing-card').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
