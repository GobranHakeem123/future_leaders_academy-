<?php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$type = $_GET['type'] ?? '';
$featured = $_GET['featured'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// بناء استعلام البحث
$sql = "SELECT * FROM works WHERE 1=1";
$params = [];
$paramTypes = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $paramTypes = array_merge($paramTypes, ['s', 's', 's']);
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $paramTypes[] = 's';
}

if (!empty($type)) {
    $sql .= " AND type = ?";
    $params[] = $type;
    $paramTypes[] = 's';
}

if ($featured !== '') {
    $sql .= " AND featured = ?";
    $params[] = $featured;
    $paramTypes[] = 'i';
}

// تحديد الترتيب
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'size_asc':
        $sql .= " ORDER BY file_size ASC";
        break;
    case 'size_desc':
        $sql .= " ORDER BY file_size DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

// جلب الأعمال
$stmt = $pdo->prepare($sql);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$works = $stmt->fetchAll();

// جلب الفئات
$categoriesStmt = $pdo->query("SELECT name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// جلب أنواع الملفات المميزة
$typesStmt = $pdo->query("SELECT DISTINCT type FROM works WHERE type IS NOT NULL ORDER BY type");
$availableTypes = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

// جلب الإحصائيات
$statsStmt = $pdo->query("SELECT * FROM statistics");
$stats = $statsStmt->fetch();

// جلب أنواع الملفات المسموح بها
$fileTypesStmt = $pdo->query("SELECT DISTINCT type FROM allowed_file_types ORDER BY type");
$fileTypes = $fileTypesStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - إدارة الملفات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --success-color: #4cc9f0;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --gradient-secondary: linear-gradient(135deg, #7209b7 0%, #3a0ca3 100%);
            --gradient-success: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .glass-morphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: var(--gradient-primary);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 10px;
            font-size: 1.5rem;
            cursor: pointer;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .mobile-menu-btn:hover {
            transform: scale(1.05);
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
            transition: transform 0.3s ease;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(3px);
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
            text-decoration: none;
            display: flex;
            align-items: center;
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
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
          
        .stat-card i {
            font-size: 2.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-left: 15px;
        }
        
        .btn-logout {
            background-color: rgba(238, 4, 109, 0.3);
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(250, 0, 112, 0.3);
            opacity: 1;
            color: white;
        }
        
        .file-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        .file-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .file-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .file-card:hover::after {
            transform: scaleX(1);
        }
        
        .file-preview-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .file-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .file-card:hover .file-preview-container img {
            transform: scale(1.1);
        }
        
        .badge-file-type {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
            padding: 8px 15px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.8rem;
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 2;
            padding: 8px 15px;
            border-radius: 10px;
            background: linear-gradient(45deg, #ffd700, #ff9500);
            color: #000;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255, 149, 0, 0.3);
        }
        
        .file-actions .btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
            transition: all 0.3s ease;
            border: none;
        }
        
        .file-actions .btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .search-form .form-control {
            border-radius: 15px;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .search-form .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .search-form select {
            cursor: pointer;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            padding: 12px 30px;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .empty-state i {
            font-size: 4rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        
        .file-info h6 {
            color: var(--dark-color);
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        
        .file-meta {
            font-size: 0.9rem;
            color: #666;
        }
        
        .file-meta i {
            color: var(--primary-color);
            margin-left: 5px;
        }
        
        .category-badge {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        /* Animations */
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
        
        .file-card {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
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
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 10px;
        }
        
        @keyframes loading {
            0% {
                background-position: 200% 0;
            }
            100% {
                background-position: -200% 0;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex;
            }
            
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-right: 0;
                width: 100%;
                padding: 20px;
                padding-top: 5rem;
            }
            
            .header {
                padding: 15px;
                margin-top: 3rem;
            }
            
            .header .btn-logout {
                display: none;
            }
            
            .stat-card {
                margin-bottom: 20px;
            }
            
            .search-form .row {
                margin-bottom: 15px;
            }
            
            .search-form .col-md-12,
            .search-form .col-md-6,
            .search-form .col-lg-2,
            .search-form .col-lg-4 {
                margin-bottom: 10px;
            }
            
            .file-card {
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
                padding-top: 5rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .header p {
                font-size: 0.9rem;
            }
            
            .stat-card h2 {
                font-size: 1.5rem;
            }
            
            .stat-card p {
                font-size: 0.8rem;
            }
            
            .stat-card i {
                font-size: 2rem;
            }
            
            .file-preview-container {
                height: 150px;
            }
            
            .file-info h6 {
                font-size: 0.9rem;
            }
            
            .file-meta {
                font-size: 0.8rem;
            }
            
            .file-actions .btn {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
        }
        
        /* User Profile Styles */
        .user-profile {
            margin-top: auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-avatar i {
            font-size: 1.5rem;
            color: white;
        }
        
        .user-info h4 {
            margin: 0;
            font-size: 1rem;
            color: white;
        }
        
        .user-info p {
            margin: 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Overlay for mobile menu -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Sidebar -->
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

            <!-- Main Content -->
            <main class="main-content">
                <!-- Header -->
                <div class="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-2" style="color: var(--dark-color);">مرحبًا بك في لوحة التحكم</h1>
                            <p class="text-muted mb-0">إدارة وعرض جميع الملفات بسهولة واحترافية</p>
                        </div>
                         <button class="btn btn-logout d-none d-md-inline-block" onclick="showLogoutModal()">
                                <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                            </button>
                        <a href="add_work.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> إضافة ملف جديد
                        </a>
                        <div class="nav-item">
                <a href="profile.php" class="nav-link active">
                    <i class="fas fa-user"></i>
                    <span>الملف الشخصي</span>
                </a>
            </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-4 g-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0" style="color: var(--dark-color);"><?php echo $stats['total_files']; ?></h2>
                                    <p class="text-muted mb-0">إجمالي الملفات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0" style="color: var(--dark-color);"><?php echo $stats['images_count']; ?></h2>
                                    <p class="text-muted mb-0">الملفات المصورة</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-video"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0" style="color: var(--dark-color);"><?php echo $stats['videos_count']; ?></h2>
                                    <p class="text-muted mb-0">ملفات الفيديو</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0" style="color: var(--dark-color);"><?php echo $stats['documents_count']; ?></h2>
                                    <p class="text-muted mb-0">المستندات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Filters -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4" style="color: var(--dark-color);">
                            <i class="fas fa-search me-2"></i>بحث وتصفية الملفات
                        </h5>
                        <form method="GET" action="" class="search-form">
                            <div class="row g-3">
                                <div class="col-md-12 col-lg-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" name="search" class="form-control border-start-0" 
                                               placeholder="ابحث بالعنوان أو الوصف..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-lg-2">
                                    <select name="category" class="form-control">
                                        <option value="">جميع الفئات</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" 
                                                <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                                                <?php echo $cat; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 col-lg-2">
                                    <select name="type" class="form-control">
                                        <option value="">جميع الأنواع</option>
                                        <?php foreach($fileTypes as $fileType): ?>
                                            <option value="<?php echo $fileType; ?>" 
                                                <?php echo ($type == $fileType) ? 'selected' : ''; ?>>
                                                <?php echo getFileTypeName($fileType); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 col-lg-2">
                                    <select name="sort" class="form-control">
                                        <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>الأحدث أولاً</option>
                                        <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>الأقدم أولاً</option>
                                        <option value="name_asc" <?php echo ($sort == 'name_asc') ? 'selected' : ''; ?>>الأبجدي (أ-ي)</option>
                                        <option value="name_desc" <?php echo ($sort == 'name_desc') ? 'selected' : ''; ?>>الأبجدي (ي-أ)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 col-lg-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i>تصفية
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Files Grid -->
                <div class="row g-4">
                    <?php if (empty($works)): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-folder-open mb-3"></i>
                                <h3 class="mb-3" style="color: var(--dark-color);">لا توجد ملفات</h3>
                                <p class="text-muted mb-4">لم يتم إضافة أي ملفات بعد. يمكنك البدء بإضافة أول ملف.</p>
                                <a href="add_work.php" class="btn btn-primary px-5">
                                    <i class="fas fa-plus me-2"></i>إضافة ملف جديد
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach($works as $work): ?>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="file-card">
                                    <!-- File Preview -->
                                    <div class="file-preview-container">
                                        <?php if($work['type'] == 'images' || $work['type'] == 'صورة'): ?>
                                            <img src="<?php echo "http://localhost/future_leaders_academy/".$work['media_url']; ?>" 
                                                 alt="<?php echo htmlspecialchars($work['title']); ?>"
                                                 loading="lazy">
                                        <?php elseif($work['type'] == 'video' || $work['type'] == 'فيديو'): ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" 
                                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                <i class="fas fa-play-circle fa-4x text-white"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" 
                                                 style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                                <i class="fas fa-file fa-4x text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- File Type Badge -->
                                        <span class="badge-file-type">
                                            <?php echo getFileTypeName($work['type']); ?>
                                        </span>
                                        
                                        <!-- Featured Badge -->
                                        <?php if($work['featured']): ?>
                                            <span class="featured-badge">
                                                <i class="fas fa-star me-1"></i>مميز
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- File Info -->
                                    <div class="p-4 file-info">
                                        <h6 class="mb-3" title="<?php echo htmlspecialchars($work['title']); ?>">
                                            <?php echo mb_substr($work['title'], 0, 40); ?>
                                            <?php echo (mb_strlen($work['title']) > 40) ? '...' : ''; ?>
                                            
                                        </h6>
                                        
                                        <div class="file-meta d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <i class="fas fa-hdd me-1"></i>
                                                <span><?php echo formatFileSize($work['file_size']); ?></span>
                                            </div>
                                            <div>
                                                <i class="fas fa-calendar me-1"></i>
                                                <span><?php echo date('Y-m-d', strtotime($work['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="category-badge">
                                                
                                                <?php echo htmlspecialchars($work['category']); ?>
                                            </span>
                                            <div class="file-actions">
                                                <a href="view_work.php?id=<?php echo $work['id']; ?>" 
                                                   class="btn btn-success" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_work.php?id=<?php echo $work['id']; ?>" 
                                                   class="btn btn-warning" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_work.php?id=<?php echo $work['id']; ?>" 
                                                   class="btn btn-danger" title="حذف"
                                                   onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination (if needed) -->
                <?php if (!empty($works) && count($works) > 12): ?>
                <div class="d-flex justify-content-center mt-5">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">السابق</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">التالي</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Floating Action Button for Mobile -->
    <a href="add_work.php" class="btn btn-primary btn-lg rounded-circle d-md-none" 
       style="position: fixed; bottom: 20px; left: 20px; width: 60px; height: 60px; z-index: 1000; box-shadow: 0 5px 20px rgba(67, 97, 238, 0.4);">
        <i class="fas fa-plus"></i>
    </a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // عناصر القائمة المتحركة
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        // التحكم في فتح/إغلاق القائمة للجوال
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : 'auto';
        }
        
        // أحداث فتح/إغلاق القائمة للجوال
        mobileMenuBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // إغلاق القائمة عند النقر على رابط (للجوال فقط)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });
        
        // إغلاق القائمة عند تغيير حجم النافذة للشاشات الكبيرة
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Add animation to cards on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.file-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = (entries.indexOf(entry) * 0.1) + 's';
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => observer.observe(card));
        });
        
        function showLogoutModal() {
            if (confirm('هل تريد تسجيل الخروج؟')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>