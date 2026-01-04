<?php
require_once 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$workId = $_GET['id'];

// جلب بيانات العمل
$stmt = $pdo->prepare("SELECT * FROM works WHERE id = ?");
$stmt->execute([$workId]);
$work = $stmt->fetch();

if (!$work) {
    header('Location: index.php?error=not_found');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // التحقق من البيانات المطلوبة
        $requiredFields = ['title', 'category', 'country', 'description', 'date'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("حقل '$field' مطلوب.");
            }
        }
        
        // تجهيز البيانات
        $title = $_POST['title'];
        $category = $_POST['category'];
        $country = $_POST['country'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        
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
        
        // رفع صورة جديدة إذا تم اختيارها
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // حذف الصورة القديمة
            deleteFile($work['media_path']);
            
            // رفع الصورة الجديدة - التصحيح هنا
            $uploadResult = uploadFile($_FILES['image']);
            
            if (isset($uploadResult['error'])) {
                throw new Exception($uploadResult['error']);
            }
            
            $mediaPath = $uploadResult['file_path'];
            $mediaUrl = $uploadResult['file_url'];
        } else {
            $mediaPath = $work['media_path'];
            $mediaUrl = $work['media_url'];
        }
        
        // تحديث البيانات في قاعدة البيانات
        $sql = "UPDATE works SET 
                title = :title,
                category = :category,
                country = :country,
                media_path = :media_path,
                media_url = :media_url,
                description = :description,
                features = :features,
                date = :date,
                featured = :featured,
                tags = :tags,
                updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':country' => $country,
            ':media_path' => $mediaPath,
            ':media_url' => $mediaUrl,
            ':description' => $description,
            ':features' => $featuresJson,
            ':date' => $date,
            ':featured' => $featured,
            ':tags' => $tagsJson,
            ':id' => $workId
        ]);
        
        $success = "تم تحديث العمل بنجاح!";
        
        // إعادة جلب البيانات المحدثة
        $stmt = $pdo->prepare("SELECT * FROM works WHERE id = ?");
        $stmt->execute([$workId]);
        $work = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// تحويل JSON إلى نص للعرض في النموذج
$featuresText = '';
if (!empty($work['features'])) {
    $featuresArray = json_decode($work['features'], true);
    if (is_array($featuresArray)) {
        $featuresText = implode(', ', $featuresArray);
    }
}

$tagsText = '';
if (!empty($work['tags'])) {
    $tagsArray = json_decode($work['tags'], true);
    if (is_array($tagsArray)) {
        $tagsText = implode(', ', $tagsArray);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل العمل - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }
        
        .logo h2 {
            font-size: 22px;
            font-weight: 600;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-links li {
            padding: 12px 25px;
        }
        
        .nav-links li.active {
            background: rgba(255, 255, 255, 0.1);
            border-right: 3px solid white;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
        }
        
        .nav-links i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            margin-right: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e6ed;
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 28px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #a0aec0;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #718096;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #c6f6d5;
            color: #276749;
            border: 1px solid #9ae6b4;
        }
        
        .alert-danger {
            background-color: #fed7d7;
            color: #9b2c2c;
            border: 1px solid #fc8181;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .preview-image {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 5px;
            display: block;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .required::after {
            content: " *";
            color: #f56565;
        }
        
        .current-image {
            margin-top: 10px;
        }
        
        .current-image img {
            max-width: 200px;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 70px;
            }
            
            .main-content {
                margin-right: 70px;
            }
            
            .logo h2, .nav-links span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>لوحة التحكم</h2>
            </div>
            
            <ul class="nav-links">
                <li>
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>الصفحة الرئيسية</span>
                    </a>
                </li>
                <li>
                    <a href="add_work.php">
                        <i class="fas fa-plus-circle"></i>
                        <span>إضافة عمل جديد</span>
                    </a>
                </li>
                <li class="active">
                    <a href="#">
                        <i class="fas fa-edit"></i>
                        <span>تعديل العمل</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>الفئات</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>تعديل العمل #<?php echo $work['id']; ?></h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> العودة للقائمة
                </a>
            </div>
            
            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="editWorkForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="required">عنوان العمل</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($work['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category" class="required">الفئة</label>
                            <input type="text" id="category" name="category" class="form-control" 
                                   value="<?php echo htmlspecialchars($work['category']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country" class="required">الدولة</label>
                            <select id="country" name="country" class="form-control" required>
                                <option value="">اختر الدولة</option>
                                <option value="saudi" <?php echo (($_POST['country'] ?? '') == 'المملكة العربية السعودية') ? 'selected' : ''; ?>>المملكة العربية السعودية</option>
                                                <option value="uae" <?php echo (($_POST['country'] ?? '') == 'الإمارات العربية المتحدة') ? 'selected' : ''; ?>>الإمارات العربية المتحدة</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date" class="required">تاريخ العمل</label>
                            <input type="date" id="date" name="date" class="form-control" 
                                   value="<?php echo date('Y-m-d', strtotime($work['date'])); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="required">وصف العمل</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($work['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">صورة العمل</label>
                        
                        <div class="current-image">
                            <p>الصورة الحالية:</p>
                            <img src="<?php echo "http://localhost/future_leaders_academy/".$work['media_url']; ?>" 
                                 alt="<?php echo htmlspecialchars($work['title']); ?>">
                        </div>
                        
                        <p style="margin: 10px 0; color: #718096;">اترك الحقل فارغاً للحفاظ على الصورة الحالية</p>
                        
                        <input type="file" id="image" name="image" class="form-control" 
                               accept="image/*" onchange="previewImage(this)">
                        <img id="imagePreview" class="preview-image" alt="معاينة الصورة الجديدة" style="display: none;">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="features">المميزات</label>
                            <input type="text" id="features" name="features" class="form-control" 
                                   value="<?php echo htmlspecialchars($featuresText); ?>"
                                   placeholder="مثال: تصميم حديث, واجهة سهلة, ألوان جذابة">
                            <small style="color: #718096; font-size: 12px;">افصل بين المميزات بفاصلة (,)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="tags">الوسوم</label>
                            <input type="text" id="tags" name="tags" class="form-control" 
                                   value="<?php echo htmlspecialchars($tagsText); ?>"
                                   placeholder="مثال: تصميم,موقع,ريادة أعمال">
                            <small style="color: #718096; font-size: 12px;">افصل بين الوسوم بفاصلة (,)</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="featured" name="featured" value="1" 
                                   <?php echo ($work['featured'] == 1) ? 'checked' : ''; ?>>
                            <label for="featured">عرض العمل كمميز</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        document.getElementById('editWorkForm').addEventListener('submit', function(e) {
            const requiredFields = ['title', 'category', 'country', 'description', 'date'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    isValid = false;
                    element.style.borderColor = '#f56565';
                } else {
                    element.style.borderColor = '#e2e8f0';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول المطلوبة');
            }
        });
    </script>
</body>
</html>