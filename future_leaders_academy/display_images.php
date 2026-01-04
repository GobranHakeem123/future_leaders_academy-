<?php
// display_images.php

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

// استعلام لاسترجاع الأعمال ذات النوع 'image'
$sql = "SELECT id, title, category, country, media_url, media_path, description, 
               date, featured, tags, views, downloads 
        FROM works 
        WHERE type = 'image' 
        ORDER BY featured DESC, date DESC, created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرض الصور - أكاديمية قادة المستقبل</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .filters {
            background-color: white;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 1200px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            justify-content: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        select, input[type="text"] {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .stats {
            background-color: white;
            padding: 15px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 1200px;
            text-align: center;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        
        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .works-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }
        
        .work-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .work-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 2;
        }
        
        .image-container {
            position: relative;
            overflow: hidden;
            height: 200px;
        }
        
        .work-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .work-card:hover .work-image {
            transform: scale(1.05);
        }
        
        .work-info {
            padding: 20px;
        }
        
        .work-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
            height: 60px;
            overflow: hidden;
        }
        
        .work-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .category {
            background-color: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .country {
            background-color: #2ecc71;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .saudi { background-color: #2ecc71 !important; }
        .uae { background-color: #9b59b6 !important; }
        
        .work-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            height: 60px;
            overflow: hidden;
            line-height: 1.5;
        }
        
        .work-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .stats-info {
            display: flex;
            gap: 10px;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        
        .tag {
            background-color: #ecf0f1;
            color: #34495e;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            flex: 1;
        }
        
        .btn-view {
            background-color: #3498db;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #2980b9;
        }
        
        .btn-download {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-download:hover {
            background-color: #27ae60;
        }
        
        .no-results {
            text-align: center;
            padding: 50px;
            font-size: 1.2rem;
            color: #7f8c8d;
            grid-column: 1 / -1;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            gap: 10px;
        }
        
        .page-btn {
            padding: 10px 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .page-btn:hover {
            background-color: #3498db;
            color: white;
        }
        
        .page-btn.active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .footer {
            text-align: center;
            padding: 30px;
            margin-top: 50px;
            background-color: #2c3e50;
            color: white;
        }
        
        @media (max-width: 768px) {
            .works-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .works-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- رأس الصفحة -->
    <header class="header">
        <h1>معرض الصور - أكاديمية قادة المستقبل</h1>
        <p>استعرض أعمال الطلاب والطالبات المتميزة</p>
    </header>
    
    <!-- إحصائيات -->
    <div class="stats">
        إجمالي الصور المعروضة: <strong><?php echo count($works); ?></strong> صورة
    </div>
    
    <!-- فلترة -->
    <div class="filters">
        <div class="filter-group">
            <label for="category">تصفية حسب الفئة:</label>
            <select id="category">
                <option value="">جميع الفئات</option>
                <?php
                // استعلام للحصول على الفئات الفريدة
                $categorySql = "SELECT DISTINCT category FROM works WHERE type = 'image' ORDER BY category";
                $categoryStmt = $pdo->query($categorySql);
                $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($categories as $cat) {
                    echo "<option value='$cat'>$cat</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="country">تصفية حسب الدولة:</label>
            <select id="country">
                <option value="">جميع الدول</option>
                <option value="saudi">السعودية</option>
                <option value="uae">الإمارات</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="search">بحث:</label>
            <input type="text" id="search" placeholder="ابحث في العناوين والوصف...">
        </div>
        
        <div class="filter-group">
            <label for="featured">المميزة فقط:</label>
            <select id="featured">
                <option value="">جميع الصور</option>
                <option value="1">المميزة فقط</option>
            </select>
        </div>
    </div>
    
    <!-- معرض الصور -->
    <div class="gallery-container">
        <?php if (count($works) > 0): ?>
            <div class="works-grid" id="worksGrid">
                <?php foreach ($works as $work): 
                    // فك تشفير الوسوم والمميزات
                    $tags = json_decode($work['tags'] ?? '[]', true);
                    $features = json_decode($work['features'] ?? '[]', true);
                    
                    // تنسيق التاريخ
                    $date = date('Y-m-d', strtotime($work['date']));
                    $hijriDate = ''; // يمكن إضافة تحويل للتاريخ الهجري هنا
                    
                    // تحديد لون الدولة
                    $countryClass = $work['country'] == 'saudi' ? 'saudi' : 'uae';
                    
                    // زيادة عدد المشاهدات
                    $updateSql = "UPDATE works SET views = views + 1 WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([$work['id']]);
                ?>
                    <div class="work-card" data-category="<?php echo htmlspecialchars($work['category']); ?>" 
                         data-country="<?php echo $work['country']; ?>"
                         data-featured="<?php echo $work['featured']; ?>"
                         data-title="<?php echo htmlspecialchars($work['title']); ?>"
                         data-description="<?php echo htmlspecialchars($work['description']); ?>">
                        
                        <?php if ($work['featured']): ?>
                            <div class="featured-badge">مميز</div>
                        <?php endif; ?>
                        
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($work['media_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($work['title']); ?>"
                                 class="work-image"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=صورة+غير+متاحة'">
                        </div>
                        
                        <div class="work-info">
                            <h3 class="work-title"><?php echo htmlspecialchars($work['title']); ?></h3>
                            
                            <div class="work-meta">
                                <span class="category"><?php echo htmlspecialchars($work['category']); ?></span>
                                <span class="country <?php echo $countryClass; ?>">
                                    <?php echo $work['country'] == 'saudi' ? 'السعودية' : 'الإمارات'; ?>
                                </span>
                            </div>
                            
                            <p class="work-description">
                                <?php 
                                $description = htmlspecialchars($work['description']);
                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                ?>
                            </p>
                            
                            <?php if (!empty($tags) && is_array($tags)): ?>
                                <div class="tags-container">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="work-footer">
                                <div class="date">
                                    <span>📅</span>
                                    <span><?php echo $date; ?></span>
                                </div>
                                <div class="stats-info">
                                    <div class="stat">
                                        <span>👁️</span>
                                        <span><?php echo $work['views']; ?></span>
                                    </div>
                                    <div class="stat">
                                        <span>⬇️</span>
                                        <span><?php echo $work['downloads']; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="actions">
                                <a href="view_image.php?id=<?php echo $work['id']; ?>" class="btn btn-view">عرض التفاصيل</a>
                                <a href="download.php?id=<?php echo $work['id']; ?>" class="btn btn-download">تحميل</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>لا توجد صور متاحة للعرض حالياً.</p>
                <a href="add_work.php" style="margin-top: 20px; display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 6px;">أضف صورة جديدة</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- تذييل الصفحة -->
    <footer class="footer">
        <p>© <?php echo date('Y'); ?> أكاديمية قادة المستقبل. جميع الحقوق محفوظة.</p>
        <p style="margin-top: 10px; opacity: 0.8;">تم تطوير هذه المنصة لعرض أعمال الطلاب والطالبات المتميزين</p>
    </footer>
    
    <!-- JavaScript للفلترة -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category');
            const countryFilter = document.getElementById('country');
            const featuredFilter = document.getElementById('featured');
            const searchFilter = document.getElementById('search');
            const workCards = document.querySelectorAll('.work-card');
            
            function filterWorks() {
                const categoryValue = categoryFilter.value;
                const countryValue = countryFilter.value;
                const featuredValue = featuredFilter.value;
                const searchValue = searchFilter.value.toLowerCase();
                
                workCards.forEach(card => {
                    const category = card.getAttribute('data-category');
                    const country = card.getAttribute('data-country');
                    const featured = card.getAttribute('data-featured');
                    const title = card.getAttribute('data-title').toLowerCase();
                    const description = card.getAttribute('data-description').toLowerCase();
                    
                    let visible = true;
                    
                    // تصفية حسب الفئة
                    if (categoryValue && category !== categoryValue) {
                        visible = false;
                    }
                    
                    // تصفية حسب الدولة
                    if (countryValue && country !== countryValue) {
                        visible = false;
                    }
                    
                    // تصفية حسب المميز
                    if (featuredValue && featured !== featuredValue) {
                        visible = false;
                    }
                    
                    // تصفية حسب البحث
                    if (searchValue && !title.includes(searchValue) && !description.includes(searchValue)) {
                        visible = false;
                    }
                    
                    // إظهار/إخفاء البطاقة
                    card.style.display = visible ? 'block' : 'none';
                });
                
                // التحقق مما إذا كان هناك نتائج
                const visibleCards = Array.from(workCards).filter(card => card.style.display !== 'none');
                const noResults = document.querySelector('.no-results') || createNoResultsElement();
                
                if (visibleCards.length === 0 && workCards.length > 0) {
                    if (!document.querySelector('.no-results')) {
                        const grid = document.getElementById('worksGrid');
                        grid.parentNode.insertBefore(noResults, grid);
                    }
                    noResults.style.display = 'block';
                } else if (document.querySelector('.no-results')) {
                    document.querySelector('.no-results').style.display = 'none';
                }
            }
            
            function createNoResultsElement() {
                const div = document.createElement('div');
                div.className = 'no-results';
                div.innerHTML = '<p>لا توجد نتائج تطابق معايير البحث.</p>';
                return div;
            }
            
            // إضافة مستمعي الأحداث
            categoryFilter.addEventListener('change', filterWorks);
            countryFilter.addEventListener('change', filterWorks);
            featuredFilter.addEventListener('change', filterWorks);
            searchFilter.addEventListener('input', filterWorks);
            
            // تهيئة الفلترة
            filterWorks();
        });
    </script>
</body>
</html>