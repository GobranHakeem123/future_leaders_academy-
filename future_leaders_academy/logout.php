<?php
// بدء الجلسة
session_start();

// التحقق من أن المستخدم مسجل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// معالجة تسجيل الخروج
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // تدمير جميع بيانات الجلسة
    $_SESSION = array();
    
    // إذا كنت تريد تدمير الجلسة تماماً، قم أيضاً بحذف كوكيز الجلسة
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // أخيراً، تدمير الجلسة
    session_destroy();
    
    // توجيه المستخدم إلى صفحة تسجيل الدخول مع رسالة
    header('Location: login.php?logout=success');
    exit();
}

// إذا لم يكن هناك تأكيد، نعرض صفحة تأكيد
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الخروج - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --danger-color: #f72585;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-danger: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .logout-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-danger);
            border-radius: 25px 25px 0 0;
        }
        
        .logout-icon {
            font-size: 4rem;
            background: var(--gradient-danger);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        p {
            color: #718096;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .user-info {
            background: #f7fafc;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        
        .user-info i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .user-info h4 {
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .user-info small {
            color: #718096;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-width: 150px;
        }
        
        .btn-danger {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 5px 20px rgba(247, 37, 133, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(247, 37, 133, 0.4);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .logout-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .logout-footer p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin: 0;
        }
        
        @media (max-width: 576px) {
            .logout-container {
                padding: 30px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        .security-notice {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: right;
        }
        
        .security-notice i {
            color: #f97316;
            margin-left: 10px;
        }
        
        .security-notice p {
            color: #ea580c;
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h2>تأكيد تسجيل الخروج</h2>
        <p>هل أنت متأكد أنك تريد تسجيل الخروج من لوحة التحكم؟</p>
        
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <h4><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'المسؤول'); ?></h4>
            <small>مدير النظام</small>
        </div>
        
        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <p>سيتم إغلاق جلسة العمل الحالية وتوجيهك إلى صفحة تسجيل الدخول.</p>
        </div>
        
        <div class="btn-group">
            <a href="?confirm=yes" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                نعم، سجل الخروج
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                إلغاء والعودة
            </a>
        </div>
        
        <div class="logout-footer">
            <p>نظام إدارة الملفات © <?php echo date('Y'); ?></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-redirect after 30 seconds if no action
        let timer = 30;
        const timerElement = document.createElement('div');
        timerElement.style.marginTop = '15px';
        timerElement.style.color = '#64748b';
        timerElement.style.fontSize = '0.9rem';
        timerElement.innerHTML = `سيتم إعادة التوجيه تلقائياً خلال <span id="countdown">${timer}</span> ثانية`;
        document.querySelector('.btn-group').after(timerElement);
        
        const countdown = setInterval(() => {
            timer--;
            document.getElementById('countdown').textContent = timer;
            
            if (timer <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php';
            }
        }, 1000);
        
        // Prevent accidental clicks
        const logoutBtn = document.querySelector('.btn-danger');
        let logoutClicked = false;
        
        logoutBtn.addEventListener('click', function(e) {
            if (!logoutClicked) {
                logoutClicked = true;
                
                // Add loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري تسجيل الخروج...';
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.7';
                
                // Allow the click to proceed
                return true;
            }
            
            e.preventDefault();
            return false;
        });
        
        // Add some animations
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.logout-container');
            container.style.animation = 'fadeIn 0.5s ease';
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>