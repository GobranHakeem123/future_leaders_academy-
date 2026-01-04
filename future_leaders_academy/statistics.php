<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// جلب الإحصائيات
$statsStmt = $pdo->query("SELECT * FROM statistics");
$stats = $statsStmt->fetch();

// جلب إحصائيات مفصلة
$detailedStats = $pdo->query("
    SELECT 
        type,
        COUNT(*) as count,
        SUM(file_size) as total_size,
        AVG(file_size) as avg_size
    FROM works 
    GROUP BY type 
    ORDER BY count DESC
")->fetchAll();

// جلب إحصائيات الفئات
$categoryStats = $pdo->query("
    SELECT 
        category,
        COUNT(*) as count,
        SUM(file_size) as total_size
    FROM works 
    GROUP BY category 
    ORDER BY count DESC
    LIMIT 10
")->fetchAll();

// جلب أحدث الملفات
$recentFiles = $pdo->query("
    SELECT * FROM works 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإحصائيات - لوحة التحكم</title>
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
            --gradient-success: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            --gradient-warning: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            --gradient-info: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
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
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
        }
        
        .mobile-menu-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(3px);
            transition: all 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            transform: translateX(0);
        }
        
        /* إخفاء القائمة الجانبية على الجوال */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
                width: 85%;
                max-width: 320px;
            }
            
            .sidebar.active {
                transform: translateX(0);
                box-shadow: 0 0 50px rgba(0, 0, 0, 0.3);
            }
            
            .mobile-menu-btn {
                display: flex;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
            
            .sidebar-overlay {
                display: none !important;
            }
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
            width: calc(100% - 280px);
        }
        
        /* تعديلات للتجاوب على الجوال */
        @media (max-width: 768px) {
            .main-content {
                margin-right: 0;
                width: 100%;
                padding: 20px;
                padding-top: 90px;
            }
            
            .header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .stat-card {
                padding: 20px;
                margin-bottom: 15px;
            }
            
            .stat-card h2 {
                font-size: 1.8rem;
            }
            
            .stat-card i {
                font-size: 2rem;
            }
            
            .chart-card {
                padding: 1.5rem;
            }
            
            .table th, .table td {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
                padding-top: 80px;
            }
            
            .header {
                padding: 15px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-card h2 {
                font-size: 1.6rem;
            }
            
            .chart-card {
                padding: 1rem;
            }
            
            .table th, .table td {
                padding: 0.6rem;
                font-size: 0.8rem;
            }
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
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            height: 100%;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            border-radius: 20px 20px 0 0;
        }
        
        .stat-card-primary::before {
            background: var(--gradient-primary);
        }
        
        .stat-card-success::before {
            background: var(--gradient-success);
        }
        
        .stat-card-info::before {
            background: var(--gradient-info);
        }
        
        .stat-card-warning::before {
            background: var(--gradient-warning);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-left: 15px;
            margin-bottom: 15px;
        }
        
        .stat-card-primary i {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card-success i {
            background: var(--gradient-success);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card-info i {
            background: var(--gradient-info);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card-warning i {
            background: var(--gradient-warning);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        /* Charts and Tables */
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        
        .chart-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .chart-card .card-header h5 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            background: white;
            margin-top: 1rem;
            -webkit-overflow-scrolling: touch;
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
        
        /* Progress Bars */
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e2e8f0;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 5px;
            background: var(--gradient-primary);
            transition: width 0.3s ease;
        }
        
        /* Badges */
        .type-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .badge-image {
            background: var(--gradient-info);
            color: white;
        }
        
        .badge-video {
            background: var(--gradient-warning);
            color: white;
        }
        
        .badge-document {
            background: var(--gradient-success);
            color: white;
        }
        
        .badge-other {
            background: var(--gradient-primary);
            color: white;
        }
        
        /* تحسينات للجوال */
        @media (max-width: 768px) {
            .header-buttons {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            .header-buttons .btn {
                width: 100%;
            }
            
            .chart-card .card-header h5 {
                font-size: 1.1rem;
            }
        }
        
        /* تحسين اللمس على الجوال */
        @media (max-width: 768px) {
            .btn, .table td, .table th {
                min-height: 44px;
            }
            
            .nav-link {
                min-height: 50px;
            }
        }
        
        /* Animation for sidebar */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(100%);
            }
        }
        
        .sidebar.active {
            animation: slideIn 0.3s ease;
        }
        
        /* تحسين عرض الجداول على الجوال */
        @media (max-width: 768px) {
            .table {
                min-width: 100%;
            }
            
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
            
            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- طبقة التعتيم للقائمة الجانبية على الجوال -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
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
    
    <!-- زر القائمة الجانبية للجوال -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    
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
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i>
                                <span>إدارة الفئات</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="statistics.php">
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
                            <h1 class="h3 mb-2" style="color: var(--dark-color);">التقارير والإحصائيات</h1>
                            <p class="text-muted mb-0">نظرة عامة على إحصائيات الملفات والنظام</p>
                        </div>
                        <div class="header-buttons d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
                            <button class="btn btn-logout d-none d-md-inline-block" onclick="showLogoutModal()">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4 g-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card stat-card-primary">
                            <i class="fas fa-folder-open"></i>
                            <h2 class="mb-2"><?php echo $stats['total_files']; ?></h2>
                            <p class="text-muted mb-0">إجمالي الملفات</p>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card stat-card-success">
                            <i class="fas fa-hdd"></i>
                            <h2 class="mb-2"><?php echo formatFileSize($stats['total_size']); ?></h2>
                            <p class="text-muted mb-0">الحجم الإجمالي</p>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo min(100, ($stats['total_size'] / (1024*1024*1024)) * 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card stat-card-info">
                            <i class="fas fa-image"></i>
                            <h2 class="mb-2"><?php echo $stats['images_count']; ?></h2>
                            <p class="text-muted mb-0">الصور</p>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo ($stats['images_count'] / max(1, $stats['total_files'])) * 100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-card stat-card-warning">
                            <i class="fas fa-video"></i>
                            <h2 class="mb-2"><?php echo $stats['videos_count']; ?></h2>
                            <p class="text-muted mb-0">الفيديو</p>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo ($stats['videos_count'] / max(1, $stats['total_files'])) * 100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detailed Statistics -->
                <div class="row g-4 mb-4">
                    <!-- Files by Type -->
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-pie me-2"></i>الملفات حسب النوع</h5>
                            </div>
                            <div class="table-responsive">
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>النوع</th>
                                                <th>العدد</th>
                                                <th>النسبة</th>
                                                <th>الحجم الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($detailedStats as $stat): 
                                                $percentage = ($stat['count'] / max(1, $stats['total_files'])) * 100;
                                                $badgeClass = getBadgeClass($stat['type']);
                                            ?>
                                                <tr>
                                                    <td>
                                                        <span class="type-badge <?php echo $badgeClass; ?>">
                                                            <i class="<?php echo getFileIcon($stat['type']); ?>"></i>
                                                            <?php echo getFileTypeName($stat['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo $stat['count']; ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                                            </div>
                                                            <span class="ms-2" style="font-size: 0.9rem;"><?php echo number_format($percentage, 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo formatFileSize($stat['total_size']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Categories -->
                    <div class="col-xl-6">
                        <div class="chart-card">
                            <div class="card-header">
                                <h5><i class="fas fa-tags me-2"></i>الفئات الأكثر نشاطاً</h5>
                            </div>
                            <div class="table-responsive">
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>الفئة</th>
                                                <th>عدد الملفات</th>
                                                <th>النسبة</th>
                                                <th>الحجم الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($categoryStats as $stat): 
                                                $percentage = ($stat['count'] / max(1, $stats['total_files'])) * 100;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo htmlspecialchars($stat['category']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo $stat['count']; ?></strong>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                                            </div>
                                                            <span class="ms-2" style="font-size: 0.9rem;"><?php echo number_format($percentage, 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                    <td><?php echo formatFileSize($stat['total_size']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Files -->
                <div class="chart-card">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>أحدث الملفات المضافة</h5>
                    </div>
                    <div class="table-responsive">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>الملف</th>
                                        <th>النوع</th>
                                        <th>الحجم</th>
                                        <th>الفئة</th>
                                        <th>تاريخ الإضافة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentFiles as $file): 
                                        $badgeClass = getBadgeClass($file['type']);
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($file['title']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="type-badge <?php echo $badgeClass; ?>">
                                                    <i class="<?php echo getFileIcon($file['type']); ?>"></i>
                                                    <?php echo getFileTypeName($file['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatFileSize($file['file_size']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($file['category']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="far fa-calendar me-1"></i>
                                                    <?php echo date('Y-m-d', strtotime($file['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view_work.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-success" title="عرض">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_work.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-warning" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
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
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : 'auto';
        }
        
        mobileMenuBtn.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Close sidebar when clicking links on mobile
        document.querySelectorAll('.sidebar .nav-link, .sidebar .btn').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !mobileMenuBtn.contains(e.target) &&
                !sidebarOverlay.contains(e.target)) {
                toggleSidebar();
            }
        });
        
        // Animate progress bars on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const width = entry.target.style.width;
                        entry.target.style.width = '0';
                        setTimeout(() => {
                            entry.target.style.width = width;
                        }, 300);
                    }
                });
            }, { threshold: 0.5 });
            
            progressBars.forEach(bar => observer.observe(bar));
        });
    </script>
</body>
</html>

<?php
// دالة مساعدة للحصول على اسم نوع الملف


// دالة مساعدة للحصول على أيقونة الملف

// دالة مساعدة لتنسيق حجم الملف


// دالة للحصول على كلاس البادج حسب نوع الملف
function getBadgeClass($type) {
    switch($type) {
        case 'images':
            return 'badge-image';
        case 'video':
            return 'badge-video';
        case 'pdf':
        case 'doc':
        case 'docx':
        case 'xls':
        case 'xlsx':
        case 'ppt':
        case 'pptx':
        case 'txt':
            return 'badge-document';
        default:
            return 'badge-other';
    }
}