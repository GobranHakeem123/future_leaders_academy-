<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// المتغيرات
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$message = '';
$message_type = '';

// معالجة الإجراءات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // إضافة فئة جديدة
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);
        
        if (empty($name)) {
            $message = 'اسم الفئة مطلوب';
            $message_type = 'error';
        } else {
            // إنشاء slug من الاسم
            $slug = createSlug($name);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $icon]);
                
                $message = 'تم إضافة الفئة بنجاح';
                $message_type = 'success';
                
                // إعادة تعيين النموذج
                $_POST = [];
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $message = 'اسم الفئة موجود مسبقاً';
                    $message_type = 'error';
                } else {
                    $message = 'حدث خطأ أثناء إضافة الفئة';
                    $message_type = 'error';
                }
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        // تعديل فئة موجودة
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $icon = trim($_POST['icon']);
        
        if (empty($name)) {
            $message = 'اسم الفئة مطلوب';
            $message_type = 'error';
        } else {
            // إنشاء slug جديد إذا تغير الاسم
            $slug = createSlug($name);
            
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $icon, $id]);
                
                $message = 'تم تعديل الفئة بنجاح';
                $message_type = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = 'اسم الفئة موجود مسبقاً';
                    $message_type = 'error';
                } else {
                    $message = 'حدث خطأ أثناء تعديل الفئة';
                    $message_type = 'error';
                }
            }
        }
    }
}

// حذف فئة
if ($action === 'delete' && !empty($id)) {
    try {
        // التحقق من وجود أعمال مرتبطة بهذه الفئة
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM works WHERE category = (SELECT name FROM categories WHERE id = ?)");
        $checkStmt->execute([$id]);
        $count = $checkStmt->fetchColumn();
        
        if ($count > 0) {
            $message = 'لا يمكن حذف الفئة لأنها تحتوي على أعمال مرتبطة';
            $message_type = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            $message = 'تم حذف الفئة بنجاح';
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = 'حدث خطأ أثناء حذف الفئة';
        $message_type = 'error';
    }
}

// جلب بيانات التعديل
$edit_data = null;
if ($action === 'edit' && !empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// جلب جميع الفئات
$stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دالة لإنشاء slug
function createSlug($string) {
    $string = preg_replace('/[^\p{L}\p{N}\s]/u', '', $string);
    $string = str_replace(' ', '-', $string);
    $string = trim($string, '-');
    return mb_strtolower($string, 'UTF-8');
}

// قائمة الأيقونات الشائعة
$common_icons = [
    'fas fa-th' => 'كل الفئات',
    'fas fa-folder-open' => 'ملفات',
    'fas fa-heartbeat' => 'خطط علاجية',
    'fas fa-desktop' => 'عروض تقديمية',
    'fas fa-graduation-cap' => 'بحوث جامعية',
    'fas fa-laptop-code' => 'حل منصات',
    'fas fa-language' => 'ترجمة',
    'fas fa-file-alt' => 'تقارير وواجبات',
    'fas fa-image' => 'صور',
    'fas fa-video' => 'فيديو',
    'fas fa-file-pdf' => 'PDF',
    'fas fa-file-word' => 'Word',
    'fas fa-file-excel' => 'Excel',
    'fas fa-file-powerpoint' => 'PowerPoint',
    'fas fa-archive' => 'أرشيف',
    'fas fa-chart-bar' => 'إحصائيات',
    'fas fa-book' => 'كتب',
    'fas fa-newspaper' => 'مقالات',
    'fas fa-project-diagram' => 'مشاريع',
    'fas fa-code' => 'برمجة',
    'fas fa-paint-brush' => 'تصميم',
    'fas fa-music' => 'موسيقى',
    'fas fa-film' => 'أفلام',
    'fas fa-camera' => 'تصوير',
    'fas fa-gamepad' => 'ألعاب',
    'fas fa-mobile-alt' => 'تطبيقات',
    'fas fa-globe' => 'ويب',
    'fas fa-shopping-cart' => 'تجارة',
    'fas fa-chart-line' => 'مالية',
    'fas fa-users' => 'مجتمع',
    'fas fa-lightbulb' => 'أفكار',
    'fas fa-trophy' => 'جوائز'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الفئات - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #ff9e00;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-danger: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .sidebar {
            background: var(--gradient-primary);
            min-height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            width: 280px;
            z-index: 1000;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar:hover {
            width: 280px;
        }
        
        .sidebar .logo {
            padding: 25px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .sidebar .logo h4 {
            background: linear-gradient(45deg, #fff, #4cc9f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 15px 25px;
            margin: 5px 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255, 255, 255, 0.1);
            transition: width 0.3s ease;
            border-radius: 12px;
        }
        
        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            width: 100%;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            transform: translateX(-5px);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .main-content {
            margin-right: 280px;
            padding: 30px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        .btn-logout {
            background: var(--gradient-danger);
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(247, 37, 133, 0.3);
            color: white;
        }
        
        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(247, 37, 133, 0.4);
            color: white;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .card-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #0f172a;
            font-size: 0.9rem;
        }
        
        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .icon-selector {
            position: relative;
        }
        
        .icon-preview {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .icon-selector .form-control {
            padding-right: 3rem;
        }
        
        .icons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-top: 0.5rem;
            display: none;
        }
        
        .icons-grid.active {
            display: grid;
        }
        
        .icon-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.8rem 0.5rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            min-height: 70px;
        }
        
        .icon-option:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .icon-option i {
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
        }
        
        .icon-option span {
            font-size: 0.65rem;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            background: white;
            margin-top: 1rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        .table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: right;
            font-weight: 600;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.9rem;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 0.9rem;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .table tr:hover {
            background: #f8fafc;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn 0.3s ease;
        }
        
        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Logout Modal */
        .logout-modal {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .logout-modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .logout-modal-content {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .logout-modal.active .logout-modal-content {
            transform: translateY(0);
        }
        
        /* User Profile in Sidebar */
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin: 20px 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            background: var(--gradient-primary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            font-size: 1.2rem;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .card-header h2 {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                transform: translateX(100%);
                z-index: 999;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
                padding: 20px;
                padding-top: 80px;
            }
            
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .header-buttons {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .header-buttons .btn {
                width: 100%;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .icons-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .icon-option {
                padding: 0.6rem 0.3rem;
                min-height: 60px;
            }
            
            .icon-option i {
                font-size: 1rem;
            }
            
            .icon-option span {
                font-size: 0.6rem;
            }
        }
        
        @media (max-width: 576px) {
            .content-grid {
                gap: 1rem;
            }
            
            .card {
                padding: 1.25rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .card-header h2 {
                font-size: 1.1rem;
            }
            
            .form-control {
                padding: 0.7rem 0.9rem;
                font-size: 0.9rem;
            }
            
            .icons-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .modal-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
                padding-top: 70px;
            }
            
            .header {
                padding: 15px;
            }
            
            .btn-logout, .btn-primary, .btn-secondary {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
            
            .user-profile {
                padding: 12px;
            }
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        /* Loading States */
        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            transform: translate(-50%, -50%);
        }
        
        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
        
        /* Animation for mobile menu */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        .sidebar.active {
            animation: slideIn 0.3s ease;
        }
        
        /* Ensure content doesn't overflow */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Better button spacing on mobile */
        .btn-group-mobile {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        /* Adjust form spacing on mobile */
        @media (max-width: 768px) {
            .form-group {
                margin-bottom: 1rem;
            }
            
            textarea.form-control {
                min-height: 80px;
            }
        }
        
        /* Mobile floating buttons */
        .mobile-floating-btns {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 100;
            display: none;
            flex-direction: column;
            gap: 10px;
        }
        
        .mobile-floating-btns .btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 768px) {
            .mobile-floating-btns {
                display: flex;
            }
        }
        
        /* Improve touch targets on mobile */
        @media (max-width: 768px) {
            .btn, .form-control, .table td, .table th {
                min-height: 44px;
            }
            
            .icon-option {
                min-height: 50px;
                min-width: 50px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Logout Modal -->
    <div class="logout-modal" id="logoutModal">
        <div class="logout-modal-content">
            <div class="mb-4">
                <i class="fas fa-sign-out-alt fa-4x" style="background: var(--gradient-danger); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
            </div>
            <h3 class="mb-3">تأكيد تسجيل الخروج</h3>
            <p class="text-muted mb-4">هل أنت متأكد أنك تريد تسجيل الخروج؟</p>
            <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-secondary" onclick="hideLogoutModal()">
                    إلغاء
                </button>
                <a href="logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                </a>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Floating Buttons -->
    <div class="mobile-floating-btns d-md-none">
        <a href="add_work.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
        </a>
        <button class="btn btn-logout" onclick="showLogoutModal()">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>
    
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <nav class="sidebar" id="sidebar">
                <div class="logo text-center">
                    <h4 class="mb-0">📁 إدارة الملفات</h4>
                    <small class="text-white opacity-75">لوحة التحكم المتكاملة</small>
                </div>
                
                <div class="sidebar-sticky pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
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
                            <a class="nav-link active" href="categories.php">
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
                    </ul>
                    
                    <!-- User Profile and Logout -->
                    <div class="mt-5 px-3">
                        <div class="user-profile" onclick="showLogoutModal()">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-info">
                                <h6 class="mb-0 text-white"><?php echo $_SESSION['admin_username'] ?? 'المسؤول'; ?></h6>
                                <small class="text-white opacity-75">مدير النظام</small>
                            </div>
                        </div>
                        
                        <!-- Logout Button in Sidebar -->
                        <div class="mt-3 px-3">
                            <button class="btn btn-logout w-100" onclick="showLogoutModal()">
                                <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Header -->
                <div class="header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h1 class="h3 mb-2" style="color: var(--dark-color);">إدارة الفئات</h1>
                            <p class="text-muted mb-0">قم بإدارة فئات المعرض الإبداعي وإضافة فئات جديدة</p>
                        </div>
                        <div class="header-buttons d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i> العودة للرئيسية
                            </a>
                            <button class="btn btn-logout d-none d-md-inline-block" onclick="showLogoutModal()">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($message): ?>
                    <div class="alert <?php echo $message_type === 'success' ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                        <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <strong><?php echo $message_type === 'success' ? 'تم بنجاح!' : 'خطأ!'; ?></strong>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="content-grid">
                    <!-- Add/Edit Category Form -->
                    <div class="card">
                        <div class="card-header">
                            <h2><?php echo $edit_data ? 'تعديل الفئة' : 'إضافة فئة جديدة'; ?></h2>
                            <?php if ($edit_data): ?>
                                <a href="categories.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-plus me-1"></i>
                                    إضافة جديد
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" id="categoryForm">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="name" class="form-label required">اسم الفئة</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($edit_data['name'] ?? ($_POST['name'] ?? '')); ?>" 
                                       placeholder="أدخل اسم الفئة" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">الوصف</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="أدخل وصفاً للفئة (اختياري)"><?php echo htmlspecialchars($edit_data['description'] ?? ($_POST['description'] ?? '')); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="icon" class="form-label">أيقونة الفئة</label>
                                <div class="icon-selector">
                                    <div class="icon-preview">
                                        <i id="selectedIconPreview" class="<?php echo $edit_data['icon'] ?? ($_POST['icon'] ?? 'fas fa-folder'); ?>"></i>
                                    </div>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="<?php echo htmlspecialchars($edit_data['icon'] ?? ($_POST['icon'] ?? 'fas fa-folder')); ?>" 
                                           placeholder="اختر أيقونة" readonly>
                                </div>
                                
                                <div class="icons-grid" id="iconsGrid">
                                    <?php foreach ($common_icons as $icon_class => $icon_name): ?>
                                        <div class="icon-option" data-icon="<?php echo $icon_class; ?>">
                                            <i class="<?php echo $icon_class; ?>"></i>
                                            <span><?php echo $icon_name; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="toggleIconsBtn">
                                    <i class="fas fa-icons me-1"></i>
                                    اختر أيقونة
                                </button>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary w-100" name="<?php echo $edit_data ? 'edit_category' : 'add_category'; ?>">
                                    <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus'; ?> me-1"></i>
                                    <?php echo $edit_data ? 'تعديل الفئة' : 'إضافة الفئة'; ?>
                                </button>
                                
                                <?php if ($edit_data): ?>
                                    <a href="categories.php" class="btn btn-secondary w-100 mt-2">
                                        <i class="fas fa-times me-1"></i>
                                        إلغاء
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Categories List -->
                    <div class="card">
                        <div class="card-header">
                            <h2>قائمة الفئات</h2>
                            <small><?php echo count($categories); ?> فئة</small>
                        </div>
                        
                        <div class="table-responsive">
                            <div class="table-container">
                                <?php if (empty($categories)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                                        <h3>لا توجد فئات</h3>
                                        <p>ابدأ بإضافة فئة جديدة</p>
                                    </div>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>الأيقونة</th>
                                                <th>اسم الفئة</th>
                                                <th>الوصف</th>
                                                <th>تاريخ الإضافة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td>
                                                        <i class="<?php echo htmlspecialchars($category['icon']); ?> category-icon"></i>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                        <div style="font-size: 0.8rem; color: #94a3b8;">
                                                            <?php echo htmlspecialchars($category['slug']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($category['description']): ?>
                                                            <?php echo htmlspecialchars(mb_substr($category['description'], 0, 30)); ?>
                                                            <?php if (mb_strlen($category['description']) > 30): ?>...<?php endif; ?>
                                                        <?php else: ?>
                                                            <span style="color: #94a3b8; font-style: italic;">لا يوجد وصف</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo date('Y/m/d', strtotime($category['created_at'])); ?>
                                                    </td>
                                                    <td>
                                                        <div class="actions d-flex justify-content-end gap-1">
                                                            <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" 
                                                               class="btn btn-success btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-danger btn-sm delete-btn" 
                                                                    data-id="<?php echo $category['id']; ?>"
                                                                    data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تأكيد الحذف</h3>
                <button class="btn-close" id="closeModal"></button>
            </div>
            <div style="padding: 1.5rem 0;">
                <p style="margin-bottom: 1.5rem;">هل أنت متأكد من حذف الفئة "<span id="deleteCategoryName"></span>"؟</p>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button class="btn btn-secondary" id="cancelDelete">إلغاء</button>
                    <a href="#" class="btn btn-danger" id="confirmDelete">حذف</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // وظائف تسجيل الخروج
        function showLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function hideLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // إغلاق النافذة عند النقر خارجها
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideLogoutModal();
            }
        });
        
        // إغلاق النافذة بمفتاح Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideLogoutModal();
            }
        });
        
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !mobileMenuBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
        
        // Icon Selector
        const iconInput = document.getElementById('icon');
        const selectedIconPreview = document.getElementById('selectedIconPreview');
        const iconsGrid = document.getElementById('iconsGrid');
        const toggleIconsBtn = document.getElementById('toggleIconsBtn');
        
        toggleIconsBtn.addEventListener('click', () => {
            iconsGrid.classList.toggle('active');
        });
        
        // Select icon from grid
        document.querySelectorAll('.icon-option').forEach(option => {
            option.addEventListener('click', () => {
                const iconClass = option.getAttribute('data-icon');
                iconInput.value = iconClass;
                selectedIconPreview.className = iconClass;
                iconsGrid.classList.remove('active');
            });
        });
        
        // Close icon grid when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.icon-selector') && !e.target.closest('#toggleIconsBtn')) {
                iconsGrid.classList.remove('active');
            }
        });
        
        // Delete Confirmation Modal
        const deleteModal = document.getElementById('deleteModal');
        const closeModal = document.getElementById('closeModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const deleteCategoryName = document.getElementById('deleteCategoryName');
        const confirmDelete = document.getElementById('confirmDelete');
        
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const categoryId = btn.getAttribute('data-id');
                const categoryName = btn.getAttribute('data-name');
                
                deleteCategoryName.textContent = categoryName;
                confirmDelete.href = `categories.php?action=delete&id=${categoryId}`;
                deleteModal.classList.add('active');
            });
        });
        
        // Close modal
        closeModal.addEventListener('click', () => {
            deleteModal.classList.remove('active');
        });
        
        cancelDelete.addEventListener('click', () => {
            deleteModal.classList.remove('active');
        });
        
        // Close modal when clicking outside
        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });
        
        // Form Validation
        const form = document.getElementById('categoryForm');
        const nameInput = document.getElementById('name');
        
        form.addEventListener('submit', (e) => {
            if (!nameInput.value.trim()) {
                e.preventDefault();
                nameInput.style.borderColor = '#f56565';
                nameInput.style.background = '#fff5f5';
                nameInput.focus();
                
                // Show error message
                if (!document.querySelector('.form-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger alert-dismissible fade show form-error';
                    errorDiv.innerHTML = `
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>خطأ!</strong> يرجى إدخال اسم الفئة
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    form.insertBefore(errorDiv, form.firstChild);
                }
            }
        });
        
        // Clear error when typing
        nameInput.addEventListener('input', () => {
            nameInput.style.borderColor = '#e2e8f0';
            nameInput.style.background = 'white';
        });
        
        // Auto-generate slug preview
        nameInput.addEventListener('blur', () => {
            if (nameInput.value.trim()) {
                const slug = nameInput.value
                    .replace(/[^\u0621-\u064A\s]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .toLowerCase();
                
                // Show slug preview
                if (!document.querySelector('.slug-preview')) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'slug-preview';
                    previewDiv.style.marginTop = '0.5rem';
                    previewDiv.style.fontSize = '0.8rem';
                    previewDiv.style.color = '#94a3b8';
                    previewDiv.innerHTML = `<strong>Slug:</strong> ${slug}`;
                    nameInput.parentNode.appendChild(previewDiv);
                } else {
                    document.querySelector('.slug-preview').innerHTML = `<strong>Slug:</strong> ${slug}`;
                }
            }
        });
        
        // Adjust table for mobile
        function adjustTableForMobile() {
            const table = document.querySelector('.table');
            if (window.innerWidth <= 768) {
                table.classList.add('table-sm');
            } else {
                table.classList.remove('table-sm');
            }
        }
        
        // Call on load and resize
        window.addEventListener('load', adjustTableForMobile);
        window.addEventListener('resize', adjustTableForMobile);
        
        // Smooth scroll for table on mobile
        document.querySelectorAll('.table-container').forEach(container => {
            container.addEventListener('touchstart', function(e) {
                this.style.overflowX = 'auto';
            }, { passive: true });
        });
    </script>
</body>
</html>