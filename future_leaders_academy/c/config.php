<?php
// config.php

// في بداية ملف index.php أو header.php
function isMobileDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $mobileAgents = [
        'Android', 'webOS', 'iPhone', 'iPad', 'iPod',
        'BlackBerry', 'Windows Phone', 'Opera Mini', 'IEMobile'
    ];
    
    foreach ($mobileAgents as $agent) {
        if (strpos($userAgent, $agent) !== false) {
            return true;
        }
    }
    return false;
}

// إذا كان جوال ولم يطلب المستخدم إصدار الجوال
if (isMobileDevice() && !isset($_COOKIE['mobile_view'])) {
    // تعيين كوكي لإصدار الكمبيوتر
    setcookie('force_desktop', 'true', time() + (86400 * 30), "/"); // 30 يوم
    
    // يمكنك إضافة بارامتر أو عرض نسخة الكمبيوتر
    $_SESSION['view_type'] = 'desktop';
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // ضع اسم المستخدم هنا
define('DB_PASS', ''); // ضع كلمة المرور هنا
define('DB_NAME', 'future_leaders_academy');

// إعدادات الموقع
define('SITE_URL', 'http://localhost/future_leaders_academy'); // ضع رابط موقعك
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB

// أنواع الملفات المسموح بها
$allowed_types = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
    'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
    'pdf' => ['pdf'],
    'word' => ['doc', 'docx'],
    'excel' => ['xls', 'xlsx'],
    'powerpoint' => ['ppt', 'pptx'],
    'text' => ['txt', 'rtf'],
    'archive' => ['zip', 'rar']
];

// الاتصال بقاعدة البيانات
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
        }
        
        // تعيين ترميز UTF-8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("خطأ: " . $e->getMessage());
    }
}

// تحويل البيانات إلى JSON آمن
function safe_json_encode($value) {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>