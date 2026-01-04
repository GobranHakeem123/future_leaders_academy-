<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$workId = $_GET['id'];

// زيادة عدد المشاهدات
$pdo->prepare("UPDATE works SET views_count = views_count + 1 WHERE id = ?")->execute([$workId]);

// جلب بيانات العمل
$stmt = $pdo->prepare("SELECT * FROM works WHERE id = ?");
$stmt->execute([$workId]);
$work = $stmt->fetch();

if (!$work) {
    header('Location: index.php?error=not_found');
    exit();
}

// تحويل JSON إلى مصفوفات
$features = [];
$tags = [];

if (!empty($work['features'])) {
    $features = json_decode($work['features'], true);
}

if (!empty($work['tags'])) {
    $tags = json_decode($work['tags'], true);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الملف - <?php echo htmlspecialchars($work['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-top: 20px;
        }
        
        .page-header {
            border-bottom: none;
            margin-bottom: 30px;
            padding-bottom: 15px;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 70px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }
        
        .file-preview-container {
            background: linear-gradient(135deg, #f5f7ff 0%, #eef1ff 100%);
            border-radius: 12px;
            padding: 25px;
            min-height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e1e5f1;
            transition: all 0.3s ease;
        }
        
        .file-preview-container:hover {
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.1);
        }
        
        .file-preview-img {
            max-width: 100%;
            max-height: 350px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .file-preview-img:hover {
            transform: scale(1.02);
        }
        
        .file-info-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border-top: 5px solid var(--primary-color);
        }
        
        .file-info-card .card-header {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .file-info-content {
            padding: 20px;
        }
        
        .file-title {
            color: var(--dark-bg);
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px dashed #eee;
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-icon {
            width: 36px;
            height: 36px;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            color: var(--primary-color);
            flex-shrink: 0;
        }
        
        .info-text {
            flex: 1;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 3px;
            font-size: 0.95rem;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .download-btn-container {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
            border-radius: 10px;
            margin-top: 25px;
        }
        
        .download-btn {
            background: linear-gradient(90deg, var(--success-color), #27ae60);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
            background: linear-gradient(90deg, #27ae60, var(--success-color));
        }
        
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .badge-custom {
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .tag-badge {
            background-color: #eef2ff;
            color: var(--primary-color);
            border-radius: 20px;
            padding: 7px 15px;
            margin: 0 5px 8px 0;
            display: inline-block;
            font-size: 0.85rem;
            border: 1px solid #d0d9ff;
        }
        
        .features-list {
            list-style: none;
            padding-right: 0;
        }
        
        .features-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            position: relative;
            padding-right: 25px;
        }
        
        .features-list li:before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 0;
            color: var(--success-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-edit {
            background: linear-gradient(90deg, var(--warning-color), #e67e22);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            flex: 1;
        }
        
        .btn-delete {
            background: linear-gradient(90deg, var(--danger-color), #c0392b);
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            flex: 1;
        }
        
        .file-type-badge {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            color: white;
            font-size: 0.9rem;
            padding: 6px 15px;
        }
        
        .featured-badge {
            background: linear-gradient(90deg, #f1c40f, #f39c12);
            color: #fff;
            font-size: 0.9rem;
            padding: 6px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .description-box {
            background-color: #f9f9ff;
            border-radius: 10px;
            padding: 20px;
            border-right: 4px solid var(--primary-color);
            line-height: 1.8;
        }
        
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .file-preview-container {
                min-height: 300px;
            }
        }
        
        .back-btn {
            background: linear-gradient(90deg, #6c757d, #495057);
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(108, 117, 125, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 page-header">
                    <div>
                        <h1 class="h2 page-title">عرض تفاصيل الملف</h1>
                        <p class="text-muted mb-0">تفاصيل كاملة عن الملف المحدد وإحصائياته</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn back-btn">
                            <i class="fas fa-arrow-right me-2"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="main-content">
                    <div class="row">
                        <!-- معاينة الملف -->
                        <div class="col-lg-8 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 pb-0">
                                    <h5 class="mb-3 fw-bold text-primary">
                                        <i class="fas fa-eye me-2"></i> معاينة الملف
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="file-preview-container">
                                        <div class="text-center">
                                            <img src="<?php echo "http://localhost/future_leaders_academy/".$work['media_url']; ?>" 
                                                 class="file-preview-img"
                                                 alt="<?php echo htmlspecialchars($work['title']); ?>">
                                            <p class="text-muted mt-3">معاينة الملف الحالي</p>
                                        </div>
                                    </div>
                                    
                                    <!-- إحصائيات التحميل والمشاهدات -->
                                    <div class="stats-container">
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $work['downloads_count']; ?></div>
                                            <div class="stat-label">
                                                <i class="fas fa-download me-1"></i> مرات التحميل
                                            </div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $work['views_count']; ?></div>
                                            <div class="stat-label">
                                                <i class="fas fa-eye me-1"></i> عدد المشاهدات
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- زر التحميل -->
                                    <div class="download-btn-container">
                                        <a href="<?php echo $work['media_url']; ?>" 
                                           class="btn download-btn" 
                                           download="<?php echo htmlspecialchars($work['file_name']); ?>">
                                            <i class="fas fa-download me-2"></i> تحميل الملف
                                            <small class="d-block mt-1 fw-normal">(<?php echo formatFileSize($work['file_size']); ?>)</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- معلومات الملف -->
                        <div class="col-lg-4 mb-4">
                            <div class="card file-info-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i> معلومات الملف
                                    </h5>
                                </div>
                                <div class="file-info-content">
                                    <!-- عنوان الملف -->
                                    <h4 class="file-title"><?php echo htmlspecialchars($work['title']); ?></h4>
                                    
                                    <!-- نوع الملف -->
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">نوع الملف</div>
                                            <div class="info-value">
                                                <span class="badge file-type-badge"><?php echo getFileTypeName($work['type']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- الفئة -->
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">الفئة</div>
                                            <div class="info-value">
                                                <span class="badge bg-info badge-custom"><?php echo htmlspecialchars($work['category']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- الدولة -->
                                    <?php if($work['country']): ?>
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">الدولة</div>
                                            <div class="info-value"><?php echo htmlspecialchars($work['country']); ?></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- تاريخ الإضافة -->
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">تاريخ الإضافة</div>
                                            <div class="info-value"><?php echo date('Y-m-d', strtotime($work['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    
                                    <!-- حجم الملف -->
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-hdd"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">حجم الملف</div>
                                            <div class="info-value"><?php echo formatFileSize($work['file_size']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <!-- اسم الملف -->
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-file-signature"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">اسم الملف الأصلي</div>
                                            <div class="info-value">
                                                <code class="bg-light p-1 rounded"><?php echo htmlspecialchars($work['file_name']); ?></code>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- ملف مميز -->
                                    <?php if($work['featured']): ?>
                                    <div class="info-item">
                                        <div class="info-icon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="info-text">
                                            <div class="info-label">حالة الملف</div>
                                            <div class="info-value">
                                                <span class="featured-badge">
                                                    <i class="fas fa-star me-1"></i> ملف مميز
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- الوصف -->
                                    <div class="mt-4">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-align-left me-2"></i> الوصف
                                        </h6>
                                        <div class="description-box">
                                            <?php echo nl2br(htmlspecialchars($work['description'])); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- المميزات -->
                                    <?php if (!empty($features)): ?>
                                    <div class="mt-4">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-list-check me-2"></i> المميزات
                                        </h6>
                                        <ul class="features-list">
                                            <?php foreach($features as $feature): ?>
                                                <li><?php echo htmlspecialchars($feature); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- الوسوم -->
                                    <?php if (!empty($tags)): ?>
                                    <div class="mt-4">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-tags me-2"></i> الوسوم
                                        </h6>
                                        <div>
                                            <?php foreach($tags as $tag): ?>
                                                <span class="tag-badge"><?php echo htmlspecialchars($tag); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- أزرار التحكم -->
                                    <div class="action-buttons">
                                        <a href="edit_work.php?id=<?php echo $work['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit me-2"></i> تعديل الملف
                                        </a>
                                        <a href="delete_work.php?id=<?php echo $work['id']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا الملف؟');">
                                            <i class="fas fa-trash me-2"></i> حذف الملف
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إضافة تأثيرات تفاعلية بسيطة
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير عند التمرير على أزرار التحميل
            const downloadBtn = document.querySelector('.download-btn');
            if(downloadBtn) {
                downloadBtn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                
                downloadBtn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            }
            
            // إضافة تأثير للصور عند التمرير
            const previewImg = document.querySelector('.file-preview-img');
            if(previewImg) {
                previewImg.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.03)';
                });
                
                previewImg.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        });
    </script>
</body>
</html>