<?php
// install.php

// إعدادات الاتصال بقاعدة البيانات
$db_host = 'localhost';
$db_user = 'root'; // غير هذا حسب إعداداتك
$db_pass = ''; // غير هذا حسب إعداداتك

// إنشاء الاتصال
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// إنشاء قاعدة البيانات
$sql = "CREATE DATABASE IF NOT EXISTS future_leaders_academy 
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
if ($conn->query($sql) === TRUE) {
    echo "✓ تم إنشاء قاعدة البيانات بنجاح<br>";
} else {
    echo "✗ خطأ في إنشاء قاعدة البيانات: " . $conn->error . "<br>";
}

// استخدام قاعدة البيانات
$conn->select_db('future_leaders_academy');

// إنشاء الجداول
$tables = [
    "works" => "CREATE TABLE IF NOT EXISTS works (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        type VARCHAR(50) DEFAULT 'image',
        media_path VARCHAR(500) NOT NULL,
        media_url VARCHAR(500) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_size INT,
        file_extension VARCHAR(20),
        description TEXT,
        features JSON,
        date DATE NOT NULL,
        featured TINYINT(1) DEFAULT 0,
        tags JSON,
        downloads_count INT DEFAULT 0,
        views_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "categories" => "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "allowed_file_types" => "CREATE TABLE IF NOT EXISTS allowed_file_types (
        id INT PRIMARY KEY AUTO_INCREMENT,
        type VARCHAR(50) NOT NULL,
        extension VARCHAR(20) NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        max_size INT DEFAULT 5242880,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    "statistics" => "CREATE TABLE IF NOT EXISTS statistics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        total_files INT DEFAULT 0,
        total_size BIGINT DEFAULT 0,
        images_count INT DEFAULT 0,
        videos_count INT DEFAULT 0,
        documents_count INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✓ تم إنشاء جدول $tableName بنجاح<br>";
    } else {
        echo "✗ خطأ في إنشاء جدول $tableName: " . $conn->error . "<br>";
    }
}

// إضافة بيانات أولية
$initialData = [
    "INSERT INTO categories (name, slug, description, icon) VALUES 
    ('ملفات المعلمين', 'teacher-files', 'ملفات المعلمين والتقارير', 'fa-folder-open'),
    ('الخطط العلاجية', 'treatment-plans', 'خطط علاجية للطلاب', 'fa-heartbeat'),
    ('عروض بوربوينت', 'presentations', 'عروض تقديمية تعليمية', 'fa-desktop'),
    ('بحوث جامعية ورسائل', 'university-research', 'بحوث ورسائل جامعية', 'fa-graduation-cap'),
    ('حل منصات تعليمية', 'e-learning-platforms', 'حلول للمنصات التعليمية', 'fa-laptop-code'),
    ('ترجمة أكاديمية', 'academic-translation', 'ترجمة المواد الأكاديمية', 'fa-language'),
    ('تقارير وواجبات', 'reports-assignments', 'تقارير وواجبات دراسية', 'fa-file-alt')",
    
    "INSERT INTO allowed_file_types (type, extension, mime_type, max_size) VALUES 
    ('image', 'jpg', 'image/jpeg', 10485760),
    ('image', 'png', 'image/png', 10485760),
    ('image', 'gif', 'image/gif', 10485760),
    ('image', 'webp', 'image/webp', 10485760),
    ('video', 'mp4', 'video/mp4', 524288000),
    ('pdf', 'pdf', 'application/pdf', 104857600),
    ('word', 'doc', 'application/msword', 52428800),
    ('word', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 52428800),
    ('powerpoint', 'ppt', 'application/vnd.ms-powerpoint', 52428800),
    ('powerpoint', 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 52428800)",
    
    "INSERT INTO statistics (total_files) VALUES (0)"
];

foreach ($initialData as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✓ تم إضافة البيانات الأولية بنجاح<br>";
    } else {
        echo "✗ خطأ في إضافة البيانات: " . $conn->error . "<br>";
    }
}

$conn->close();

echo "<h3>✅ تم تثبيت النظام بنجاح!</h3>";
echo "<p>يمكنك الآن:</p>";
echo "<ul>";
echo "<li><a href='index.php'>الذهاب إلى الصفحة الرئيسية</a></li>";
echo "<li><a href='admin-login.php'>تسجيل دخول المدير</a></li>";
echo "</ul>";
echo "<p><strong>ملاحظة:</strong> حذف ملف install.php بعد التثبيت</p>";
?>