<?php
require_once 'config.php';

// بدء الجلسة
session_start();

// إذا كان المستخدم مسجل الدخول بالفعل
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// دالة لإنشاء جدول admin_users إذا لم يكن موجوداً
function createAdminTableIfNotExists($pdo) {
    // التحقق مما إذا كان الجدول موجوداً
    $checkTable = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($checkTable->rowCount() == 0) {
        // إنشاء الجدول
        $sql = "CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            status TINYINT(1) DEFAULT 1,
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        // إضافة مستخدم مسؤول افتراضي
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO admin_users (username, password, role) VALUES ('admin', ?, 'super_admin')";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([$defaultPassword]);
        
        // إضافة مستخدم إضافي للاختبار
        $insertSql2 = "INSERT INTO admin_users (username, password, role) VALUES ('moderator', ?, 'moderator')";
        $stmt2 = $pdo->prepare($insertSql2);
        $stmt2->execute([$defaultPassword]);
        
        return true;
    }
    return false;
}

// إنشاء الجدول إذا لم يكن موجوداً
try {
    createAdminTableIfNotExists($pdo);
} catch (Exception $e) {
    // تسجيل الخطأ ولكن الاستمرار
    error_log("Error creating admin table: " . $e->getMessage());
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        try {
            // البحث عن المستخدم
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_role'] = $user['role'];
                
                // تسجيل وقت الدخول
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            }
        } catch (Exception $e) {
            $error = 'حدث خطأ في النظام. يرجى المحاولة لاحقاً.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-danger: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo h2 {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .logo p {
            color: #666;
            font-size: 1rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h1 {
            color: #2d3748;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header .welcome-text {
            color: #718096;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 50px 15px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .form-control::placeholder {
            color: #a0aec0;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 100%;
            background: var(--gradient-secondary);
            transition: width 0.3s ease;
            z-index: 1;
        }
        
        .btn-login:hover::before {
            width: 100%;
        }
        
        .btn-login span {
            position: relative;
            z-index: 2;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .error-message {
            background: linear-gradient(135deg, #fed7d7 0%, #fff5f5 100%);
            border: 2px solid #fc8181;
            color: #c53030;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        .error-message i {
            font-size: 1.3rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-footer p {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .admin-badge {
            display: inline-block;
            background: var(--gradient-primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
        }
        
        /* Toggle Password Visibility */
        .toggle-password {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            font-size: 1.1rem;
            z-index: 2;
        }
        
        .toggle-password:hover {
            color: var(--primary-color);
        }
        
        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Decorations */
        .decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4361ee, #4cc9f0);
            opacity: 0.1;
            z-index: 0;
        }
        
        .decoration-1 {
            top: -100px;
            right: -100px;
        }
        
        .decoration-2 {
            bottom: -100px;
            left: -100px;
            background: linear-gradient(45deg, #f72585, #7209b7);
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 30px;
                max-width: 100%;
            }
            
            .logo h2 {
                font-size: 1.8rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 12px 45px 12px 12px;
            }
        }
        
        /* Loading State */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
        }
        
        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }
        
        /* Security Info */
        .security-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding: 10px;
            background: #f7fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .security-info i {
            color: #48bb78;
            font-size: 1.2rem;
        }
        
        .security-info p {
            color: #718096;
            font-size: 0.85rem;
            margin: 0;
        }
        
        .test-credentials {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }
        
        .test-credentials h5 {
            color: #0369a1;
            margin-bottom: 10px;
        }
        
        .test-credentials p {
            color: #0c4a6e;
            font-size: 0.9rem;
            margin: 5px 0;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Decorations -->
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>
    
    <div class="login-container">
        <div class="logo">
            <h2>لوحة التحكم</h2>
            <p>نظام إدارة الملفات المتكامل</p>
        </div>
        
        <div class="login-header">
            <h1>تسجيل الدخول</h1>
            <p class="welcome-text">مرحباً بك مجدداً! يرجى إدخال بيانات الدخول</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>خطأ!</strong>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-1"></i>اسم المستخدم
                </label>
                <div class="input-group">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="أدخل اسم المستخدم"
                           value="<?php echo htmlspecialchars($username); ?>"
                           required>
                    <div class="input-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-1"></i>كلمة المرور
                </label>
                <div class="input-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="أدخل كلمة المرور"
                           required>
                    <button type="button" class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                    <div class="input-icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">
                <span>تسجيل الدخول</span>
            </button>
            
            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                <p>اتصالك آمن ومشفّر. بياناتك محمية</p>
            </div>
            
            
        </form>
        
        <div class="login-footer">
            <p>نظام إدارة محتوى متكامل © 2024</p>
            <div class="admin-badge">إصدار 1.0.0</div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = togglePassword.querySelector('i');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
        
        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
        });
        
        // Auto focus on username field
        document.getElementById('username').focus();
        
        // Add some interactive effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            // Add focus effect
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#4361ee';
                this.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1.2)';
            });
            
            // Remove focus effect
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#4361ee';
                this.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1)';
            });
            
            // Add input animation
            input.addEventListener('input', function() {
                if (this.value) {
                    this.style.borderColor = '#48bb78';
                } else {
                    this.style.borderColor = '#e2e8f0';
                }
            });
        });
        
        // Add enter key submit
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !loginBtn.disabled) {
                loginForm.submit();
            }
        });
        
        // Add some floating animation to the container
        const loginContainer = document.querySelector('.login-container');
        loginContainer.style.animation = 'float 6s ease-in-out infinite';
        
        // Create floating animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
        `;
        document.head.appendChild(style);
        
        // Auto-clear error after 5 seconds
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                errorMessage.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    errorMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>