<?php
// admin-panel-pro.php
session_start();

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'future_leaders_academy');

// معلومات تسجيل الدخول
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// إعدادات رفع الملفات
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('ALLOWED_EXTENSIONS', [
    'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
    'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
    'pdf' => ['pdf'],
    'presentations' => ['ppt', 'pptx'],
    'documents' => ['doc', 'docx', 'xls', 'xlsx', 'txt', 'rtf'],
    'archives' => ['zip', 'rar', '7z']
]);

// إنشاء الاتصال بقاعدة البيانات
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// إنشاء مجلد التحميلات إذا لم يكن موجوداً
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
    // حماية المجلد
    file_put_contents(UPLOAD_DIR . '.htaccess', "Order Deny,Allow\nDeny from all\n<Files ~ \"\\.(jpg|jpeg|png|gif|webp|bmp|svg|mp4|avi|mov|wmv|flv|mkv|webm|pdf|ppt|pptx|doc|docx|xls|xlsx|txt|rtf|zip|rar|7z)$\">\nAllow from all\n</Files>");
}

// إنشاء مجلد التحميلات المؤقتة
define('TEMP_UPLOAD_DIR', 'temp_uploads/');
if (!file_exists(TEMP_UPLOAD_DIR)) {
    mkdir(TEMP_UPLOAD_DIR, 0777, true);
}

// التحقق من صلاحية الجلسة
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = cleanInput($_POST['username'] ?? '');
    $password = cleanInput($_POST['password'] ?? '');
    
    if (verifyCredentials($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_id'] = 1; // يمكن توسيع النظام لدعم مستخدمين متعددين
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "اسم المستخدم أو كلمة المرور غير صحيحة";
    }
}

// معالجة تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// التحقق من أذونات الملفات
checkFilePermissions();

// === معالجة رفع الملفات عبر AJAX ===
if (isset($_POST['action'])) {
    if (!isLoggedIn()) {
        header('HTTP/1.1 401 Unauthorized');
        exit(json_encode(['error' => 'غير مصرح']));
    }
    
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'upload_temp':
            echo json_encode(handleTempUpload());
            break;
            
        case 'delete_temp':
            echo json_encode(deleteTempFile());
            break;
            
        case 'get_upload_progress':
            echo json_encode(getUploadProgress());
            break;
            
        case 'save_work':
            echo json_encode(saveWork($conn));
            break;
            
        case 'delete_work':
            echo json_encode(deleteWork($conn));
            break;
            
        case 'get_work':
            echo json_encode(getWork($conn));
            break;
            
        case 'update_work':
            echo json_encode(updateWork($conn));
            break;
            
        default:
            echo json_encode(['error' => 'إجراء غير معروف']);
    }
    exit;
}

// === دوال المعالجة ===

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function verifyCredentials($username, $password) {
    // يمكن تطوير هذا الجزء للتحقق من قاعدة بيانات المستخدمين
    return $username === ADMIN_USERNAME && password_verify($password, password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT));
}

function checkFilePermissions() {
    $folders = [UPLOAD_DIR, TEMP_UPLOAD_DIR];
    foreach ($folders as $folder) {
        if (file_exists($folder) && !is_writable($folder)) {
            die("خطأ: المجلد $folder غير قابل للكتابة. يرجى تعديل الأذونات.");
        }
    }
}

function handleTempUpload() {
    if (!isset($_FILES['file'])) {
        return ['error' => 'لم يتم رفع أي ملف'];
    }
    
    $file = $_FILES['file'];
    $fileCount = count($file['name']);
    $results = [];
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($file['error'][$i] !== UPLOAD_ERR_OK) {
            $results[] = ['error' => getUploadError($file['error'][$i])];
            continue;
        }
        
        $fileName = cleanFileName($file['name'][$i]);
        $fileSize = $file['size'][$i];
        $fileTmp = $file['tmp_name'][$i];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // التحقق من حجم الملف
        if ($fileSize > MAX_FILE_SIZE) {
            $results[] = ['error' => "الملف $fileName يتجاوز الحد المسموح (" . formatFileSize(MAX_FILE_SIZE) . ")"];
            continue;
        }
        
        // التحقق من نوع الملف
        if (!isValidFileType($fileExt)) {
            $results[] = ['error' => "نوع الملف $fileName غير مدعوم"];
            continue;
        }
        
        // إنشاء اسم فريد للملف
        $uniqueName = generateUniqueFileName($fileName);
        $tempPath = TEMP_UPLOAD_DIR . $uniqueName;
        
        // نقل الملف إلى المجلد المؤقت
        if (move_uploaded_file($fileTmp, $tempPath)) {
            $fileType = getFileTypeByExtension($fileExt);
            
            $results[] = [
                'success' => true,
                'original_name' => $fileName,
                'temp_name' => $uniqueName,
                'size' => $fileSize,
                'type' => $fileType,
                'preview' => getFilePreview($tempPath, $fileType, $fileExt),
                'icon' => getFileIcon($fileExt)
            ];
        } else {
            $results[] = ['error' => "فشل في رفع الملف $fileName"];
        }
    }
    
    return $results;
}

function deleteTempFile() {
    $fileName = cleanInput($_POST['file_name'] ?? '');
    $filePath = TEMP_UPLOAD_DIR . $fileName;
    
    if (file_exists($filePath) && unlink($filePath)) {
        return ['success' => true];
    }
    
    return ['error' => 'فشل في حذف الملف'];
}

function getUploadProgress() {
    if (isset($_SESSION['upload_progress'])) {
        return $_SESSION['upload_progress'];
    }
    return ['progress' => 0];
}

function saveWork($conn) {
    $data = json_decode($_POST['data'], true);
    
    // التحقق من البيانات المطلوبة
    $required = ['title', 'category', 'country', 'type', 'description'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['error' => "يرجى ملء حقل " . getFieldName($field)];
        }
    }
    
    // التحقق من وجود ملفات
    if (empty($data['files']) || !is_array($data['files'])) {
        return ['error' => 'يرجى رفع ملف واحد على الأقل'];
    }
    
    // معالجة ونقل الملفات من المجلد المؤقت إلى الدائم
    $mediaPaths = [];
    $totalSize = 0;
    
    foreach ($data['files'] as $file) {
        $tempPath = TEMP_UPLOAD_DIR . $file['temp_name'];
        $finalPath = UPLOAD_DIR . $file['temp_name'];
        
        if (file_exists($tempPath) && rename($tempPath, $finalPath)) {
            $mediaPaths[] = [
                'path' => $finalPath,
                'url' => $finalPath,
                'name' => $file['original_name'],
                'size' => $file['size'],
                'type' => $file['type']
            ];
            $totalSize += $file['size'];
        }
    }
    
    if (empty($mediaPaths)) {
        return ['error' => 'فشل في حفظ الملفات'];
    }
    
    // إدخال البيانات في قاعدة البيانات
    $title = $conn->real_escape_string($data['title']);
    $category = $conn->real_escape_string($data['category']);
    $country = $conn->real_escape_string($data['country']);
    $type = $conn->real_escape_string($data['type']);
    $date = $conn->real_escape_string($data['date'] ?? date('Y-m-d'));
    $description = $conn->real_escape_string($data['description']);
    $features = isset($data['features']) ? $conn->real_escape_string(json_encode($data['features'])) : '[]';
    $tags = isset($data['tags']) ? $conn->real_escape_string(json_encode($data['tags'])) : '[]';
    $featured = isset($data['featured']) && $data['featured'] ? 1 : 0;
    $mediaData = $conn->real_escape_string(json_encode($mediaPaths));
    
    $sql = "INSERT INTO works (title, category, country, type, media_data, description, 
            features, date, featured, tags, file_size, created_at) 
            VALUES ('$title', '$category', '$country', '$type', '$mediaData', '$description', 
            '$features', '$date', $featured, '$tags', $totalSize, NOW())";
    
    if ($conn->query($sql)) {
        $workId = $conn->insert_id;
        updateStatistics($conn);
        return [
            'success' => true,
            'message' => 'تم إضافة العمل بنجاح',
            'work_id' => $workId
        ];
    }
    
    return ['error' => 'حدث خطأ أثناء حفظ العمل: ' . $conn->error];
}

function deleteWork($conn) {
    $workId = intval($_POST['work_id']);
    
    // الحصول على معلومات الملفات
    $result = $conn->query("SELECT media_data FROM works WHERE id = $workId");
    if ($result && $row = $result->fetch_assoc()) {
        $mediaData = json_decode($row['media_data'], true);
        
        // حذف الملفات من السيرفر
        if (is_array($mediaData)) {
            foreach ($mediaData as $media) {
                if (isset($media['path']) && file_exists($media['path'])) {
                    unlink($media['path']);
                }
            }
        }
    }
    
    // حذف من قاعدة البيانات
    if ($conn->query("DELETE FROM works WHERE id = $workId")) {
        updateStatistics($conn);
        return ['success' => true, 'message' => 'تم حذف العمل بنجاح'];
    }
    
    return ['error' => 'فشل في حذف العمل'];
}

function getWork($conn) {
    $workId = intval($_POST['work_id']);
    $result = $conn->query("SELECT * FROM works WHERE id = $workId");
    
    if ($result && $row = $result->fetch_assoc()) {
        $row['features'] = json_decode($row['features'], true);
        $row['tags'] = json_decode($row['tags'], true);
        $row['media_data'] = json_decode($row['media_data'], true);
        return ['success' => true, 'data' => $row];
    }
    
    return ['error' => 'لم يتم العثور على العمل'];
}

function updateWork($conn) {
    $workId = intval($_POST['work_id']);
    $data = json_decode($_POST['data'], true);
    
    // التحقق من البيانات المطلوبة
    $required = ['title', 'category', 'country', 'type', 'description'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['error' => "يرجى ملء حقل " . getFieldName($field)];
        }
    }
    
    $title = $conn->real_escape_string($data['title']);
    $category = $conn->real_escape_string($data['category']);
    $country = $conn->real_escape_string($data['country']);
    $type = $conn->real_escape_string($data['type']);
    $date = $conn->real_escape_string($data['date'] ?? date('Y-m-d'));
    $description = $conn->real_escape_string($data['description']);
    $features = isset($data['features']) ? $conn->real_escape_string(json_encode($data['features'])) : '[]';
    $tags = isset($data['tags']) ? $conn->real_escape_string(json_encode($data['tags'])) : '[]';
    $featured = isset($data['featured']) && $data['featured'] ? 1 : 0;
    
    // إذا تم رفع ملفات جديدة
    $mediaData = null;
    $totalSize = 0;
    
    if (!empty($data['files']) && is_array($data['files'])) {
        $mediaPaths = [];
        
        foreach ($data['files'] as $file) {
            $tempPath = TEMP_UPLOAD_DIR . $file['temp_name'];
            $finalPath = UPLOAD_DIR . $file['temp_name'];
            
            if (file_exists($tempPath) && rename($tempPath, $finalPath)) {
                $mediaPaths[] = [
                    'path' => $finalPath,
                    'url' => $finalPath,
                    'name' => $file['original_name'],
                    'size' => $file['size'],
                    'type' => $file['type']
                ];
                $totalSize += $file['size'];
            }
        }
        
        if (!empty($mediaPaths)) {
            $mediaData = $conn->real_escape_string(json_encode($mediaPaths));
        }
    }
    
    // بناء استعلام التحديث
    $sql = "UPDATE works SET 
            title = '$title',
            category = '$category',
            country = '$country',
            type = '$type',
            description = '$description',
            features = '$features',
            date = '$date',
            featured = $featured,
            tags = '$tags',
            updated_at = NOW()";
    
    if ($mediaData !== null) {
        $sql .= ", media_data = '$mediaData', file_size = $totalSize";
    }
    
    $sql .= " WHERE id = $workId";
    
    if ($conn->query($sql)) {
        updateStatistics($conn);
        return ['success' => true, 'message' => 'تم تحديث العمل بنجاح'];
    }
    
    return ['error' => 'حدث خطأ أثناء تحديث العمل: ' . $conn->error];
}

function updateStatistics($conn) {
    $totalFiles = $conn->query("SELECT COUNT(*) as count FROM works")->fetch_assoc()['count'];
    $saudiFiles = $conn->query("SELECT COUNT(*) as count FROM works WHERE country = 'saudi'")->fetch_assoc()['count'];
    $uaeFiles = $conn->query("SELECT COUNT(*) as count FROM works WHERE country = 'uae'")->fetch_assoc()['count'];
    $featuredFiles = $conn->query("SELECT COUNT(*) as count FROM works WHERE featured = 1")->fetch_assoc()['count'];
    
    $documentsCount = $conn->query("SELECT COUNT(*) as count FROM works WHERE type IN ('pdf', 'document', 'presentation')")->fetch_assoc()['count'];
    
    $totalSizeResult = $conn->query("SELECT SUM(file_size) as total FROM works");
    $totalSize = $totalSizeResult->fetch_assoc()['total'] ?? 0;
    
    $conn->query("UPDATE statistics SET 
        total_files = $totalFiles,
        saudi_files = $saudiFiles,
        uae_files = $uaeFiles,
        featured_files = $featuredFiles,
        documents_count = $documentsCount,
        total_size = $totalSize,
        updated_at = NOW()
    ");
}

// === دوال المساعدة ===

function getUploadError($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'الملف يتجاوز الحد المسموح به في السيرفر',
        UPLOAD_ERR_FORM_SIZE => 'الملف يتجاوز الحد المسموح به في النموذج',
        UPLOAD_ERR_PARTIAL => 'تم رفع الملف جزئياً فقط',
        UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
        UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت غير موجود',
        UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص',
        UPLOAD_ERR_EXTENSION => 'تم إيقاف الرفع بواسطة إضافة PHP'
    ];
    return $errors[$errorCode] ?? 'خطأ غير معروف في الرفع';
}

function cleanFileName($fileName) {
    $fileName = preg_replace('/[^a-zA-Z0-9_.\-\p{Arabic}]/u', '', $fileName);
    $fileName = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', '', $fileName);
    $fileName = mb_ereg_replace('([\.]{2,})', '', $fileName);
    return $fileName;
}

function generateUniqueFileName($originalName) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = substr($name, 0, 50); // تقليل طول الاسم
    $uniqueId = uniqid('', true);
    return $name . '_' . $uniqueId . '.' . $extension;
}

function isValidFileType($extension) {
    foreach (ALLOWED_EXTENSIONS as $types) {
        if (in_array($extension, $types)) {
            return true;
        }
    }
    return false;
}

function getFileTypeByExtension($extension) {
    foreach (ALLOWED_EXTENSIONS as $type => $extensions) {
        if (in_array($extension, $extensions)) {
            return $type;
        }
    }
    return 'unknown';
}

function getFilePreview($filePath, $fileType, $extension) {
    if ($fileType === 'images') {
        return $filePath;
    }
    
    return getFileIcon($extension);
}

function getFileIcon($extension) {
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word',
        'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel',
        'xlsx' => 'fa-file-excel',
        'ppt' => 'fa-file-powerpoint',
        'pptx' => 'fa-file-powerpoint',
        'txt' => 'fa-file-alt',
        'zip' => 'fa-file-archive',
        'rar' => 'fa-file-archive',
        '7z' => 'fa-file-archive',
        'mp4' => 'fa-file-video',
        'avi' => 'fa-file-video',
        'mov' => 'fa-file-video'
    ];
    
    return $icons[$extension] ?? 'fa-file';
}

function getFieldName($field) {
    $names = [
        'title' => 'العنوان',
        'category' => 'الفئة',
        'country' => 'البلد',
        'type' => 'نوع الملف',
        'description' => 'الوصف'
    ];
    return $names[$field] ?? $field;
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 ب';
    $k = 1024;
    $sizes = ['ب', 'ك.ب', 'م.ب', 'ج.ب'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// الحصول على الإحصائيات
function getStatistics($conn) {
    $stats = [
        'total_works' => 0,
        'saudi_works' => 0,
        'uae_works' => 0,
        'featured_works' => 0,
        'documents_count' => 0,
        'storage_used' => 0
    ];
    
    $result = $conn->query("SELECT * FROM statistics LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats = [
            'total_works' => $row['total_files'] ?? 0,
            'saudi_works' => $row['saudi_files'] ?? 0,
            'uae_works' => $row['uae_files'] ?? 0,
            'featured_works' => $row['featured_files'] ?? 0,
            'documents_count' => $row['documents_count'] ?? 0,
            'storage_used' => $row['total_size'] ?? 0
        ];
    }
    
    return $stats;
}

// الحصول على جميع الأعمال
function getAllWorks($conn) {
    $works = [];
    $result = $conn->query("SELECT * FROM works ORDER BY created_at DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $works[] = $row;
        }
    }
    return $works;
}

// تنظيف الملفات المؤقتة القديمة
function cleanupTempFiles() {
    $files = glob(TEMP_UPLOAD_DIR . '*');
    $now = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) > 3600) { // حذف الملفات الأقدم من ساعة
                unlink($file);
            }
        }
    }
}

// تنظيف الملفات المؤقتة
cleanupTempFiles();

// الحصول على البيانات
$statistics = getStatistics($conn);
$all_works = getAllWorks($conn);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الاحترافية - إدارة الأعمال</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --primary-gold: #C9A227;
            --primary-gold-light: #E8D48A;
            --primary-gold-dark: #9A7B1A;
            --admin-blue: #0F172A;
            --admin-purple: #7C3AED;
            --admin-purple-light: #A78BFA;
            --success-green: #10B981;
            --error-red: #EF4444;
            --warning-orange: #F59E0B;
            --info-blue: #3B82F6;
            
            /* UI Colors */
            --bg-primary: #F8F9FC;
            --bg-secondary: #FFFFFF;
            --bg-card: #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #94A3B8;
            --border-color: #E2E8F0;
            
            /* Gradients */
            --gradient-admin: linear-gradient(135deg, #7C3AED 0%, #A78BFA 100%);
            --gradient-success: linear-gradient(135deg, #10B981 0%, #34D399 100%);
            --gradient-error: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
            --gradient-warning: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
            --gradient-info: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            
            /* Transitions */
            --transition-fast: 0.2s ease;
            --transition-normal: 0.3s ease;
            
            /* Shadows */
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.12);
            
            /* Z-index */
            --z-modal: 1050;
        }

        /* ===== Reset & Base Styles ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Kufi Arabic', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.7;
        }

        /* ===== Layout Components ===== */
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ===== Login Screen ===== */
        .login-screen {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--admin-blue) 0%, #1E293B 100%);
            padding: 2rem;
        }

        .login-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            font-size: 1.8rem;
            font-weight: 900;
            background: var(--gradient-admin);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        /* ===== Form Styles ===== */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-card);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all var(--transition-normal);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--admin-purple);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-normal);
            border: none;
            font-family: inherit;
            font-size: 0.95rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--gradient-admin);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--bg-primary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
        }

        .btn-danger {
            background: var(--gradient-error);
            color: white;
        }

        .btn-warning {
            background: var(--gradient-warning);
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* ===== Admin Panel ===== */
        .admin-panel {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .admin-header {
            background: var(--gradient-admin);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-brand h1 {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .admin-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* ===== Upload Area ===== */
        .upload-area {
            border: 3px dashed var(--border-color);
            border-radius: var(--radius-lg);
            padding: 3rem 2rem;
            text-align: center;
            background: var(--bg-secondary);
            cursor: pointer;
            transition: all var(--transition-normal);
            margin-bottom: 2rem;
            position: relative;
        }

        .upload-area:hover {
            border-color: var(--admin-purple);
            background: rgba(124, 58, 237, 0.02);
        }

        .upload-area.dragover {
            border-color: var(--admin-purple);
            background: rgba(124, 58, 237, 0.05);
            transform: scale(1.01);
        }

        .upload-icon {
            font-size: 4rem;
            color: var(--admin-purple);
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }

        .upload-text h3 {
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .upload-text p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .upload-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--border-color);
            overflow: hidden;
            border-radius: 0 0 var(--radius-md) var(--radius-md);
        }

        .upload-progress-bar {
            height: 100%;
            background: var(--gradient-success);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* ===== Uploaded Files Preview ===== */
        .files-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .file-item {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            overflow: hidden;
            position: relative;
            transition: all var(--transition-normal);
        }

        .file-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .file-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
            background: var(--bg-primary);
        }

        .file-icon {
            width: 100%;
            height: 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--text-muted);
            background: var(--bg-primary);
        }

        .file-info {
            padding: 0.75rem;
            border-top: 1px solid var(--border-color);
        }

        .file-name {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-size {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .file-remove {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity var(--transition-normal);
        }

        .file-item:hover .file-remove {
            opacity: 1;
        }

        .file-remove:hover {
            background: var(--error-red);
        }

        /* ===== Admin Tabs ===== */
        .admin-tabs {
            display: flex;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            overflow: hidden;
        }

        .admin-tab {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normal);
            border-bottom: 3px solid transparent;
            font-family: inherit;
            flex: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .admin-tab.active {
            color: var(--admin-purple);
            border-bottom-color: var(--admin-purple);
            background: rgba(124, 58, 237, 0.1);
        }

        .admin-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .admin-section.active {
            display: block;
        }

        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
        }

        .stat-card:hover {
            border-color: var(--admin-purple);
            box-shadow: var(--shadow-sm);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--admin-purple);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* ===== Works List ===== */
        .works-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .work-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all var(--transition-normal);
        }

        .work-item:hover {
            border-color: var(--admin-purple);
            box-shadow: var(--shadow-sm);
        }

        .work-item-info {
            flex: 1;
        }

        .work-item-info h4 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .work-item-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            flex-wrap: wrap;
        }

        .work-item-type {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .work-item-type.image { background: rgba(124, 58, 237, 0.1); color: var(--admin-purple); }
        .work-item-type.video { background: rgba(16, 185, 129, 0.1); color: var(--success-green); }
        .work-item-type.pdf { background: rgba(239, 68, 68, 0.1); color: var(--error-red); }
        .work-item-type.presentation { background: rgba(236, 72, 153, 0.1); color: #EC4899; }
        .work-item-type.document { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }

        .work-item-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* ===== Toast Notifications ===== */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: var(--z-modal);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .toast {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-lg);
            border-right: 4px solid var(--admin-purple);
            animation: slideInRight 0.3s ease;
            max-width: 350px;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast.success {
            border-right-color: var(--success-green);
        }

        .toast.error {
            border-right-color: var(--error-red);
        }

        .toast.warning {
            border-right-color: var(--warning-orange);
        }

        /* ===== Loading Overlay ===== */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal);
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border-color);
            border-top-color: var(--admin-purple);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===== Media Type Selection ===== */
        .media-type-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .media-type-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-normal);
            text-align: center;
        }

        .media-type-btn:hover {
            border-color: var(--admin-purple);
            transform: translateY(-2px);
        }

        .media-type-btn.active {
            border-color: var(--admin-purple);
            background: rgba(124, 58, 237, 0.1);
        }

        .media-type-btn i {
            font-size: 1.5rem;
        }

        .media-type-btn span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 992px) {
            .container {
                padding: 0 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .admin-tabs {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
            }
            
            .admin-header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .work-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }
            
            .login-card {
                padding: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .files-preview {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <?php if (!isLoggedIn()): ?>
    <!-- Login Screen -->
    <div class="login-screen">
        <div class="login-card">
            <div class="login-logo">
                <h1>لوحة التحكم الاحترافية</h1>
                <p>أكاديمية قادة المستقبل - إدارة الأعمال</p>
            </div>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           placeholder="أدخل اسم المستخدم">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" class="form-control" required
                           placeholder="أدخل كلمة المرور">
                </div>
                
                <?php if (isset($login_error)): ?>
                <div style="color: var(--error-red); margin-bottom: 1rem; text-align: center;">
                    <?php echo $login_error; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.html'">
                        <i class="fas fa-home"></i>
                        الصفحة الرئيسية
                    </button>
                    <button type="submit" name="login" value="1" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Panel -->
    <div class="admin-panel">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="container">
                <div class="admin-header-content">
                    <div class="admin-brand">
                        <h1><i class="fas fa-cog"></i> لوحة التحكم الاحترافية</h1>
                    </div>
                    <div class="admin-actions">
                        <span style="color: white;">مرحبًا، <?php echo $_SESSION['admin_username']; ?></span>
                        <button class="btn btn-secondary" onclick="window.location.href='?logout=1'">
                            <i class="fas fa-sign-out-alt"></i>
                            تسجيل الخروج
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner"></div>
            <div style="color: white; font-size: 1.1rem; font-weight: 600;" id="loadingText">جاري المعالجة...</div>
        </div>

        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>

        <!-- Admin Content -->
        <div class="admin-content">
            <div class="container">
                <!-- Admin Tabs -->
                <div class="admin-tabs">
                    <button class="admin-tab active" onclick="switchTab('add-work')">
                        <i class="fas fa-plus-circle"></i>
                        إضافة عمل جديد
                    </button>
                    <button class="admin-tab" onclick="switchTab('manage-works')">
                        <i class="fas fa-edit"></i>
                        إدارة الأعمال
                    </button>
                    <button class="admin-tab" onclick="switchTab('stats')">
                        <i class="fas fa-chart-bar"></i>
                        الإحصائيات
                    </button>
                </div>

                <!-- Add Work Section -->
                <div class="admin-section active" id="add-work-section">
                    <!-- File Upload Area -->
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <h3>اسحب وأفلت الملفات هنا</h3>
                            <p>أو انقر لاختيار الملفات</p>
                            <p style="font-size: 0.9rem; color: var(--text-muted);">
                                الحد الأقصى: <?php echo floor(MAX_FILE_SIZE / (1024 * 1024)); ?> ميجابايت لكل ملف
                            </p>
                        </div>
                        <input type="file" id="fileInput" multiple style="display: none;">
                        <div class="upload-progress" id="uploadProgress">
                            <div class="upload-progress-bar" id="uploadProgressBar"></div>
                        </div>
                    </div>

                    <!-- Uploaded Files Preview -->
                    <div id="filesPreviewContainer"></div>

                    <!-- Work Details Form -->
                    <form id="addWorkForm">
                        <div class="form-group">
                            <label class="form-label" for="workTitle">عنوان العمل *</label>
                            <input type="text" id="workTitle" class="form-control" required
                                   placeholder="أدخل عنوان العمل">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="workCategory">الفئة *</label>
                                <select id="workCategory" class="form-control" required>
                                    <option value="">اختر الفئة</option>
                                    <option value="ملفات المعلمين">ملفات المعلمين</option>
                                    <option value="الخطط العلاجية">الخطط العلاجية</option>
                                    <option value="عروض بوربوينت">عروض بوربوينت</option>
                                    <option value="ملفات PDF">ملفات PDF</option>
                                    <option value="مستندات وورد">مستندات وورد</option>
                                    <option value="بحوث جامعية ورسائل">بحوث جامعية ورسائل</option>
                                    <option value="حل منصات تعليمية">حل منصات تعليمية</option>
                                    <option value="ترجمة أكاديمية">ترجمة أكاديمية</option>
                                    <option value="تقارير وواجبات">تقارير وواجبات</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="workCountry">البلد *</label>
                                <select id="workCountry" class="form-control" required>
                                    <option value="">اختر البلد</option>
                                    <option value="saudi">المملكة العربية السعودية</option>
                                    <option value="uae">الإمارات العربية المتحدة</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">نوع الملف *</label>
                            <div class="media-type-selection" id="mediaTypeSelection">
                                <div class="media-type-btn image active" data-type="images">
                                    <i class="fas fa-image"></i>
                                    <span>صور</span>
                                </div>
                                <div class="media-type-btn video" data-type="videos">
                                    <i class="fas fa-video"></i>
                                    <span>فيديو</span>
                                </div>
                                <div class="media-type-btn pdf" data-type="pdf">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>PDF</span>
                                </div>
                                <div class="media-type-btn presentation" data-type="presentations">
                                    <i class="fas fa-file-powerpoint"></i>
                                    <span>عرض تقديمي</span>
                                </div>
                                <div class="media-type-btn document" data-type="documents">
                                    <i class="fas fa-file-word"></i>
                                    <span>مستند</span>
                                </div>
                            </div>
                            <input type="hidden" id="mediaType" value="images">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="workDate">التاريخ *</label>
                            <input type="date" id="workDate" class="form-control" required
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="workDescription">الوصف *</label>
                            <textarea id="workDescription" class="form-control form-textarea" required
                                      placeholder="أدخل وصفًا تفصيليًا للعمل" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">المميزات</label>
                            <div id="featuresContainer">
                                <div class="feature-item" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <input type="text" class="form-control" placeholder="أدخل ميزة">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addFeature()">
                                <i class="fas fa-plus"></i>
                                إضافة ميزة
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="workTags">الكلمات المفتاحية</label>
                            <input type="text" id="workTags" class="form-control"
                                   placeholder="كلمات مفتاحية مفصولة بفاصلة">
                            <small style="color: var(--text-muted);">افصل الكلمات بفاصلة</small>
                        </div>
                        
                        <div class="form-check" style="margin: 1rem 0;">
                            <input type="checkbox" id="workFeatured" class="form-check-input">
                            <label for="workFeatured" class="form-check-label">وضع علامة كمميز</label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                إعادة تعيين
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitWorkBtn">
                                <i class="fas fa-save"></i>
                                حفظ العمل
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Manage Works Section -->
                <div class="admin-section" id="manage-works-section">
                    <div class="works-list" id="worksListAdmin">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="admin-section" id="stats-section">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['total_works']; ?></div>
                            <div class="stat-label">إجمالي الأعمال</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['saudi_works']; ?></div>
                            <div class="stat-label">أعمال سعودية</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['uae_works']; ?></div>
                            <div class="stat-label">أعمال إماراتية</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['featured_works']; ?></div>
                            <div class="stat-label">أعمال مميزة</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['documents_count']; ?></div>
                            <div class="stat-label">ملفات ومستندات</div>
                        </div>
                    </div>
                    
                    <div class="stat-card" style="margin-top: 2rem;">
                        <div class="stat-value"><?php echo formatFileSize($statistics['storage_used']); ?></div>
                        <div class="stat-label">المساحة المستخدمة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // === متغيرات التطبيق ===
        let uploadedFiles = [];
        let currentTab = 'add-work';
        let editingWorkId = null;

        // === تهيئة التطبيق ===
        document.addEventListener('DOMContentLoaded', function() {
            if (<?php echo isLoggedIn() ? 'true' : 'false'; ?>) {
                initAdminPanel();
            }
        });

        function initAdminPanel() {
            setupUploadArea();
            setupMediaTypeSelection();
            setupEventListeners();
            loadWorks();
        }

        // === إدارة التبويبات ===
        function switchTab(tabId) {
            document.querySelectorAll('.admin-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.admin-section').forEach(section => section.classList.remove('active'));
            
            document.querySelectorAll('.admin-tab').forEach(tab => {
                if (tab.textContent.includes(tabId === 'add-work' ? 'إضافة عمل' : 
                                            tabId === 'manage-works' ? 'إدارة الأعمال' : 'الإحصائيات')) {
                    tab.classList.add('active');
                }
            });
            
            document.getElementById(`${tabId}-section`).classList.add('active');
            currentTab = tabId;
            
            if (tabId === 'manage-works') {
                loadWorks();
            }
        }

        // === إعداد منطقة رفع الملفات ===
        function setupUploadArea() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');
            
            // النقر لاختيار الملفات
            uploadArea.addEventListener('click', () => fileInput.click());
            
            // تغيير الملفات المختارة
            fileInput.addEventListener('change', handleFileSelect);
            
            // سحب وإفلات الملفات
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            function highlight() {
                uploadArea.classList.add('dragover');
            }
            
            function unhighlight() {
                uploadArea.classList.remove('dragover');
            }
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }
        }

        function handleFileSelect(e) {
            handleFiles(e.target.files);
        }

        async function handleFiles(files) {
            if (!files || files.length === 0) return;
            
            showLoading('جاري رفع الملفات...');
            
            const formData = new FormData();
            formData.append('action', 'upload_temp');
            
            for (let i = 0; i < files.length; i++) {
                formData.append('file[]', files[i]);
            }
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const results = await response.json();
                
                results.forEach(result => {
                    if (result.success) {
                        uploadedFiles.push({
                            original_name: result.original_name,
                            temp_name: result.temp_name,
                            size: result.size,
                            type: result.type,
                            preview: result.preview,
                            icon: result.icon
                        });
                        showToast('تم رفع الملف بنجاح', 'success');
                    } else {
                        showToast(result.error, 'error');
                    }
                });
                
                updateFilesPreview();
                document.getElementById('fileInput').value = '';
                
            } catch (error) {
                showToast('حدث خطأ أثناء رفع الملفات', 'error');
                console.error('Upload error:', error);
            } finally {
                hideLoading();
            }
        }

        function updateFilesPreview() {
            const container = document.getElementById('filesPreviewContainer');
            
            if (uploadedFiles.length === 0) {
                container.innerHTML = '';
                return;
            }
            
            let html = '<h3 style="margin-bottom: 1rem;">الملفات المرفوعة:</h3>';
            html += '<div class="files-preview">';
            
            uploadedFiles.forEach((file, index) => {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                
                html += `
                    <div class="file-item">
                        ${file.type === 'images' && file.preview !== file.icon ? 
                            `<img src="${file.preview}" alt="${file.original_name}" class="file-preview">` :
                            `<div class="file-icon"><i class="fas ${file.icon}"></i></div>`
                        }
                        <div class="file-info">
                            <div class="file-name" title="${file.original_name}">${file.original_name}</div>
                            <div class="file-size">${sizeMB} م.ب</div>
                        </div>
                        <button class="file-remove" onclick="removeFile(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        async function removeFile(index) {
            const file = uploadedFiles[index];
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_temp&file_name=${encodeURIComponent(file.temp_name)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    uploadedFiles.splice(index, 1);
                    updateFilesPreview();
                    showToast('تم حذف الملف', 'success');
                } else {
                    showToast('فشل في حذف الملف', 'error');
                }
            } catch (error) {
                showToast('حدث خطأ أثناء حذف الملف', 'error');
            }
        }

        // === إعداد اختيار نوع الوسائط ===
        function setupMediaTypeSelection() {
            const buttons = document.querySelectorAll('.media-type-btn');
            const mediaTypeInput = document.getElementById('mediaType');
            
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const type = this.getAttribute('data-type');
                    mediaTypeInput.value = type;
                    
                    // تحديث حقل اختيار الملفات
                    const fileInput = document.getElementById('fileInput');
                    const accept = getAcceptStringForType(type);
                    fileInput.setAttribute('accept', accept);
                });
            });
        }

        function getAcceptStringForType(type) {
            const acceptMap = {
                'images': 'image/*',
                'videos': 'video/*',
                'pdf': '.pdf',
                'presentations': '.ppt,.pptx',
                'documents': '.doc,.docx,.xls,.xlsx,.txt,.rtf',
                'archives': '.zip,.rar,.7z'
            };
            return acceptMap[type] || '*/*';
        }

        // === إدارة المميزات ===
        function addFeature() {
            const container = document.getElementById('featuresContainer');
            const featureCount = container.querySelectorAll('.feature-item').length;
            
            if (featureCount >= 10) {
                showToast('لا يمكن إضافة أكثر من 10 مميزات', 'warning');
                return;
            }
            
            const div = document.createElement('div');
            div.className = 'feature-item';
            div.style.display = 'flex';
            div.style.gap = '0.5rem';
            div.style.marginBottom = '0.5rem';
            div.innerHTML = `
                <input type="text" class="form-control" placeholder="أدخل ميزة">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
            
            div.querySelector('input').focus();
        }

        function removeFeature(button) {
            const container = document.getElementById('featuresContainer');
            const items = container.querySelectorAll('.feature-item');
            
            if (items.length > 1) {
                button.closest('.feature-item').remove();
            } else {
                button.previousElementSibling.value = '';
                button.previousElementSibling.focus();
            }
        }

        // === حفظ العمل ===
        async function saveWork() {
            // التحقق من البيانات
            const title = document.getElementById('workTitle').value.trim();
            const category = document.getElementById('workCategory').value;
            const country = document.getElementById('workCountry').value;
            const type = document.getElementById('mediaType').value;
            const date = document.getElementById('workDate').value;
            const description = document.getElementById('workDescription').value.trim();
            
            if (!title || !category || !country || !date || !description) {
                showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
                return;
            }
            
            if (uploadedFiles.length === 0) {
                showToast('يرجى رفع ملف واحد على الأقل', 'error');
                return;
            }
            
            showLoading('جاري حفظ العمل...');
            
            // جمع بيانات المميزات
            const features = [];
            document.querySelectorAll('#featuresContainer input[type="text"]').forEach(input => {
                if (input.value.trim()) {
                    features.push(input.value.trim());
                }
            });
            
            // جمع بيانات الكلمات المفتاحية
            const tags = document.getElementById('workTags').value
                .split(',')
                .map(tag => tag.trim())
                .filter(tag => tag);
            
            const workData = {
                title,
                category,
                country,
                type,
                date,
                description,
                features,
                tags,
                featured: document.getElementById('workFeatured').checked,
                files: uploadedFiles
            };
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_work&data=${encodeURIComponent(JSON.stringify(workData))}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    resetForm();
                    uploadedFiles = [];
                    updateFilesPreview();
                    
                    // الانتقال إلى قائمة الأعمال
                    setTimeout(() => {
                        switchTab('manage-works');
                    }, 1500);
                } else {
                    showToast(result.error, 'error');
                }
            } catch (error) {
                showToast('حدث خطأ أثناء حفظ العمل', 'error');
                console.error('Save error:', error);
            } finally {
                hideLoading();
            }
        }

        // === تحميل الأعمال ===
        async function loadWorks() {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_work&work_id=all'
                });
                
                // هذا مثال - سيتم تعديله ليتناسب مع بنية البيانات الفعلية
                const works = <?php echo json_encode($all_works); ?>;
                displayWorks(works);
                
            } catch (error) {
                console.error('Load error:', error);
            }
        }

        function displayWorks(works) {
            const container = document.getElementById('worksListAdmin');
            
            if (!works || works.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h3>لا توجد أعمال</h3>
                        <p>ابدأ بإضافة أول عمل</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            works.forEach(work => {
                const typeIcon = getWorkTypeIcon(work.type);
                const typeText = getWorkTypeText(work.type);
                const countryText = work.country === 'saudi' ? 'سعودي' : 'إماراتي';
                const countryIcon = work.country === 'saudi' ? 'fa-landmark' : 'fa-building';
                const date = new Date(work.date).toLocaleDateString('ar-SA');
                
                html += `
                    <div class="work-item">
                        <div class="work-item-info">
                            <h4>${work.title}</h4>
                            <div class="work-item-meta">
                                <span><i class="fas fa-tag"></i> ${work.category}</span>
                                <span><i class="fas ${countryIcon}"></i> ${countryText}</span>
                                <span><i class="far fa-calendar-alt"></i> ${date}</span>
                                <span class="work-item-type ${work.type}">
                                    <i class="fas ${typeIcon}"></i>
                                    ${typeText}
                                </span>
                                ${work.featured == 1 ? '<span><i class="fas fa-star"></i> مميز</span>' : ''}
                            </div>
                        </div>
                        <div class="work-item-actions">
                            <button class="btn btn-warning btn-sm" onclick="editWork(${work.id})">
                                <i class="fas fa-edit"></i>
                                تعديل
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteWorkConfirm(${work.id})">
                                <i class="fas fa-trash"></i>
                                حذف
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function getWorkTypeIcon(type) {
            const icons = {
                'images': 'fa-image',
                'videos': 'fa-video',
                'pdf': 'fa-file-pdf',
                'presentations': 'fa-file-powerpoint',
                'documents': 'fa-file-word'
            };
            return icons[type] || 'fa-file';
        }

        function getWorkTypeText(type) {
            const texts = {
                'images': 'صور',
                'videos': 'فيديو',
                'pdf': 'PDF',
                'presentations': 'عرض تقديمي',
                'documents': 'مستند'
            };
            return texts[type] || 'ملف';
        }

        // === تعديل العمل ===
        async function editWork(workId) {
            showLoading('جاري تحميل بيانات العمل...');
            editingWorkId = workId;
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get_work&work_id=${workId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const work = result.data;
                    
                    // تعبئة النموذج
                    document.getElementById('workTitle').value = work.title;
                    document.getElementById('workCategory').value = work.category;
                    document.getElementById('workCountry').value = work.country;
                    document.getElementById('workDate').value = work.date;
                    document.getElementById('workDescription').value = work.description;
                    document.getElementById('workFeatured').checked = work.featured == 1;
                    
                    if (work.tags) {
                        document.getElementById('workTags').value = work.tags.join(', ');
                    }
                    
                    // تعبئة المميزات
                    const featuresContainer = document.getElementById('featuresContainer');
                    featuresContainer.innerHTML = '';
                    
                    if (work.features && work.features.length > 0) {
                        work.features.forEach(feature => {
                            addFeature();
                            const inputs = featuresContainer.querySelectorAll('input');
                            if (inputs.length > 0) {
                                inputs[inputs.length - 1].value = feature;
                            }
                        });
                    } else {
                        addFeature();
                    }
                    
                    // تحديد نوع الوسائط
                    const typeButtons = document.querySelectorAll('.media-type-btn');
                    typeButtons.forEach(btn => btn.classList.remove('active'));
                    const activeBtn = document.querySelector(`[data-type="${work.type}"]`);
                    if (activeBtn) {
                        activeBtn.classList.add('active');
                        document.getElementById('mediaType').value = work.type;
                    }
                    
                    // تحديث زر الحفظ
                    document.getElementById('submitWorkBtn').innerHTML = '<i class="fas fa-save"></i> تحديث العمل';
                    
                    // الانتقال إلى تبويب الإضافة
                    switchTab('add-work');
                    
                    showToast('تم تحميل بيانات العمل للتعديل', 'success');
                } else {
                    showToast(result.error, 'error');
                }
            } catch (error) {
                showToast('حدث خطأ أثناء تحميل بيانات العمل', 'error');
            } finally {
                hideLoading();
            }
        }

        async function updateWork() {
            if (!editingWorkId) return;
            
            // جمع البيانات (مشابه لحفظ العمل)
            const title = document.getElementById('workTitle').value.trim();
            const category = document.getElementById('workCategory').value;
            const country = document.getElementById('workCountry').value;
            const type = document.getElementById('mediaType').value;
            const date = document.getElementById('workDate').value;
            const description = document.getElementById('workDescription').value.trim();
            
            if (!title || !category || !country || !date || !description) {
                showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
                return;
            }
            
            showLoading('جاري تحديث العمل...');
            
            const features = [];
            document.querySelectorAll('#featuresContainer input[type="text"]').forEach(input => {
                if (input.value.trim()) {
                    features.push(input.value.trim());
                }
            });
            
            const tags = document.getElementById('workTags').value
                .split(',')
                .map(tag => tag.trim())
                .filter(tag => tag);
            
            const workData = {
                title,
                category,
                country,
                type,
                date,
                description,
                features,
                tags,
                featured: document.getElementById('workFeatured').checked,
                files: uploadedFiles
            };
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_work&work_id=${editingWorkId}&data=${encodeURIComponent(JSON.stringify(workData))}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    resetForm();
                    uploadedFiles = [];
                    updateFilesPreview();
                    editingWorkId = null;
                    
                    setTimeout(() => {
                        switchTab('manage-works');
                    }, 1500);
                } else {
                    showToast(result.error, 'error');
                }
            } catch (error) {
                showToast('حدث خطأ أثناء تحديث العمل', 'error');
            } finally {
                hideLoading();
            }
        }

        // === حذف العمل ===
        function deleteWorkConfirm(workId) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "سيتم حذف هذا العمل نهائيًا ولا يمكن التراجع عن هذا الإجراء!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    showLoading('جاري حذف العمل...');
                    
                    try {
                        const response = await fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete_work&work_id=${workId}`
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(result.message, 'success');
                            loadWorks();
                        } else {
                            showToast(result.error, 'error');
                        }
                    } catch (error) {
                        showToast('حدث خطأ أثناء حذف العمل', 'error');
                    } finally {
                        hideLoading();
                    }
                }
            });
        }

        // === إعادة تعيين النموذج ===
        function resetForm() {
            document.getElementById('addWorkForm').reset();
            document.getElementById('workDate').value = '<?php echo date('Y-m-d'); ?>';
            document.getElementById('submitWorkBtn').innerHTML = '<i class="fas fa-save"></i> حفظ العمل';
            
            // إعادة تعيين نوع الوسائط
            const typeButtons = document.querySelectorAll('.media-type-btn');
            typeButtons.forEach(btn => btn.classList.remove('active'));
            const imageBtn = document.querySelector('[data-type="images"]');
            if (imageBtn) {
                imageBtn.classList.add('active');
                document.getElementById('mediaType').value = 'images';
            }
            
            // إعادة تعيين المميزات
            const featuresContainer = document.getElementById('featuresContainer');
            featuresContainer.innerHTML = `
                <div class="feature-item" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" class="form-control" placeholder="أدخل ميزة">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeFeature(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            editingWorkId = null;
        }

        // === إعداد مستمعي الأحداث ===
        function setupEventListeners() {
            // النموذج
            const form = document.getElementById('addWorkForm');
            const submitBtn = document.getElementById('submitWorkBtn');
            
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (editingWorkId) {
                    updateWork();
                } else {
                    saveWork();
                }
            });
        }

        // === دوال المساعدة ===
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-circle' : 
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
            
            const typeText = type === 'success' ? 'تم بنجاح' : 
                           type === 'error' ? 'خطأ' : 
                           type === 'warning' ? 'تحذير' : 'ملاحظة';
            
            toast.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas ${icon}" style="color: ${type === 'success' ? 'var(--success-green)' : 
                                                type === 'error' ? 'var(--error-red)' : 
                                                type === 'warning' ? 'var(--warning-orange)' : 'var(--info-blue)'}; 
                                                font-size: 1.2rem;"></i>
                    <div>
                        <div style="font-weight: 600; font-size: 0.95rem;">${typeText}</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">${message}</div>
                    </div>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        function showLoading(message = 'جاري المعالجة...') {
            document.getElementById('loadingText').textContent = message;
            document.getElementById('loadingOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }

        // إغلاق التنبيهات عند النقر عليها
        document.addEventListener('click', function(e) {
            if (e.target.closest('.toast')) {
                e.target.closest('.toast').style.opacity = '0';
                setTimeout(() => {
                    if (e.target.closest('.toast').parentNode) {
                        e.target.closest('.toast').remove();
                    }
                }, 300);
            }
        });
    </script>
</body>
</html>