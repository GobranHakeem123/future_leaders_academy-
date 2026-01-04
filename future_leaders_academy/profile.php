<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// جلب بيانات المستخدم من قاعدة البيانات
$user_id = $_SESSION['admin_id'];
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = 'المستخدم غير موجود';
    }
} catch (Exception $e) {
    $error = 'حدث خطأ في جلب بيانات المستخدم';
    error_log("Profile error: " . $e->getMessage());
}

// معالجة طلب تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // تحديث البيانات الشخصية
        $full_name = $_POST['full_name'] ?? '';
        
        if (empty($full_name)) {
            $error = 'الاسم الكامل مطلوب';
        } else {
            try {
                $updateStmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $updateStmt->execute([$full_name, $user_id]);
                
                // تحديث الجلسة
                $_SESSION['full_name'] = $full_name;
                
                $success = 'تم تحديث البيانات الشخصية بنجاح';
                
                // تحديث بيانات المستخدم
                $user['full_name'] = $full_name;
            } catch (Exception $e) {
                $error = 'حدث خطأ في تحديث البيانات';
                error_log("Update profile error: " . $e->getMessage());
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        // تغيير كلمة المرور
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
                // التحقق من كلمة المرور الحالية
                if (password_verify($current_password, $user['password_hash'])) {
                    // تحديث كلمة المرور
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $updateStmt->execute([$new_password_hash, $user_id]);
                    
                    $success = 'تم تغيير كلمة المرور بنجاح';
                    
                    // إرسال بريد إلكتروني إشاري (اختياري)
                    // sendPasswordChangeEmail($user['email'], $user['full_name']);
                } else {
                    $error = 'كلمة المرور الحالية غير صحيحة';
                }
            } catch (Exception $e) {
                $error = 'حدث خطأ في تغيير كلمة المرور';
                error_log("Change password error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - لوحة التحكم</title>
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
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .sidebar {
            background: var(--gradient-primary);
            min-height: 100vh;
            color: white;
            position: fixed;
            right: 0;
            top: 0;
            width: 260px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .main-content {
            margin-right: 260px;
            padding: 30px;
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .main-content {
                margin-right: 0;
            }
        }
        
        .nav-logo {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-logo h3 {
            margin: 0;
            font-weight: 700;
            color: white;
        }
        
        .nav-logo p {
            margin: 5px 0 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .nav-menu {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-right: 3px solid white;
        }
        
        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .user-info {
            padding: 20px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
        }
        
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-right: 5px solid var(--primary-color);
        }
        
        .page-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .page-header .breadcrumb {
            margin-bottom: 0;
            background: none;
            padding: 0;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-header {
            background: var(--gradient-primary);
            padding: 30px;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: var(--primary-color);
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .info-group {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .info-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
            display: block;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #555;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 10px;
        }
        
        .form-control {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-right: 4px solid #28a745;
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-right: 4px solid #dc3545;
            color: #721c24;
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border-right: 3px solid var(--primary-color);
        }
        
        .password-requirements h6 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .password-requirements ul {
            margin-bottom: 0;
            padding-right: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .password-requirements li.valid {
            color: #28a745;
        }
        
        .password-requirements li i {
            margin-left: 5px;
        }
        
        .password-strength {
            margin-top: 10px;
            height: 5px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s;
            border-radius: 5px;
        }
        
        .strength-weak {
            background: #dc3545;
            width: 25%;
        }
        
        .strength-medium {
            background: #ffc107;
            width: 50%;
        }
        
        .strength-strong {
            background: #28a745;
            width: 75%;
        }
        
        .strength-very-strong {
            background: #20c997;
            width: 100%;
        }
        
        .last-login {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .stats-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- الشريط الجانبي -->
     <nav class="sidebar" id="sidebar">
                <div class="logo text-center">
                    <h4 class="mb-0">📁 إدارة الملفات</h4>
                    <small class="text-white opacity-75">لوحة التحكم المتكاملة</small>
                </div>
                
                <div class="sidebar-sticky pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-home"></i>
                                <span>الصفحة الرئيسية</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_work.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>إضافة ملف جديد</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i>
                                <span>إدارة الفئات</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="statistics.php">
                                <i class="fas fa-chart-bar"></i>
                                <span>التقارير والإحصائيات</span>
                            </a>
                        </li>
                        <div class="nav-item">
                <a href="profile.php" class="nav-link active">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
            </div>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="#" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-cog"></i>
                                <span>الإعدادات</span>
                            </a>
                        </li>
                    </ul>
                    
                    <div class="sidebar-footer mt-5 px-3">
                        <div class="user-info d-flex align-items-center">
                            <div class="avatar me-3">
                                <i class="fas fa-user-circle fa-2x text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-white">المسؤول</h6>
                                <small class="text-white opacity-75">مدير النظام</small>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <!-- رأس الصفحة -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user-circle me-2"></i>الملف الشخصي</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                            <li class="breadcrumb-item active" aria-current="page">الملف الشخصي</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3">آخر دخول: <?php echo date('Y-m-d H:i', strtotime($user['last_login'] ?? 'now')); ?></span>
                    <div class="badge bg-primary p-2">
                        <i class="fas fa-user-shield me-1"></i>
                        <?php echo htmlspecialchars($user['role'] ?? 'مستخدم'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- الرسائل -->
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
        
        <div class="row">
            <!-- معلومات الحساب -->
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['admin_username']); ?></h3>
                        <p>عضو منذ <?php echo date('Y-m-d', strtotime($user['created_at'] ?? 'now')); ?></p>
                    </div>
                    
                    <div class="profile-body">
                        <h4 class="mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>معلومات الحساب</h4>
                        
                        <!-- معلومات المستخدم -->
                        <div class="info-group">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">اسم المستخدم</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                                            <small class="text-muted">لا يمكن تغيير اسم المستخدم</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">الاسم الكامل</label>
                                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">الدور</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">تاريخ التسجيل</label>
                                            <input type="text" class="form-control" value="<?php echo date('Y-m-d H:i', strtotime($user['created_at'] ?? '')); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>حفظ التغييرات
                                </button>
                            </form>
                        </div>
                        
                        <!-- تغيير كلمة المرور -->
                        <div class="info-group">
                            <h5 class="mb-4"><i class="fas fa-key me-2 text-primary"></i>تغيير كلمة المرور</h5>
                            
                            <form method="POST" action="" id="passwordForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">كلمة المرور الحالية</label>
                                            <div class="input-group">
                                                <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="currentPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">كلمة المرور الجديدة</label>
                                            <div class="input-group">
                                                <input type="password" name="new_password" id="newPassword" class="form-control" required>
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength mt-2">
                                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                                            <div class="input-group">
                                                <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text" id="passwordMatch"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- متطلبات كلمة المرور -->
                                <div class="password-requirements">
                                    <h6><i class="fas fa-shield-alt me-1"></i>متطلبات كلمة المرور</h6>
                                    <ul>
                                        <li id="req-length"><i class="fas fa-circle"></i> 6 أحرف على الأقل</li>
                                        <li id="req-uppercase"><i class="fas fa-circle"></i> تحتوي على حرف كبير</li>
                                        <li id="req-lowercase"><i class="fas fa-circle"></i> تحتوي على حرف صغير</li>
                                        <li id="req-number"><i class="fas fa-circle"></i> تحتوي على رقم</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary" id="submitPasswordBtn">
                                    <i class="fas fa-key me-1"></i>تغيير كلمة المرور
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- إحصائيات ونشاط -->
            <div class="col-lg-4">
                <!-- بطاقة الإحصائيات -->
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stats-number">
                        <?php 
                            // احسب عدد الأيام منذ التسجيل
                            $created = new DateTime($user['created_at'] ?? date('Y-m-d H:i:s'));
                            $now = new DateTime();
                            $interval = $now->diff($created);
                            echo $interval->days;
                        ?>
                    </div>
                    <div class="stats-label">يوم في النظام</div>
                </div>
                
                <!-- حالة الحساب -->
                <div class="profile-card mb-4">
                    <div class="profile-body">
                        <h5 class="mb-3"><i class="fas fa-user-check me-2 text-success"></i>حالة الحساب</h5>
                        <div class="info-group">
                            <div class="d-flex justify-content-between mb-2">
                                <span>حالة الحساب:</span>
                                <span class="badge bg-success">نشط</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>آخر نشاط:</span>
                                <span><?php echo date('H:i', strtotime($user['last_login'] ?? 'now')); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>آخر تحديث:</span>
                                <span><?php echo date('Y-m-d', strtotime($user['updated_at'] ?? $user['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="logout.php" class="btn btn-outline w-100">
                                <i class="fas fa-sign-out-alt me-1"></i>تسجيل الخروج من جميع الجلسات
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- نصائح أمنية -->
                <div class="profile-card">
                    <div class="profile-body">
                        <h5 class="mb-3"><i class="fas fa-shield-alt me-2 text-warning"></i>نصائح أمنية</h5>
                        <div class="info-group">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>استخدم كلمة مرور قوية</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>غير كلمة المرور دورياً</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>لا تشارك بيانات الدخول</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>تسجيل الخروج من الأجهزة العامة</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>راجع نشاط حسابك بانتظام</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        const submitPasswordBtn = document.getElementById('submitPasswordBtn');
        
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
                passwordMatchText.textContent = 'كلمة المرور متطابقة';
                passwordMatchText.className = 'form-text text-success';
            } else {
                passwordMatchText.textContent = 'كلمة المرور غير متطابقة';
                passwordMatchText.className = 'form-text text-danger';
            }
            
            // تفعيل/تعطيل زر الحفظ
            const strength = checkPasswordStrength(password);
            const isValid = (password === confirmPassword) && password.length > 0 && strength >= 3;
            submitPasswordBtn.disabled = !isValid;
        }
        
        newPasswordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        // التحقق من النموذج قبل الإرسال
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
            submitPasswordBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري التغيير...';
            submitPasswordBtn.disabled = true;
        });
        
        // تحديث وقت آخر دخول تلقائياً
        function updateLastLoginTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ar-SA', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            document.querySelector('.last-login').textContent = `آخر نشاط: ${timeString}`;
        }
        
        // تحديث كل 5 دقائق
        setInterval(updateLastLoginTime, 300000);
        
        // التأكيد قبل تغيير كلمة المرور
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', function(e) {
            if (!confirm('هل أنت متأكد من تغيير كلمة المرور؟')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>