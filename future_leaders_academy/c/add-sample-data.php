<?php
// add-sample-data.php
require_once 'config.php';
require_once 'functions.php';

// تحقق من أن المستخدم هو المدير (يمكنك إضافة تحقق أكثر أماناً)
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    die('غير مسموح الوصول. يرجى تسجيل الدخول كمدير.');
}

// بيانات تجريبية لإضافة أعمال
$sampleWorks = [
    [
        'title' => 'ملف الأداء الوظيفي المتكامل',
        'category' => 'ملفات المعلمين',
        'country' => 'saudi',
        'type' => 'pdf',
        'media_path' => '/uploads/performance.pdf',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/10/31/12/00/pdf-1015409_1280.png',
        'file_name' => 'performance.pdf',
        'file_size' => 2048000,
        'file_extension' => 'pdf',
        'description' => 'ملف أداء وظيفي متكامل مع جميع المستندات المطلوبة ومعايير التقويم الحديثة.',
        'features' => json_encode([
            'تخطيط الأهداف السنوية',
            'سجل المنجزات والإنجازات',
            'تقارير التقييم الذاتي',
            'خطط التحسين المهني',
            'وثائق الإنجازات المميزة'
        ]),
        'date' => '2024-01-15',
        'featured' => 1,
        'tags' => json_encode(['معلمين', 'تقويم', 'أداء', 'وظيفي']),
    ],
    [
        'title' => 'عرض تقديمي لدرس الرياضيات',
        'category' => 'عروض بوربوينت',
        'country' => 'saudi',
        'type' => 'presentation',
        'media_path' => '/uploads/math_presentation.pptx',
        'media_url' => 'https://cdn.pixabay.com/photo/2017/03/05/21/55/powerpoint-2120014_1280.png',
        'file_name' => 'math_presentation.pptx',
        'file_size' => 3072000,
        'file_extension' => 'pptx',
        'description' => 'عرض تقديمي تفاعلي لدرس الرياضيات للصف الرابع الابتدائي.',
        'features' => json_encode([
            'شرح تفاعلي للمفاهيم الرياضية',
            'تمارين وأنشطة تطبيقية',
            'ألعاب تعليمية تفاعلية',
            'تقويم إلكتروني للفهم',
            'موارد إضافية للتعلم'
        ]),
        'date' => '2024-01-20',
        'featured' => 0,
        'tags' => json_encode(['رياضيات', 'بوربوينت', 'عرض', 'تفاعلي']),
    ],
    [
        'title' => 'بحث جامعي في علم النفس التربوي',
        'category' => 'بحوث جامعية ورسائل',
        'country' => 'uae',
        'type' => 'document',
        'media_path' => '/uploads/research_paper.docx',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/12/04/14/05/code-1076536_1280.jpg',
        'file_name' => 'research_paper.docx',
        'file_size' => 1024000,
        'file_extension' => 'docx',
        'description' => 'بحث متكامل في مجال علم النفس التربوي مع المراجع الحديثة.',
        'features' => json_encode([
            'مقدمة وأهمية البحث',
            'الإطار النظري والدراسات السابقة',
            'منهجية البحث وأدواته',
            'تحليل النتائج والإحصائيات',
            'الاستنتاجات والتوصيات'
        ]),
        'date' => '2024-01-25',
        'featured' => 1,
        'tags' => json_encode(['بحث', 'جامعة', 'تربية', 'نفس']),
    ],
    [
        'title' => 'خطة علاجية للطلاب الضعاف في اللغة العربية',
        'category' => 'الخطط العلاجية',
        'country' => 'saudi',
        'type' => 'pdf',
        'media_path' => '/uploads/treatment_plan.pdf',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/10/31/12/00/pdf-1015409_1280.png',
        'file_name' => 'treatment_plan.pdf',
        'file_size' => 1536000,
        'file_extension' => 'pdf',
        'description' => 'خطة علاجية شاملة للطلاب الضعاف في مادة اللغة العربية.',
        'features' => json_encode([
            'تشخيص نقاط الضعف',
            'أنشطة علاجية متنوعة',
            'تمارين تقوية المهارات',
            'جدول زمني للتنفيذ',
            'أدوات تقييم التقدم'
        ]),
        'date' => '2024-02-01',
        'featured' => 0,
        'tags' => json_encode(['علاجي', 'لغة عربية', 'طلاب', 'ضعاف']),
    ],
    [
        'title' => 'حل واجبات منصة مدرستي',
        'category' => 'حل منصات تعليمية',
        'country' => 'saudi',
        'type' => 'document',
        'media_path' => '/uploads/madrasati_solutions.docx',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/12/04/14/05/code-1076536_1280.jpg',
        'file_name' => 'madrasati_solutions.docx',
        'file_size' => 2048000,
        'file_extension' => 'docx',
        'description' => 'حلول كاملة لواجبات منصة مدرستي للفصل الدراسي الثاني.',
        'features' => json_encode([
            'حلول جميع المواد',
            'شرح مفصل للخطوات',
            'نماذج إجابة معتمدة',
            'مراجعة نهائية',
            'نصائح للامتحانات'
        ]),
        'date' => '2024-02-05',
        'featured' => 1,
        'tags' => json_encode(['منصة', 'مدرستي', 'واجبات', 'حلول']),
    ],
    [
        'title' => 'ترجمة بحث علمي من الإنجليزية إلى العربية',
        'category' => 'ترجمة أكاديمية',
        'country' => 'uae',
        'type' => 'document',
        'media_path' => '/uploads/translation.docx',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/12/04/14/05/code-1076536_1280.jpg',
        'file_name' => 'translation.docx',
        'file_size' => 1024000,
        'file_extension' => 'docx',
        'description' => 'ترجمة دقيقة لبحث علمي في مجال التكنولوجيا من الإنجليزية إلى العربية.',
        'features' => json_encode([
            'ترجمة احترافية',
            'مراعاة السياق العلمي',
            'تدقيق لغوي',
            'توثيق المراجع',
            'تنسيق أكاديمي'
        ]),
        'date' => '2024-02-10',
        'featured' => 0,
        'tags' => json_encode(['ترجمة', 'أكاديمي', 'بحث', 'علمي']),
    ],
    [
        'title' => 'تقارير فصلية للإدارة المدرسية',
        'category' => 'تقارير وواجبات',
        'country' => 'saudi',
        'type' => 'pdf',
        'media_path' => '/uploads/school_reports.pdf',
        'media_url' => 'https://cdn.pixabay.com/photo/2015/10/31/12/00/pdf-1015409_1280.png',
        'file_name' => 'school_reports.pdf',
        'file_size' => 2560000,
        'file_extension' => 'pdf',
        'description' => 'مجموعة تقارير فصلية جاهزة للإدارة المدرسية والمعلمين.',
        'features' => json_encode([
            'تقارير طلابية',
            'تقارير أداء المعلمين',
            'تقارير الأنشطة',
            'تقارير الجودة',
            'نماذج جاهزة للطباعة'
        ]),
        'date' => '2024-02-15',
        'featured' => 1,
        'tags' => json_encode(['تقارير', 'إدارة', 'مدرسية', 'فصلية']),
    ],
    [
        'title' => 'فيديو تعليمي لشرح المنهج',
        'category' => 'عروض بوربوينت',
        'country' => 'uae',
        'type' => 'video',
        'media_path' => '/uploads/educational_video.mp4',
        'media_url' => 'https://cdn.pixabay.com/video/2023/11/22/190184-872792007_large.mp4',
        'file_name' => 'educational_video.mp4',
        'file_size' => 10485760,
        'file_extension' => 'mp4',
        'description' => 'فيديو تعليمي مميز لشرح منهج الرياضيات للصف الخامس.',
        'features' => json_encode([
            'شرح مرئي ممتع',
            'رسوم متحركة',
            'أمثلة تطبيقية',
            'تمارين تفاعلية',
            'ملخص الدرس'
        ]),
        'date' => '2024-02-20',
        'featured' => 1,
        'tags' => json_encode(['فيديو', 'تعليمي', 'رياضيات', 'شرح']),
    ]
];

$conn = getDBConnection();
$addedCount = 0;

foreach ($sampleWorks as $work) {
    // تحقق من عدم وجود العمل مسبقاً (بناءً على العنوان)
    $checkSql = "SELECT id FROM works WHERE title = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $work['title']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows == 0) {
        // إعداد استعلام الإدخال
        $sql = "INSERT INTO works (title, category, country, type, media_path, media_url, file_name, file_size, file_extension, description, features, date, featured, tags) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssissssis",
            $work['title'],
            $work['category'],
            $work['country'],
            $work['type'],
            $work['media_path'],
            $work['media_url'],
            $work['file_name'],
            $work['file_size'],
            $work['file_extension'],
            $work['description'],
            $work['features'],
            $work['date'],
            $work['featured'],
            $work['tags']
        );
        
        if ($stmt->execute()) {
            $addedCount++;
        }
        $stmt->close();
    }
    $checkStmt->close();
}

$conn->close();

echo "<h1>تمت إضافة البيانات التجريبية بنجاح!</h1>";
echo "<p>تم إضافة $addedCount عمل جديد إلى قاعدة البيانات.</p>";
echo "<a href='index.php'>العودة إلى الصفحة الرئيسية</a> | ";
echo "<a href='admin-panel.php'>الذهاب إلى لوحة التحكم</a>";
?>