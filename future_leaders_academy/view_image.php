<?php
// view_image.php

// التحقق من وجود معرف الصورة
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('معرف الصورة غير صالح.');
}

$imageId = intval($_GET['id']);

// الاتصال بقاعدة البيانات
$host = 'localhost';
$dbname = 'future_leaders_academy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// استعلام لاسترجاع بيانات الصورة
$sql = "SELECT * FROM works WHERE id = ? AND type = 'image'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$imageId]);
$work = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود الصورة
if (!$work) {
    die('الصورة غير موجودة.');
}

// زيادة عدد المشاهدات
$updateSql = "UPDATE works SET views = views + 1 WHERE id = ?";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->execute([$imageId]);

// فك تشفير البيانات
$tags = json_decode($work['tags'] ?? '[]', true);
$features = json_decode($work['features'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($work['title']); ?> - أكاديمية قادة المستقبل</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 8px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .back-btn:hover {
            background-color: #2980b9;
        }
        
        .image-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .image-section {
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-image {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            object-fit: contain;
        }
        
        .info-section {
            padding: 30px;
        }
        
        .work-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .meta-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .description {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        
        .description h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .tags-section {
            margin-bottom: 25px;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .tag {
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .features-section {
            margin-bottom: 30px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .feature {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }
        
        .feature-key {
            font-weight: bold;
            color: #2e7d32;
        }
        
        .feature-value {
            color: #1b5e20;
            margin-top: 5px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-download {
            background-color: #2ecc71;
            color: white;
            flex: 1;
        }
        
        .btn-download:hover {
            background-color: #27ae60;
        }
        
        .btn-share {
            background-color: #3498db;
            color: white;
            flex: 1;
        }
        
        .btn-share:hover {
            background-color: #2980b9;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-around;
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .related-works {
            margin-top: 50px;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .related-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
        }
        
        .related-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .related-card h4 {
            padding: 15px;
            font-size: 1rem;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .image-details {
                grid-template-columns: 1fr;
            }
            
            .meta-info {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .work-title {
                font-size: 1.3rem;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- رأس الصفحة -->
        <div class="header">
            <h1>أكاديمية قادة المستقبل</h1>
            <a href="display_images.php" class="back-btn">← العودة إلى المعرض</a>
        </div>
        
        <!-- تفاصيل الصورة -->
        <div class="image-details">
            <div class="image-section">
                <img src="<?php echo htmlspecialchars($work['media_url']); ?>" 
                     alt="<?php echo htmlspecialchars($work['title']); ?>"
                     class="main-image"
                     onerror="this.src='https://via.placeholder.com/600x400?text=صورة+غير+متاحة'">
            </div>
            
            <div class="info-section">
                <h1 class="work-title"><?php echo htmlspecialchars($work['title']); ?></h1>
                
                <div class="meta-info">
                    <div class="info-item">
                        <div class="info-label">الفئة</div>
                        <div class="info-value"><?php echo htmlspecialchars($work['category']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">الدولة</div>
                        <div class="info-value">
                            <?php echo $work['country'] == 'saudi' ? 'السعودية' : 'الإمارات العربية المتحدة'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">التاريخ</div>
                        <div class="info-value"><?php echo date('Y-m-d', strtotime($work['date'])); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">الحالة</div>
                        <div class="info-value">
                            <?php echo $work['featured'] ? 'مميز' : 'عادي'; ?>
                        </div>
                    </div>
                </div>
                
                <div class="description">
                    <h3>وصف العمل:</h3>
                    <p><?php echo nl2br(htmlspecialchars($work['description'])); ?></p>
                </div>
                
                <?php if (!empty($tags) && is_array($tags)): ?>
                    <div class="tags-section">
                        <h3>الوسوم:</h3>
                        <div class="tags-container">
                            <?php foreach ($tags as $tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($features) && is_array($features)): ?>
                    <div class="features-section">
                        <h3>المميزات:</h3>
                        <div class="features-grid">
                            <?php foreach ($features as $key => $value): ?>
                                <div class="feature">
                                    <div class="feature-key"><?php echo htmlspecialchars($key); ?></div>
                                    <div class="feature-value"><?php echo htmlspecialchars($value); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="actions">
                    <a href="download.php?id=<?php echo $work['id']; ?>" class="btn btn-download">
                        📥 تحميل الصورة
                    </a>
                    <button onclick="shareImage()" class="btn btn-share">
                        🔗 مشاركة
                    </button>
                </div>
            </div>
        </div>
        
        <!-- إحصائيات -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?php echo $work['views']; ?></div>
                <div class="stat-label">عدد المشاهدات</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $work['downloads']; ?></div>
                <div class="stat-label">عدد التنزيلات</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo date('Y-m-d', strtotime($work['created_at'])); ?></div>
                <div class="stat-label">تاريخ الإضافة</div>
            </div>
        </div>
        
        <!-- أعمال ذات صلة -->
        <?php
        // استعلام للحصول على أعمال ذات صلة
        $relatedSql = "SELECT id, title, media_url, category 
                      FROM works 
                      WHERE category = ? AND id != ? AND type = 'image'
                      ORDER BY created_at DESC 
                      LIMIT 4";
        $relatedStmt = $pdo->prepare($relatedSql);
        $relatedStmt->execute([$work['category'], $work['id']]);
        $relatedWorks = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($relatedWorks)):
        ?>
        <div class="related-works">
            <h2 class="section-title">أعمال ذات صلة</h2>
            <div class="related-grid">
                <?php foreach ($relatedWorks as $related): ?>
                    <a href="view_image.php?id=<?php echo $related['id']; ?>" class="related-card">
                        <img src="<?php echo htmlspecialchars($related['media_url']); ?>" 
                             alt="<?php echo htmlspecialchars($related['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x150?text=صورة'">
                        <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function shareImage() {
            const title = "<?php echo addslashes($work['title']); ?>";
            const url = window.location.href;
            const text = `انظر إلى هذا العمل المتميز: ${title}`;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                });
            } else {
                // نسخ الرابط إلى الحافظة
                navigator.clipboard.writeText(url).then(() => {
                    alert('تم نسخ رابط الصورة إلى الحافظة!');
                });
            }
        }
        
        // إظهار التأثير عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            const image = document.querySelector('.main-image');
            image.style.opacity = '0';
            image.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                image.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                image.style.opacity = '1';
                image.style.transform = 'scale(1)';
            }, 100);
        });
    </script>
</body>
</html>