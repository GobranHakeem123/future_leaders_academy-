<?php
// admin-panel.php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}

// جلب الإحصائيات
$conn = getDBConnection();
$stats = [];
$result = $conn->query("SELECT * FROM statistics LIMIT 1");
if ($result && $result->num_rows > 0) {
    $stats = $result->fetch_assoc();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - FUTURE LEADERS ACADEMY</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* أنماط لوحة التحكم */
        body {
            font-family: 'Noto Kufi Arabic', sans-serif;
            background: #f8f9fc;
            margin: 0;
            padding: 0;
        }
        
        .admin-header {
            background: #1a1a2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav {
            background: #2d2d44;
            padding: 1rem 2rem;
        }
        
        .admin-nav ul {
            list-style: none;
            display: flex;
            gap: 1rem;
            margin: 0;
            padding: 0;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .admin-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 1rem 0;
            color: #333;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #C9A227;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-primary {
            background: #C9A227;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>لوحة تحكم الأكاديمية</h1>
        <form action="admin-logout.php" method="POST">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </button>
        </form>
    </header>
    
    <nav class="admin-nav">
        <ul>
            <li><a href="admin-panel.php"><i class="fas fa-tachometer-alt"></i> الإحصائيات</a></li>
            <li><a href="admin-works.php"><i class="fas fa-briefcase"></i> إدارة الأعمال</a></li>
            <li><a href="admin-add-work.php"><i class="fas fa-plus"></i> إضافة عمل جديد</a></li>
        </ul>
    </nav>
    
    <main class="admin-content">
        <h2>الإحصائيات العامة</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>إجمالي الملفات</h3>
                <div class="stat-value"><?= $stats['total_files'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <h3>إجمالي المساحة</h3>
                <div class="stat-value"><?= formatBytes($stats['total_size'] ?? 0) ?></div>
            </div>
            
            <div class="stat-card">
                <h3>عدد الصور</h3>
                <div class="stat-value"><?= $stats['images_count'] ?? 0 ?></div>
            </div>
            
            <div class="stat-card">
                <h3>عدد الفيديوهات</h3>
                <div class="stat-value"><?= $stats['videos_count'] ?? 0 ?></div>
            </div>
        </div>
        
        <div class="table-container">
            <h3>الأعمال المضافة مؤخرًا</h3>
            <table>
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>الفئة</th>
                        <th>البلد</th>
                        <th>النوع</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = getDBConnection();
                    $result = $conn->query("SELECT * FROM works ORDER BY created_at DESC LIMIT 10");
                    
                    while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= $row['country'] == 'saudi' ? 'سعودي' : 'إماراتي' ?></td>
                        <td><?= Helper::getTypeName(Helper::getFileType($row['file_extension'])) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="admin-edit-work.php?id=<?= $row['id'] ?>" class="btn btn-primary">تعديل</a>
                            <a href="admin-delete-work.php?id=<?= $row['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟')">حذف</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
        // دالة لتنسيق حجم الملف
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 بايت';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>