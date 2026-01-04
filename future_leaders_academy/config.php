<?php
// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'future_leaders_academy');
define('DB_USER', 'root');
define('DB_PASS', '');

// إعدادات التطبيق
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
define('UPLOAD_DIR', 'c/uploads/');
define('MAX_FILE_SIZE', 500 * 1024 * 1024); // 500MB

// الأنواع المسموح بها (باللغة العربية للتطابق مع القائمة المنسدلة)
$allowedTypesArabic = [
    'صورة' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'],
    'فيديو' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
    'PDF' => ['pdf'],
    'Word' => ['doc', 'docx'],
    'Excel' => ['xls', 'xlsx'],
    'PowerPoint' => ['ppt', 'pptx'],
    'نص' => ['txt', 'rtf'],
    'أرشيف' => ['zip', 'rar'],
    'مستند' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'] // نوع عام للمستندات
];

// أحجام قصوى لكل نوع (بالبايت)
$maxSizes = [
    'صورة' => 10 * 1024 * 1024, // 10MB
    'فيديو' => 500 * 1024 * 1024, // 500MB
    'PDF' => 100 * 1024 * 1024, // 100MB
    'Word' => 50 * 1024 * 1024, // 50MB
    'Excel' => 50 * 1024 * 1024, // 50MB
    'PowerPoint' => 50 * 1024 * 1024, // 50MB
    'نص' => 1 * 1024 * 1024, // 1MB
    'أرشيف' => 200 * 1024 * 1024, // 200MB
    'مستند' => 100 * 1024 * 1024 // 100MB
];

// MIME types لكل امتداد
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'bmp' => 'image/bmp',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'mp4' => 'video/mp4',
    'avi' => 'video/x-msvideo',
    'mov' => 'video/quicktime',
    'wmv' => 'video/x-ms-wmv',
    'flv' => 'video/x-flv',
    'mkv' => 'video/x-matroska',
    'webm' => 'video/webm',
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'txt' => 'text/plain',
    'rtf' => 'application/rtf',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed'
];

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// دالة لرفع أي نوع من الملفات
function uploadFile($file, $type = 'auto') {
    global $allowedTypesArabic, $maxSizes, $mimeTypes;
    
    $targetDir = UPLOAD_DIR;
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // الحصول على معلومات الملف
    $fileName = basename($file['name']);
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileError = $file['error'];
    
    // الحصول على امتداد الملف
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // إنشاء اسم فريد للملف
    $uniqueName = time() . '_' . uniqid() . '.' . $fileExtension;
    
    // تحديد نوع الملف تلقائياً إذا لم يتم تحديده
    if ($type === 'auto' || $type === '') {
        $detectedType = detectFileType($fileExtension);
        $type = $detectedType;
    }
    
    // التحقق من وجود أخطاء في الرفع
    if ($fileError !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => getUploadErrorMessage($fileError)
        ];
    }
    
    // التحقق من حجم الملف
    if ($fileSize > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'error' => 'حجم الملف كبير جداً. الحد الأقصى 500MB.'
        ];
    }
    
    // التحقق من أن النوع موجود في القائمة المسموح بها
    if (!array_key_exists($type, $allowedTypesArabic)) {
        return [
            'success' => false,
            'error' => "نوع الملف '$type' غير معروف."
        ];
    }
    
    // التحقق من أن الامتداد مسموح به لهذا النوع
    if (!in_array($fileExtension, $allowedTypesArabic[$type])) {
        return [
            'success' => false,
            'error' => "امتداد الملف '.$fileExtension' غير مسموح به للنوع '$type'."
        ];
    }
    
    // التحقق من الحجم المسموح به للنوع المحدد
    $typeMaxSize = isset($maxSizes[$type]) ? $maxSizes[$type] : MAX_FILE_SIZE;
    if ($fileSize > $typeMaxSize) {
        $maxSizeFormatted = formatFileSize($typeMaxSize);
        return [
            'success' => false,
            'error' => "حجم الملف كبير جداً للنوع '$type'. الحد الأقصى المسموح به هو $maxSizeFormatted."
        ];
    }
    
    // التحقق من MIME type
    if (isset($mimeTypes[$fileExtension])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);
        
        if ($detectedMime !== $mimeTypes[$fileExtension]) {
            return [
                'success' => false,
                'error' => 'نوع الملف غير صالح أو تم التلاعب به.'
            ];
        }
    }
    
    // تحديد المجلد الهدف بناءً على نوع الملف
    $subDir = getSubDirectory($type);
    $fullTargetDir = $targetDir . $subDir . '/';
    
    // إنشاء المجلد الفرعي إذا لم يكن موجوداً
    if (!file_exists($fullTargetDir)) {
        mkdir($fullTargetDir, 0777, true);
    }
    
    // المسار الكامل للملف
    $targetFile = $fullTargetDir . $uniqueName;
    
    // محاولة رفع الملف
    if (move_uploaded_file($fileTmp, $targetFile)) {
        // استخدام المسار النسبي بدلاً من المطلق
        $fileUrl = '/' . $targetFile;
        
        // إذا كان نوع الصورة GIF، تحويله إلى PNG لتجنب مشاكل الأمان
        if ($fileExtension === 'gif') {
            convertGifToPng($targetFile);
            $fileExtension = 'png';
            $uniqueName = str_replace('.gif', '.png', $uniqueName);
            $targetFile = str_replace('.gif', '.png', $targetFile);
        }
        
        return [
            'success' => true,
            'file_name' => $fileName,
            'file_path' => $targetFile,
            'file_url' => $fileUrl,
            'file_size' => $fileSize,
            'file_extension' => $fileExtension,
            'file_type' => $type, // سيتم تخزينه بالعربية
            'unique_name' => $uniqueName
        ];
    } else {
        return [
            'success' => false,
            'error' => 'حدث خطأ أثناء رفع الملف.'
        ];
    }
}

// دالة للكشف عن نوع الملف تلقائياً (بالعربية)
function detectFileType($extension) {
    global $allowedTypesArabic;
    
    $extension = strtolower($extension);
    
    foreach ($allowedTypesArabic as $typeName => $extensions) {
        if (in_array($extension, $extensions)) {
            return $typeName;
        }
    }
    
    return 'مستند'; // نوع افتراضي
}

// دالة للحصول على المجلد الفرعي المناسب
function getSubDirectory($type) {
    // تعيين المجلدات حسب النوع العربي
    $mapping = [
        'صورة' => 'images',
        'فيديو' => 'videos',
        'PDF' => 'documents',
        'Word' => 'documents',
        'Excel' => 'documents',
        'PowerPoint' => 'documents',
        'نص' => 'documents',
        'أرشيف' => 'archives',
        'مستند' => 'documents'
    ];
    
    return $mapping[$type] ?? 'others';
}

// دالة للتحقق من أن الملف آمن
function isFileSafe($filePath) {
    global $mimeTypes;
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // التحقق من أن MIME type مسموح به
    return in_array($detectedMime, array_values($mimeTypes));
}

// دالة تحويل GIF إلى PNG لأسباب أمنية
function convertGifToPng($gifPath) {
    if (file_exists($gifPath) && function_exists('imagecreatefromgif')) {
        $image = imagecreatefromgif($gifPath);
        $pngPath = str_replace('.gif', '.png', $gifPath);
        imagepng($image, $pngPath);
        imagedestroy($image);
        unlink($gifPath); // حذف ملف GIF الأصلي
        return true;
    }
    return false;
}

// دالة للحصول على رسالة خطأ الرفع
function getUploadErrorMessage($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'حجم الملف أكبر من المسموح به في إعدادات الخادم.',
        UPLOAD_ERR_FORM_SIZE => 'حجم الملف أكبر من المسموح به في النموذج.',
        UPLOAD_ERR_PARTIAL => 'تم رفع الملف جزئياً فقط.',
        UPLOAD_ERR_NO_FILE => 'لم يتم اختيار أي ملف.',
        UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت مفقود.',
        UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص.',
        UPLOAD_ERR_EXTENSION => 'تم إيقاف رفع الملف بواسطة إضافة PHP.'
    ];
    
    return $errors[$errorCode] ?? 'حدث خطأ غير معروف أثناء رفع الملف.';
}

// دالة لحذف الملف
function deleteFile($filePath) {
    if (file_exists($filePath) && is_file($filePath)) {
        // تحديث الإحصائيات قبل الحذف
        updateStatisticsOnDelete($filePath);
        return unlink($filePath);
    }
    return false;
}

// دالة لتحديث الإحصائيات بعد الحذف
function updateStatisticsOnDelete($filePath) {
    global $pdo;
    
    try {
        $fileSize = filesize($filePath);
        $stmt = $pdo->prepare("SELECT type FROM works WHERE media_path = ?");
        $stmt->execute([$filePath]);
        $work = $stmt->fetch();
        
        if ($work) {
            $type = $work['type'];
            $updateSql = "UPDATE statistics SET 
                         total_files = total_files - 1,
                         total_size = total_size - ?";
            
            // تحويل النوع العربي إلى تصنيف إنجليزي للإحصائيات
            if (in_array($type, ['صورة'])) {
                $updateSql .= ", images_count = images_count - 1";
            } elseif (in_array($type, ['فيديو'])) {
                $updateSql .= ", videos_count = videos_count - 1";
            } else {
                $updateSql .= ", documents_count = documents_count - 1";
            }
            
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([$fileSize]);
        }
    } catch(PDOException $e) {
        // تجاهل الأخطاء في تحديث الإحصائيات
    }
}

// دالة لتحديث الإحصائيات بعد الإضافة
function updateStatisticsOnAdd($fileSize, $type) {
    global $pdo;
    
    try {
        $updateSql = "UPDATE statistics SET 
                     total_files = total_files + 1,
                     total_size = total_size + ?";
        
        // تحويل النوع العربي إلى تصنيف إنجليزي للإحصائيات
        if (in_array($type, ['صورة'])) {
            $updateSql .= ", images_count = images_count + 1";
        } elseif (in_array($type, ['فيديو'])) {
            $updateSql .= ", videos_count = videos_count + 1";
        } else {
            $updateSql .= ", documents_count = documents_count + 1";
        }
        
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$fileSize]);
    } catch(PDOException $e) {
        // تجاهل الأخطاء في تحديث الإحصائيات
    }
}

// دالة لتنسيق حجم الملف
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// دالة للحصول على أيقونة الملف حسب النوع (بالعربية)
function getFileIcon($type, $extension = '') {
    $icons = [
        'صورة' => 'fas fa-image',
        'فيديو' => 'fas fa-video',
        'PDF' => 'fas fa-file-pdf',
        'Word' => 'fas fa-file-word',
        'Excel' => 'fas fa-file-excel',
        'PowerPoint' => 'fas fa-file-powerpoint',
        'نص' => 'fas fa-file-alt',
        'أرشيف' => 'fas fa-file-archive',
        'مستند' => 'fas fa-file'
    ];
    
    if (isset($icons[$type])) {
        return $icons[$type];
    }
    
    return 'fas fa-file';
}

// دالة لعرض معاينة الملف
    function getFilePreview($url, $type, $title = '') {
        switch ($type) {
            case 'صورة':
                return '<img src="' . $url . '" alt="' . htmlspecialchars($title) . '" class="file-preview img-fluid" style="max-height: 300px;">';
            
            case 'فيديو':
                return '<video controls class="file-preview" style="max-width: 100%; max-height: 300px;">
                        <source src="' . $url . '" type="video/mp4">
                        متصفحك لا يدعم تشغيل الفيديو.
                        </video>';
            
            case 'PDF':
                return '<iframe src="' . $url . '" class="file-preview" style="width: 100%; height: 400px;" frameborder="0"></iframe>';
            
            default:
                return '<div class="file-icon-preview text-center">
                        <i class="' . getFileIcon($type) . ' fa-5x"></i>
                        <p class="mt-2">معاينة غير متاحة لهذا النوع من الملفات</p>
                        </div>';
        }
}

// دالة للحصول على قائمة أنواع الملفات المسموح بها للاستخدام في HTML
function getAllowedFileTypes() {
    global $allowedTypesArabic;
    
    $allExtensions = [];
    foreach ($allowedTypesArabic as $extensions) {
        $allExtensions = array_merge($allExtensions, $extensions);
    }
    
    // إضافة نقطة قبل كل امتداد
    $allExtensions = array_unique($allExtensions);
    $extensionsWithDot = array_map(function($ext) {
        return '.' . $ext;
    }, $allExtensions);
    
    return implode(',', $extensionsWithDot);
}

// دالة للحصول على اسم النوع بالعربية
function getFileTypeName($type) {
    $names = [
        'صورة' => 'صورة',
        'فيديو' => 'فيديو',
        'PDF' => 'PDF',
        'Word' => 'Word',
        'Excel' => 'Excel',
        'PowerPoint' => 'PowerPoint',
        'نص' => 'نص',
        'أرشيف' => 'أرشيف',
        'مستند' => 'مستند'
    ];
    
    return $names[$type] ?? $type;
}

// دالة لإعادة تهيئة قاعدة البيانات إذا لزم الأمر
function initializeDatabase() {
    global $pdo;
    
    // التحقق من وجود جدول الإحصائيات
    $stmt = $pdo->query("SHOW TABLES LIKE 'statistics'");
    if (!$stmt->fetch()) {
        // إنشاء جدول الإحصائيات إذا لم يكن موجوداً
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS statistics (
                id INT PRIMARY KEY AUTO_INCREMENT,
                total_files INT DEFAULT 0,
                total_size BIGINT DEFAULT 0,
                images_count INT DEFAULT 0,
                videos_count INT DEFAULT 0,
                documents_count INT DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // إدخال سجل أولي
        $pdo->exec("INSERT INTO statistics (total_files) VALUES (0)");
    }
}

// استدعاء تهيئة قاعدة البيانات
initializeDatabase();
?>