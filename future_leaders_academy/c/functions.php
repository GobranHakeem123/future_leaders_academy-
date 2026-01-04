<?php
// functions.php
require_once 'config.php';

class Helper {
    
    // جلب جميع الأعمال
    public static function getAllWorks($filter = 'all', $category = 'all') {
        $conn = getDBConnection();
        
        $sql = "SELECT * FROM works WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($filter !== 'all') {
            $sql .= " AND country = ?";
            $params[] = $filter;
            $types .= "s";
        }
        
        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $works = [];
        
        while ($row = $result->fetch_assoc()) {
            // تحويل الحقول JSON
            if (isset($row['features'])) {
                $row['features'] = json_decode($row['features'], true) ?: [];
            }
            if (isset($row['tags'])) {
                $row['tags'] = json_decode($row['tags'], true) ?: [];
            }
            $works[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $works;
    }
    
    // جلب جميع الفئات
    public static function getAllCategories() {
        $conn = getDBConnection();
        
        // جلب الفئات الفريدة من الأعمال
        $sql = "SELECT DISTINCT category FROM works ORDER BY category";
        $result = $conn->query($sql);
        
        $categories = ['all'];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        $conn->close();
        return $categories;
    }
    
    // تنسيق التاريخ
    public static function formatDate($dateString, $format = 'ar') {
        if ($format === 'ar') {
            $months = [
                'January' => 'يناير',
                'February' => 'فبراير',
                'March' => 'مارس',
                'April' => 'أبريل',
                'May' => 'مايو',
                'June' => 'يونيو',
                'July' => 'يوليو',
                'August' => 'أغسطس',
                'September' => 'سبتمبر',
                'October' => 'أكتوبر',
                'November' => 'نوفمبر',
                'December' => 'ديسمبر'
            ];
            
            $date = new DateTime($dateString);
            $month = $date->format('F');
            $day = $date->format('j');
            $year = $date->format('Y');
            
            return $day . ' ' . $months[$month] . ' ' . $year;
        }
        
        return date('Y-m-d', strtotime($dateString));
    }
    
    // تحديد نوع الملف
    public static function getFileType($extension) {
        global $allowed_types;
        
        foreach ($allowed_types as $type => $extensions) {
            if (in_array(strtolower($extension), $extensions)) {
                return $type;
            }
        }
        
        return 'unknown';
    }
    
    // الحصول على أيقونة الملف
    public static function getFileIcon($fileType) {
        $icons = [
            'image' => 'fa-file-image',
            'video' => 'fa-file-video',
            'pdf' => 'fa-file-pdf',
            'word' => 'fa-file-word',
            'excel' => 'fa-file-excel',
            'powerpoint' => 'fa-file-powerpoint',
            'text' => 'fa-file-alt',
            'archive' => 'fa-file-archive'
        ];
        
        return $icons[$fileType] ?? 'fa-file';
    }
    
    // الحصول على اسم النوع بالعربية
    public static function getTypeName($fileType) {
        $names = [
            'image' => 'صورة',
            'video' => 'فيديو',
            'pdf' => 'PDF',
            'word' => 'مستند وورد',
            'excel' => 'جدول إكسل',
            'powerpoint' => 'عرض تقديمي',
            'text' => 'نص',
            'archive' => 'مضغوط'
        ];
        
        return $names[$fileType] ?? 'ملف';
    }
    
    // زيادة عدد المشاهدات
    public static function incrementViews($workId) {
        $conn = getDBConnection();
        
        $sql = "UPDATE works SET views_count = views_count + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $workId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
    // زيادة عدد التحميلات
    public static function incrementDownloads($workId) {
        $conn = getDBConnection();
        
        $sql = "UPDATE works SET downloads_count = downloads_count + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $workId);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    
    // جلب عمل بواسطة ID
    public static function getWorkById($id) {
        $conn = getDBConnection();
        
        $sql = "SELECT * FROM works WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $work = $result->fetch_assoc();
        
        if ($work) {
            if (isset($work['features'])) {
                $work['features'] = json_decode($work['features'], true) ?: [];
            }
            if (isset($work['tags'])) {
                $work['tags'] = json_decode($work['tags'], true) ?: [];
            }
        }
        
        $stmt->close();
        $conn->close();
        
        return $work;
    }
    
    // الحصول على رابط واتساب
    public static function getWhatsAppUrl($message = null) {
        $defaultMessage = 'مرحبًا ، أرغب في الحصول على خدمة مخصصة';
        $number = '971553353672';
        
        $text = $message ? urlencode($message) : urlencode($defaultMessage);
        return "https://wa.me/{$number}?text={$text}";
    }

// functions.php

    // ... باقي الدوال الموجودة ...
    
    /**
     * دالة جديدة لاستخراج الصورة الأولى من الملفات
     */
    public static function extractFirstPageImage($filePath, $workId) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $outputDir = 'uploads/thumbnails/';
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $thumbnailPath = $outputDir . 'work_' . $workId . '_preview.jpg';
        
        // إذا كان الملف موجود بالفعل، أرجع مساره
        if (file_exists($thumbnailPath) && filesize($thumbnailPath) > 0) {
            return $thumbnailPath;
        }
        
        // بناءً على نوع الملف، استخدم الأداة المناسبة
        switch ($extension) {
            case 'pdf':
                return self::extractPdfFirstPage($filePath, $thumbnailPath);
                
            case 'docx':
                return self::extractDocxFirstPage($filePath, $thumbnailPath);
                
            case 'pptx':
            case 'ppt':
                return self::extractPptxFirstPage($filePath, $thumbnailPath);
                
            case 'doc':
                return self::extractDocFirstPage($filePath, $thumbnailPath);
                
            default:
                return false;
        }
    }
    
    /**
     * استخراج أول صفحة من PDF
     */
    private static function extractPdfFirstPage($pdfPath, $outputPath) {
        if (!file_exists($pdfPath)) {
            return false;
        }
        
        // استخدم Imagick إذا كان مثبتاً
        if (extension_loaded('imagick')) {
            try {
                $imagick = new Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($pdfPath . '[0]');
                $imagick->setImageFormat('jpg');
                $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(85);
                $imagick->writeImage($outputPath);
                $imagick->clear();
                $imagick->destroy();
                
                return $outputPath;
            } catch (Exception $e) {
                error_log("Imagick Error: " . $e->getMessage());
            }
        }
        
        // بديل: استخدم Ghostscript
        if (self::commandExists('gs')) {
            $command = sprintf(
                'gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 ' .
                '-r150 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -dJPEGQ=85 ' .
                '-sOutputFile="%s" "%s"',
                $outputPath,
                $pdfPath
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($outputPath)) {
                return $outputPath;
            }
        }
        
        return false;
    }
    
    /**
     * استخراج أول صفحة من DOCX
     */
    private static function extractDocxFirstPage($docxPath, $outputPath) {
        if (!file_exists($docxPath)) {
            return false;
        }
        
        // استخدم LibreOffice لتحويل DOCX إلى PDF ثم استخراج الصفحة الأولى
        if (self::commandExists('libreoffice')) {
            $tempDir = sys_get_temp_dir() . '/docx_temp_' . uniqid();
            mkdir($tempDir, 0755, true);
            
            $tempPdf = $tempDir . '/temp.pdf';
            
            // تحويل DOCX إلى PDF
            $command = sprintf(
                'libreoffice --headless --convert-to pdf --outdir "%s" "%s"',
                dirname($tempPdf),
                $docxPath
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempPdf)) {
                // استخراج أول صفحة من PDF الناتج
                $result = self::extractPdfFirstPage($tempPdf, $outputPath);
                
                // تنظيف الملفات المؤقتة
                unlink($tempPdf);
                rmdir($tempDir);
                
                return $result;
            }
            
            // تنظيف إذا فشل التحويل
            if (file_exists($tempPdf)) {
                unlink($tempPdf);
            }
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
        }
        
        return false;
    }
    
    /**
     * استخراج أول شريحة من PPTX
     */
    private static function extractPptxFirstPage($pptxPath, $outputPath) {
        if (!file_exists($pptxPath)) {
            return false;
        }
        
        // استخدم LibreOffice لتحويل PPTX إلى PDF ثم استخراج الصفحة الأولى
        if (self::commandExists('libreoffice')) {
            $tempDir = sys_get_temp_dir() . '/pptx_temp_' . uniqid();
            mkdir($tempDir, 0755, true);
            
            $tempPdf = $tempDir . '/temp.pdf';
            
            // تحويل PPTX إلى PDF
            $command = sprintf(
                'libreoffice --headless --convert-to pdf --outdir "%s" "%s"',
                dirname($tempPdf),
                $pptxPath
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempPdf)) {
                // استخراج أول صفحة من PDF الناتج
                $result = self::extractPdfFirstPage($tempPdf, $outputPath);
                
                // تنظيف الملفات المؤقتة
                unlink($tempPdf);
                rmdir($tempDir);
                
                return $result;
            }
            
            // تنظيف إذا فشل التحويل
            if (file_exists($tempPdf)) {
                unlink($tempPdf);
            }
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
        }
        
        return false;
    }
    
    /**
     * استخراج أول صفحة من DOC (النسخ القديمة)
     */
    private static function extractDocFirstPage($docPath, $outputPath) {
        // نفس طريقة DOCX ولكن لملفات .doc القديمة
        return self::extractDocxFirstPage($docPath, $outputPath);
    }
    
    /**
     * التحقق من وجود أمر في النظام
     */
    private static function commandExists($command) {
        $which = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'where' : 'which';
        exec("$which $command", $output, $returnCode);
        return $returnCode === 0;
    }
    
    /**
     * دالة جديدة للحصول على صورة معاينة للعمل
     */
    public static function getWorkThumbnail($workId, $filePath, $fileExtension) {
        $extension = strtolower($fileExtension);
        
        // قائمة بالامتدادات التي يمكن استخراج معاينات لها
        $supportedExtensions = ['pdf', 'docx', 'pptx', 'ppt', 'doc'];
        
        if (in_array($extension, $supportedExtensions) && file_exists($filePath)) {
            $thumbnail = self::extractFirstPageImage($filePath, $workId);
            if ($thumbnail) {
                return $thumbnail;
            }
        }
        
        // إذا لم نستطع استخراج معاينة، أرجع أيقونة مناسبة
        return self::getFileTypeIcon($extension);
    }
    
    /**
     * دالة للحصول على أيقونة النوع
     */
    public static function getFileTypeIcon($extension) {
        $icons = [
            'pdf' => 'fa-file-pdf',
            'docx' => 'fa-file-word',
            'doc' => 'fa-file-word',
            'pptx' => 'fa-file-powerpoint',
            'ppt' => 'fa-file-powerpoint',
            'xlsx' => 'fa-file-excel',
            'xls' => 'fa-file-excel',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'mp4' => 'fa-file-video',
            'avi' => 'fa-file-video',
            'mov' => 'fa-file-video'
        ];
        
        return $icons[strtolower($extension)] ?? 'fa-file';
    }
    
    /**
     * دالة للحصول على اسم النوع بالعربية
     */
    public static function getFileTypeName($extension) {
        $names = [
            'pdf' => 'ملف PDF',
            'docx' => 'مستند Word',
            'doc' => 'مستند Word',
            'pptx' => 'عرض تقديمي',
            'ppt' => 'عرض تقديمي',
            'xlsx' => 'جدول بيانات',
            'xls' => 'جدول بيانات',
            'zip' => 'ملف مضغوط',
            'rar' => 'ملف مضغوط',
            'jpg' => 'صورة',
            'jpeg' => 'صورة',
            'png' => 'صورة',
            'gif' => 'صورة',
            'mp4' => 'فيديو',
            'avi' => 'فيديو',
            'mov' => 'فيديو'
        ];
        
        return $names[strtolower($extension)] ?? 'ملف';
    }
}

?>