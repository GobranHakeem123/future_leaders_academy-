<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// بيانات الاتصال بقاعدة البيانات future_leaders_academy
$db_host = 'localhost';
$db_name = 'future_leaders_academy';
$db_user = 'root'; // عدل حسب إعداداتك
$db_pass = ''; // عدل حسب إعداداتك

try {
    $future_leaders_pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection to future_leaders_academy failed: " . $e->getMessage());
}

// جلب بيانات المستخدم الحالي
$admin_id = $_SESSION['admin_id'];
$error = '';
$success = '';

// معالجة طلب تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'جميع حقول كلمة المرور مطلوبة';
    } elseif ($new_password !== $confirm_password) {
        $error = 'كلمة المرور الجديدة غير متطابقة';
    } elseif (strlen($new_password) < 6) {
        $error = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل';
    } else {
        try {
            // الحصول على المستخدم من قاعدة البيانات
            $stmt = $future_leaders_pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'المستخدم غير موجود';
            } else {
                // التحقق من كلمة المرور الحالية
                if (password_verify($current_password, $user['password_hash'])) {
                    // تحديث كلمة المرور فقط (بدون updated_at)
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // محاولة التحديث بدون updated_at
                    try {
                        $updateStmt = $future_leaders_pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $updateStmt->execute([$new_password_hash, $admin_id]);
                        $success = 'تم تغيير كلمة المرور بنجاح في قاعدة بيانات future_leaders_academy';
                    } catch (Exception $updateError) {
                        // إذا فشل، قد يكون هناك حقل آخر يحتاج تحديث
                        $updateStmt = $future_leaders_pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $updateStmt->execute([$new_password_hash, $admin_id]);
                        $success = 'تم تغيير كلمة المرور بنجاح في قاعدة بيانات future_leaders_academy';
                    }
                } else {
                    $error = 'كلمة المرور الحالية غير صحيحة';
                }
            }
        } catch (Exception $e) {
            $error = 'حدث خطأ في تغيير كلمة المرور: ' . $e->getMessage();
            error_log("Change password error: " . $e->getMessage());
        }
    }
}

// جلب معلومات المستخدم لعرضها
try {
    $stmt = $future_leaders_pdo->prepare("SELECT id, username, full_name, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $user_info = $stmt->fetch();
} catch (Exception $e) {
    $user_info = null;
    error_log("Get user info error: " . $e->getMessage());
}

// دالة لإضافة عمود updated_at إذا لم يكن موجوداً
function addUpdatedAtColumn($pdo) {
    try {
        // التحقق مما إذا كان العمود موجوداً
        $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'updated_at'");
        if ($checkColumn->rowCount() == 0) {
            // إضافة العمود
            $pdo->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            error_log("Column updated_at added successfully");
            return true;
        }
    } catch (Exception $e) {
        error_log("Error adding updated_at column: " . $e->getMessage());
        return false;
    }
    return false;
}

// محاولة إضافة العمود إذا لم يكن موجوداً (اختياري)
addUpdatedAtColumn($future_leaders_pdo);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير كلمة المرور - future_leaders_academy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* نفس التنسيقات السابقة */
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="change-password-container">
        <div class="header">
            <h2>تغيير كلمة المرور</h2>
            <p>تحديث كلمة المرور في قاعدة بيانات Future Leaders Academy</p>
        </div>
        
        <!-- معلومات قاعدة البيانات -->
        <div class="database-info">
            <h5><i class="fas fa-database me-2"></i>قاعدة البيانات: future_leaders_academy</h5>
            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($user_info['username'] ?? 'غير محدد'); ?></p>
            <p><strong>الاسم الكامل:</strong> <?php echo htmlspecialchars($user_info['full_name'] ?? 'غير محدد'); ?></p>
            <p><strong>الدور:</strong> <?php echo htmlspecialchars($user_info['role'] ?? 'غير محدد'); ?></p>
            <p><strong>تاريخ التسجيل:</strong> <?php echo htmlspecialchars($user_info['created_at'] ?? 'غير محدد'); ?></p>
            <div class="database-status status-connected">
                <i class="fas fa-check-circle me-1"></i>متصل بقاعدة البيانات
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="passwordForm">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock me-1"></i>كلمة المرور الحالية
                </label>
                <div class="input-group">
                    <input type="password" 
                           name="current_password" 
                           id="currentPassword" 
                           class="form-control" 
                           placeholder="أدخل كلمة المرور الحالية"
                           required>
                    <button type="button" class="toggle-password" data-target="currentPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key me-1"></i>كلمة المرور الجديدة
                </label>
                <div class="input-group">
                    <input type="password" 
                           name="new_password" 
                           id="newPassword" 
                           class="form-control" 
                           placeholder="أدخل كلمة المرور الجديدة"
                           required>
                    <button type="button" class="toggle-password" data-target="newPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key me-1"></i>تأكيد كلمة المرور الجديدة
                </label>
                <div class="input-group">
                    <input type="password" 
                           name="confirm_password" 
                           id="confirmPassword" 
                           class="form-control" 
                           placeholder="أعد إدخال كلمة المرور الجديدة"
                           required>
                    <button type="button" class="toggle-password" data-target="confirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-text" id="passwordMatch"></div>
            </div>
            
            <!-- متطلبات كلمة المرور -->
            <div class="password-requirements">
                <h6><i class="fas fa-shield-alt me-1"></i>متطلبات كلمة المرور:</h6>
                <ul>
                    <li id="req-length"><i class="fas fa-circle me-1"></i> 6 أحرف على الأقل</li>
                    <li id="req-uppercase"><i class="fas fa-circle me-1"></i> تحتوي على حرف كبير</li>
                    <li id="req-lowercase"><i class="fas fa-circle me-1"></i> تحتوي على حرف صغير</li>
                    <li id="req-number"><i class="fas fa-circle me-1"></i> تحتوي على رقم</li>
                </ul>
            </div>
            
            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="submitText">تغيير كلمة المرور</span>
            </button>
            
            <div class="back-link">
                <a href="profile.php">
                    <i class="fas fa-arrow-right me-1"></i>العودة إلى الملف الشخصي
                </a>
                <span class="mx-2">|</span>
                <a href="check_table_structure.php" target="_blank">
                    <i class="fas fa-table me-1"></i>فحص هيكل الجدول
                </a>
            </div>
        </form>
    </div>
    
    <script>
        // إظهار/إخفاء كلمة المرور
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });
        
        // التحقق من قوة كلمة المرور
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordMatchText = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        
        // متطلبات كلمة المرور
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // طول كلمة المرور
            if (password.length >= 6) {
                strength += 1;
                reqLength.className = 'valid';
                reqLength.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> 6 أحرف على الأقل';
            } else {
                reqLength.className = '';
                reqLength.innerHTML = '<i class="fas fa-circle me-1"></i> 6 أحرف على الأقل';
            }
            
            // أحرف كبيرة
            if (/[A-Z]/.test(password)) {
                strength += 1;
                reqUppercase.className = 'valid';
                reqUppercase.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> تحتوي على حرف كبير';
            } else {
                reqUppercase.className = '';
                reqUppercase.innerHTML = '<i class="fas fa-circle me-1"></i> تحتوي على حرف كبير';
            }
            
            // أحرف صغيرة
            if (/[a-z]/.test(password)) {
                strength += 1;
                reqLowercase.className = 'valid';
                reqLowercase.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> تحتوي على حرف صغير';
            } else {
                reqLowercase.className = '';
                reqLowercase.innerHTML = '<i class="fas fa-circle me-1"></i> تحتوي على حرف صغير';
            }
            
            // أرقام
            if (/[0-9]/.test(password)) {
                strength += 1;
                reqNumber.className = 'valid';
                reqNumber.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> تحتوي على رقم';
            } else {
                reqNumber.className = '';
                reqNumber.innerHTML = '<i class="fas fa-circle me-1"></i> تحتوي على رقم';
            }
            
            // تحديث شريط القوة
            passwordStrengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                passwordStrengthBar.style.width = '0';
            } else if (strength <= 1) {
                passwordStrengthBar.className += ' strength-weak';
            } else if (strength === 2) {
                passwordStrengthBar.className += ' strength-medium';
            } else if (strength === 3) {
                passwordStrengthBar.className += ' strength-strong';
            } else {
                passwordStrengthBar.className += ' strength-very-strong';
            }
            
            return strength;
        }
        
        function checkPasswordMatch() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatchText.textContent = '';
                passwordMatchText.className = 'form-text';
            } else if (password === confirmPassword) {
                passwordMatchText.textContent = 'كلمة المرور متطابقة ✓';
                passwordMatchText.className = 'form-text text-success';
            } else {
                passwordMatchText.textContent = 'كلمة المرور غير متطابقة ✗';
                passwordMatchText.className = 'form-text text-danger';
            }
            
            // تفعيل/تعطيل زر الحفظ
            const strength = checkPasswordStrength(password);
            const isValid = (password === confirmPassword) && password.length > 0 && strength >= 3;
            submitBtn.disabled = !isValid;
        }
        
        newPasswordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // إضافة مؤشر تحميل عند الإرسال
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('كلمة المرور الجديدة غير متطابقة');
                return;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل');
                return;
            }
            
            // إضافة مؤشر تحميل
            submitText.innerHTML = 'جاري التغيير...';
            submitBtn.innerHTML = '<span class="loading-spinner"></span>';
            submitBtn.disabled = true;
        });
        
        // التحقق من صحة النموذج عند التحميل
        checkPasswordMatch();
        
        // التركيز على حقل كلمة المرور الحالية
        document.getElementById('currentPassword').focus();
        
        // إضافة رسالة تأكيد قبل الإرسال
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', function(e) {
            if (!confirm('هل أنت متأكد من تغيير كلمة المرور؟ سيتم تسجيل خروجك من جميع الجلسات.')) {
                e.preventDefault();
                submitText.innerHTML = 'تغيير كلمة المرور';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span id="submitText">تغيير كلمة المرور</span>';
            }
        });
    </script>
</body>
</html>