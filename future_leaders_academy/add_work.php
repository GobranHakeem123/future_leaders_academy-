<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // التحقق من البيانات المطلوبة
        $requiredFields = ['title', 'category', 'description'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("حقل '$field' مطلوب.");
            }
        }
        
        // رفع الملف
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("يرجى اختيار ملف صالح.");
        }
        
        // تحديد نوع الملف تلقائياً أو من الاختيار
        $fileType = $_POST['file_type'] ?? 'auto';
        
        $uploadResult = uploadFile($_FILES['file'], $fileType);
        
        if (!$uploadResult['success']) {
            throw new Exception($uploadResult['error']);
        }
        
        // تجهيز البيانات
        $title = $_POST['title'];
        $category = $_POST['category'];
        $country = $_POST['country'] ?? '';
        $description = $_POST['description'];
        $date = $_POST['date'] ?? date('Y-m-d');
        
        // البيانات الاختيارية
        $features = [];
        if (!empty($_POST['features'])) {
            $featuresInput = $_POST['features'];
            $decoded = json_decode($featuresInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $features = $decoded;
            } else {
                $features = array_map('trim', explode(',', $featuresInput));
            }
        }
        
        $tags = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // تحويل المصفوفات إلى JSON
        $featuresJson = json_encode($features, JSON_UNESCAPED_UNICODE);
        $tagsJson = json_encode($tags, JSON_UNESCAPED_UNICODE);
        
        // إدخال البيانات في قاعدة البيانات
        $sql = "INSERT INTO works 
                (title, category, country, type, media_path, media_url, file_name, file_size, file_extension, 
                 description, features, date, featured, tags)
                VALUES 
                (:title, :category, :country, :type, :media_path, :media_url, :file_name, :file_size, 
                 :file_extension, :description, :features, :date, :featured, :tags)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':country' => $country,
            ':type' => $uploadResult['file_type'],
            ':media_path' => $uploadResult['file_path'],
            ':media_url' => $uploadResult['file_url'],
            ':file_name' => $uploadResult['file_name'],
            ':file_size' => $uploadResult['file_size'],
            ':file_extension' => $uploadResult['file_extension'],
            ':description' => $description,
            ':features' => $featuresJson,
            ':date' => $date,
            ':featured' => $featured,
            ':tags' => $tagsJson
        ]);
        
        $workId = $pdo->lastInsertId();
        
        // تحديث الإحصائيات
        updateStatisticsOnAdd($uploadResult['file_size'], $uploadResult['file_type']);
        
        $success = "تم إضافة الملف بنجاح! رقم الملف: $workId";
        
        // إعادة تعيين النموذج
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// جلب الفئات
$categoriesStmt = $pdo->query("SELECT name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// أنواع الملفات الثابتة (بدلاً من قاعدة البيانات)
$fileTypes = ['صورة', 'فيديو', 'PDF', 'Word', 'Excel', 'PowerPoint'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة ملف جديد - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            --danger-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --warning-gradient: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            --dark-bg: #1a1a2e;
            --dark-card: #16213e;
            --primary-color: #667eea;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: var(--light-bg);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: var(--primary-gradient);
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
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .mobile-menu-btn:hover {
            transform: scale(1.05);
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--dark-bg);
            color: white;
            padding: 2rem 1.5rem;
            width: 280px;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .nav-item i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        /* Sidebar Overlay for Mobile */
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
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            margin-right: 280px;
            min-height: 100vh;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.8rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-secondary:hover {
            background: var(--border-color);
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 1.8rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: var(--text-primary);
            font-size: 1rem;
        }
        
        .form-label.required::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        /* File Upload Styles */
        .file-upload-area {
            border: 3px dashed var(--border-color);
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            background: var(--light-bg);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }
        
        .file-upload-area.dragover {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.05);
        }
        
        .upload-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        .upload-text h4 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .upload-text p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        
        .btn-browse {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .btn-browse:hover {
            transform: translateY(-2px);
        }
        
        .file-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--light-bg);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .file-details h5 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .file-details p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .btn-remove {
            background: var(--danger-gradient);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .btn-remove:hover {
            transform: scale(1.1);
        }
        
        /* File Preview */
        .file-preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .file-icon {
            font-size: 4rem;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
        }
        
        .file-icon.image { color: #4299e1; }
        .file-icon.video { color: #ed8936; }
        .file-icon.pdf { color: #f56565; }
        .file-icon.document { color: #38b2ac; }
        
        /* Alert Styles */
        .alert {
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.3s ease;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fed7d7 0%, #fff5f5 100%);
            border: 2px solid #fc8181;
            color: #c53030;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #c6f6d5 0%, #f0fff4 100%);
            border: 2px solid #68d391;
            color: #276749;
        }
        
        .alert i {
            font-size: 1.5rem;
        }
        
        /* Checkbox Styles */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 12px;
            border: 2px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .checkbox-group:hover {
            border-color: var(--primary-color);
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        
        /* Supported Files */
        .supported-files {
            background: var(--light-bg);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .supported-files h6 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .file-types-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.8rem;
        }
        
        .file-type-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem;
            background: white;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .file-type-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        
        /* Animations */
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
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-container {
                display: block;
            }
            
            .sidebar {
                transform: translateX(100%);
                display: block;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-right: 0;
                width: 100%;
                padding: 1.5rem;
                padding-top: 5rem;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding-top: 4rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions button {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 1rem;
                padding-top: 5rem;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .file-upload-area {
                padding: 2rem;
            }
            
            .form-grid {
                gap: 1rem;
            }
            
            .file-types-grid {
                grid-template-columns: 1fr;
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
            background: var(--primary-gradient);
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
</head>
<body>
    <!-- Overlay for mobile menu -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="admin-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-folder-open"></i>
                    <span>إدارة الملفات</span>
                </div>
                <p style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-top: 0.5rem;">لوحة التحكم المتكاملة</p>
            </div>

            <div class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>الصفحة الرئيسية</span>
                </a>
                <a href="add_work.php" class="nav-item active">
                    <i class="fas fa-plus-circle"></i>
                    <span>إضافة ملف جديد</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>إدارة الفئات</span>
                </a>
                <a href="statistics.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>التقارير والإحصائيات</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>الإعدادات</span>
                </a>
            </div>

            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h4>المسؤول</h4>
                    <p>مدير النظام</p>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>إضافة ملف جديد</h1>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;">أضف محتوى جديد إلى المعرض الإبداعي</p>
                </div>
                <a href="index.php" class="btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    العودة للقائمة
                </a>
            </div>
            
            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>خطأ!</strong>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>تم بنجاح!</strong>
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Main Form -->
            <div class="card">
                <form method="POST" enctype="multipart/form-data" id="addFileForm">
                    <div class="form-grid">
                        <!-- Left Column - Form Fields -->
                        <div>
                            <div class="form-group">
                                <label for="title" class="form-label required">عنوان الملف</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                       placeholder="أدخل عنوانًا وصفياً للملف" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="category" class="form-label required">الفئة</label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">اختر الفئة</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                                <?php echo (($_POST['category'] ?? '') == $cat) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country" class="form-label">الدولة</label>
                                    <select class="form-control" id="country" name="country">
                                        <option value="">اختر الدولة</option>
                                        <option value="saudi" <?php echo (($_POST['country'] ?? '') == 'saudi') ? 'selected' : ''; ?>>
                                            المملكة العربية السعودية
                                        </option>
                                        <option value="uae" <?php echo (($_POST['country'] ?? '') == 'uae') ? 'selected' : ''; ?>>
                                            الإمارات العربية المتحدة
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label required">الوصف</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" placeholder="أدخل وصفًا تفصيليًا للملف" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date" class="form-label">تاريخ الملف</label>
                                    <input type="date" class="form-control" id="date" name="date" 
                                           value="<?php echo $_POST['date'] ?? date('Y-m-d'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="file_type" class="form-label">تحديد نوع الملف</label>
                                    <select class="form-control" id="file_type" name="file_type">
                                        <option value="">تحديد تلقائي</option>
                                        <?php foreach($fileTypes as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                                <?php echo (($_POST['file_type'] ?? '') == $type) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="features" class="form-label">المميزات</label>
                                    <input type="text" class="form-control" id="features" name="features" 
                                           value="<?php echo htmlspecialchars($_POST['features'] ?? ''); ?>"
                                           placeholder="مثال: تصميم حديث, واجهة سهلة, ألوان جذابة">
                                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">افصل بين المميزات بفاصلة (,)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tags" class="form-label">الوسوم</label>
                                    <input type="text" class="form-control" id="tags" name="tags" 
                                           value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                           placeholder="مثال: تصميم, موقع, ريادة أعمال">
                                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">افصل بين الوسوم بفاصلة (,)</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="checkbox-group">
                                    <input type="checkbox" id="featured" name="featured" value="1" 
                                           <?php echo (($_POST['featured'] ?? '') == '1') ? 'checked' : ''; ?>>
                                    <span>عرض الملف كمميز في الصفحة الرئيسية</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Right Column - File Upload -->
                        <div>
                            <div class="form-group">
                                <label class="form-label required">رفع الملف</label>
                                
                                <div class="file-upload-area" id="fileUploadContainer">
                                    <div id="uploadIcon">
                                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                        <div class="upload-text">
                                            <h4>اسحب وأفلت الملف هنا</h4>
                                            <p>أو انقر لاختيار الملف</p>
                                            <button type="button" class="btn-browse" id="browseBtn">تصفح الملفات</button>
                                        </div>
                                    </div>
                                    
                                    <div id="filePreview" class="file-preview-container" style="display: none;">
                                        <div id="fileIcon" class="file-icon">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <div id="fileName"></div>
                                        <div id="fileSize" style="color: var(--text-muted);"></div>
                                    </div>
                                </div>
                                
                                <input type="file" class="d-none" id="file" name="file" 
                                       accept="<?php echo getAllowedFileTypes(); ?>">
                                
                                <div id="fileInfo" class="file-info" style="display: none;">
                                    <div class="file-details">
                                        <h5 id="selectedFileName"></h5>
                                        <p id="selectedFileSize"></p>
                                    </div>
                                    <button type="button" class="btn-remove" id="removeFileBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Supported Files -->
                            <div class="supported-files">
                                <h6><i class="fas fa-info-circle me-2"></i>الأنواع المدعومة</h6>
                                <div class="file-types-grid">
                                    <div class="file-type-item">
                                        <i class="fas fa-image text-success"></i>
                                        <span>صور (JPG, PNG, GIF)</span>
                                    </div>
                                    <div class="file-type-item">
                                        <i class="fas fa-video text-danger"></i>
                                        <span>فيديو (MP4, AVI, MOV)</span>
                                    </div>
                                    <div class="file-type-item">
                                        <i class="fas fa-file-pdf text-warning"></i>
                                        <span>PDF</span>
                                    </div>
                                    <div class="file-type-item">
                                        <i class="fas fa-file-word text-primary"></i>
                                        <span>Word</span>
                                    </div>
                                    <div class="file-type-item">
                                        <i class="fas fa-file-excel text-success"></i>
                                        <span>Excel</span>
                                    </div>
                                    <div class="file-type-item">
                                        <i class="fas fa-file-powerpoint text-danger"></i>
                                        <span>PowerPoint</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-redo"></i>
                            إعادة تعيين
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i>
                            حفظ الملف
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
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
        document.querySelectorAll('.nav-item').forEach(link => {
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
        
        // عناصر DOM للرفع
        const fileInput = document.getElementById('file');
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const uploadIcon = document.getElementById('uploadIcon');
        const filePreview = document.getElementById('filePreview');
        const fileIcon = document.getElementById('fileIcon');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileInfo = document.getElementById('fileInfo');
        const selectedFileName = document.getElementById('selectedFileName');
        const selectedFileSize = document.getElementById('selectedFileSize');
        const browseBtn = document.getElementById('browseBtn');
        const removeFileBtn = document.getElementById('removeFileBtn');
        
        // فتح نافذة اختيار الملف
        browseBtn.addEventListener('click', () => fileInput.click());
        fileUploadContainer.addEventListener('click', () => fileInput.click());
        
        // تغيير الملف المحدد
        fileInput.addEventListener('change', handleFileSelect);
        
        // سحب وإفلات الملفات
        fileUploadContainer.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadContainer.classList.add('dragover');
        });
        
        fileUploadContainer.addEventListener('dragleave', () => {
            fileUploadContainer.classList.remove('dragover');
        });
        
        fileUploadContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadContainer.classList.remove('dragover');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect({ target: fileInput });
            }
        });
        
        // معالجة اختيار الملف
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // عرض معلومات الملف
            selectedFileName.textContent = file.name;
            selectedFileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'flex';
            
            // عرض معاينة
            const fileType = file.type.split('/')[0];
            const extension = file.name.split('.').pop().toLowerCase();
            
            uploadIcon.style.display = 'none';
            filePreview.style.display = 'flex';
            
            // تحديد الأيقونة المناسبة
            let iconClass = 'fas fa-file';
            let iconColorClass = '';
            
            if (fileType === 'image') {
                iconClass = 'fas fa-image';
                iconColorClass = 'image';
                // عرض معاينة الصورة
                const reader = new FileReader();
                reader.onload = function(e) {
                    fileIcon.innerHTML = `<img src="${e.target.result}" style="max-width: 80px; border-radius: 10px;">`;
                };
                reader.readAsDataURL(file);
            } else if (fileType === 'video') {
                iconClass = 'fas fa-video';
                iconColorClass = 'video';
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            } else if (extension === 'pdf') {
                iconClass = 'fas fa-file-pdf';
                iconColorClass = 'pdf';
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            } else if (['doc', 'docx'].includes(extension)) {
                iconClass = 'fas fa-file-word';
                iconColorClass = 'document';
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            } else if (['xls', 'xlsx'].includes(extension)) {
                iconClass = 'fas fa-file-excel';
                iconColorClass = 'document';
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            } else if (['ppt', 'pptx'].includes(extension)) {
                iconClass = 'fas fa-file-powerpoint';
                iconColorClass = 'document';
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            } else {
                fileIcon.innerHTML = `<i class="${iconClass} fa-3x"></i>`;
            }
            
            fileIcon.className = `file-icon ${iconColorClass}`;
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            // إضافة تأثير النبض
            fileUploadContainer.style.animation = 'pulse 0.6s ease';
            setTimeout(() => {
                fileUploadContainer.style.animation = '';
            }, 600);
        }
        
        // إزالة الملف المحدد
        removeFileBtn.addEventListener('click', () => {
            fileInput.value = '';
            fileInfo.style.display = 'none';
            filePreview.style.display = 'none';
            uploadIcon.style.display = 'block';
        });
        
        // تنسيق حجم الملف
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 بايت';
            const k = 1024;
            const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // التحقق من النموذج قبل الإرسال
        document.getElementById('addFileForm').addEventListener('submit', function(e) {
            const requiredFields = ['title', 'category', 'description', 'file'];
            let isValid = true;
            let errorMessage = '';
            
            requiredFields.forEach(field => {
                const element = field === 'file' ? document.getElementById('file') : document.getElementById(field);
                if (field === 'file') {
                    if (!element.files.length) {
                        isValid = false;
                        errorMessage = 'يرجى اختيار ملف لرفعه';
                        fileUploadContainer.style.borderColor = '#f56565';
                        fileUploadContainer.style.background = '#fff5f5';
                    } else {
                        fileUploadContainer.style.borderColor = '#68d391';
                        fileUploadContainer.style.background = '#f0fff4';
                    }
                } else {
                    if (!element.value.trim()) {
                        isValid = false;
                        element.style.borderColor = '#f56565';
                        element.style.background = '#fff5f5';
                    } else {
                        element.style.borderColor = '#cbd5e1';
                        element.style.background = 'white';
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>خطأ!</strong>
                        <p>${errorMessage || 'يرجى ملء جميع الحقول المطلوبة'}</p>
                    </div>
                `;
                
                const header = document.querySelector('.header');
                header.insertAdjacentElement('afterend', alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
                
                // Scroll to error
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
        
        // إضافة مؤشر تحميل عند الإرسال
        const form = document.getElementById('addFileForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>