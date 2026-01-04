<?php
// إعدادات الاتصال بقاعدة البيانات
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

// دالة لرفع الصورة
function uploadImage($file, $targetDir = "uploads/") {
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // إنشاء اسم فريد للملف
    $fileName = time() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    
    // التحقق من نوع الملف
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($imageFileType, $allowedTypes)) {
        return ['error' => 'نوع الملف غير مسموح به. يرجى رفع صورة فقط.'];
    }
    
    // التحقق من حجم الملف (5MB كحد أقصى)
    if ($file["size"] > 5000000) {
        return ['error' => 'حجم الملف كبير جداً. الحد الأقصى 5MB.'];
    }
    
    // محاولة رفع الملف
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => $targetFile,
            'file_url' => "http://" . $_SERVER['HTTP_HOST'] . '/www//' . $targetFile
        ];
    } else {
        return ['error' => 'حدث خطأ أثناء رفع الملف.'];
    }
}

// معالجة النموذج عند إرساله
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من البيانات المطلوبة
    $requiredFields = ['title', 'category', 'country', 'description', 'date'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            die("حقل '$field' مطلوب.");
        }
    }
    
    // رفع الصورة
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        
        if (isset($uploadResult['error'])) {
            die($uploadResult['error']);
        }
        
        $mediaPath = $uploadResult['file_path'];
        $mediaUrl = $uploadResult['file_url'];
    } else {
        die("يرجى اختيار صورة صالحة.");
    }
    
    // تجهيز البيانات للإدخال
    $title = $_POST['title'];
    $category = $_POST['category'];
    $country = $_POST['country'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    
    // البيانات الاختيارية
    $features = isset($_POST['features']) ? json_encode($_POST['features']) : json_encode([]);
    $tags = isset($_POST['tags']) ? json_encode(explode(',', $_POST['tags'])) : json_encode([]);
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // استعداد الاستعلام
    $sql = "INSERT INTO works 
            (title, category, country, type, media_path, media_url, description, features, date, featured, tags)
            VALUES 
            (:title, :category, :country, 'image', :media_path, :media_url, :description, :features, :date, :featured, :tags)";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':country' => $country,
            ':media_path' => $mediaPath,
            ':media_url' => $mediaUrl,
            ':description' => $description,
            ':features' => $features,
            ':date' => $date,
            ':featured' => $featured,
            ':tags' => $tags
        ]);
        
        echo "تم إضافة العمل بنجاح! رقم العمل: " . $pdo->lastInsertId();
        
    } catch(PDOException $e) {
        die("حدث خطأ في الإضافة: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة عمل جديد</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .checkbox-group input {
            width: auto;
            margin-left: 10px;
            margin-bottom: 0;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>إضافة عمل جديد</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <!-- العنوان -->
            <label for="title">عنوان العمل:</label>
            <input type="text" id="title" name="title" required>
            
            <!-- الفئة -->
            <label for="category">الفئة:</label>
            <input type="text" id="category" name="category" placeholder="مثال: تصميم، برمجة..." required>
            
            <!-- الدولة -->
            <label for="country">الدولة:</label>
            <select id="country" name="country" required>
                <option value="">اختر الدولة</option>
                <option value="saudi">السعودية</option>
                <option value="uae">الإمارات</option>
            </select>
            
            <!-- الوصف -->
            <label for="description">وصف العمل:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <!-- التاريخ -->
            <label for="date">تاريخ العمل:</label>
            <input type="date" id="date" name="date" required>
            
            <!-- الصورة -->
            <label for="image">صورة العمل:</label>
            <input type="file" id="image" name="image" accept="image/*" required>
            
            <!-- المميزات (JSON) -->
            <label for="features">المميزات (اختياري):</label>
            <input type="text" id="features" name="features" placeholder='مثال: {"لون": "أزرق", "حجم": "كبير"}'>
            
            <!-- الوسوم -->
            <label for="tags">الوسوم (اختياري):</label>
            <input type="text" id="tags" name="tags" placeholder="مثال: تصميم,موقع,ريادة أعمال">
            
            <!-- مميز -->
            <div class="checkbox-group">
                <label for="featured">عرض كمميز:</label>
                <input type="checkbox" id="featured" name="featured" value="1">
            </div>
            
            <button type="submit">إضافة العمل</button>
        </form>
    </div>
</body>
</html>