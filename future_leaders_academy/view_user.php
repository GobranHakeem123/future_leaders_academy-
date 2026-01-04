<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;

if ($user_id <= 0) {
    header('Location: get_users.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            (SELECT COUNT(*) FROM login_history WHERE user_id = u.id) as login_count,
            (SELECT MAX(login_time) FROM login_history WHERE user_id = u.id) as last_login_history
        FROM users u 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: get_users.php');
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض المستخدم - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* نفس التنسيقات السابقة مع إضافة */
        .profile-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            padding: 40px;
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .user-avatar-lg {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #4361ee;
            margin: 0 auto;
            border: 5px solid rgba(255, 255, 255, 0.3);
        }
        
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-right: 4px solid #4361ee;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user me-2"></i>عرض المستخدم</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="get_users.php">المستخدمين</a></li>
                            <li class="breadcrumb-item active">عرض المستخدم</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="get_users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>رجوع
                    </a>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="profile-header text-center">
                <div class="user-avatar-lg mb-3">
                    <i class="fas fa-user"></i>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h2>
                <p class="mb-0">@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-info-circle me-2 text-primary"></i>المعلومات الأساسية</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-label">اسم المستخدم</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">الاسم الكامل</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['full_name'] ?: 'غير محدد'); ?></div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-label">البريد الإلكتروني</div>
                                    <div class="info-value">
                                        <?php if ($user['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </a>
                                        <?php else: ?>
                                        غير محدد
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">رقم الهاتف</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'غير محدد'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-user-tag me-2 text-primary"></i>معلومات الحساب</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-label">الدور</div>
                                    <div class="info-value">
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($user['role'] ?: 'user'); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">الحالة</div>
                                    <div class="info-value">
                                        <?php if ($user['status'] == 1): ?>
                                        <span class="badge bg-success">نشط</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">غير نشط</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-label">تاريخ التسجيل</div>
                                    <div class="info-value">
                                        <?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">آخر تحديث</div>
                                    <div class="info-value">
                                        <?php echo $user['updated_at'] ? date('Y-m-d H:i', strtotime($user['updated_at'])) : 'لم يتم التحديث'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-sign-in-alt me-2 text-primary"></i>نشاط الدخول</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="info-label">آخر دخول</div>
                                    <div class="info-value">
                                        <?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'لم يسجل دخول'; ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">عدد مرات الدخول</div>
                                    <div class="info-value"><?php echo $user['login_count'] ?? 0; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-cogs me-2 text-primary"></i>الإجراءات</h5>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i>تعديل المستخدم
                                </a>
                                <a href="change_user_password.php?id=<?php echo $user['id']; ?>" class="btn btn-info">
                                    <i class="fas fa-key me-1"></i>تغيير كلمة المرور
                                </a>
                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                <button class="btn btn-danger delete-user" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['username']); ?>">
                                    <i class="fas fa-trash me-1"></i>حذف المستخدم
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // حذف المستخدم
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                
                if (confirm(`هل أنت متأكد من حذف المستخدم "${userName}"؟`)) {
                    fetch(`delete_user.php?id=${userId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({id: userId})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('تم حذف المستخدم بنجاح');
                            window.location.href = 'get_users.php';
                        } else {
                            alert('حدث خطأ: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال');
                    });
                }
            });
        });
    </script>
</body>
</html>