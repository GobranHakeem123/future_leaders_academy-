<?php



// الاتصال بقاعدة البيانات
$host = 'localhost';
$dbname = 'future_leaders_academy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// إنشاء الجدول إذا لم يكن موجوداً
$create_table = "
CREATE TABLE IF NOT EXISTS whatsapp_numbers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_code VARCHAR(5) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    country_name VARCHAR(50) NOT NULL,
    country_flag VARCHAR(10) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

$pdo->exec($create_table);

// إضافة بيانات تجريبية إذا كان الجدول فارغاً
$check_data = $pdo->query("SELECT COUNT(*) as count FROM whatsapp_numbers")->fetch();
if ($check_data['count'] == 0) {
    $insert_sample = "
    INSERT INTO whatsapp_numbers (country_code, phone_number, country_name, country_flag) 
    VALUES 
        ('+966', '500000000', 'السعودية', '🇸🇦'),
        ('+971', '553353672', 'الإمارات', '🇦🇪');
    ";
    $pdo->exec($insert_sample);
}

// معالجة عمليات CRUD
$message = '';
$message_type = '';

// إضافة رقم جديد
if (isset($_POST['add'])) {
    $country_code = $_POST['country_code'];
    $phone_number = $_POST['phone_number'];
    $country_name = $_POST['country_name'];
    $country_flag = $_POST['country_flag'];
    $display_order = $_POST['display_order'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO whatsapp_numbers 
            (country_code, phone_number, country_name, country_flag, display_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$country_code, $phone_number, $country_name, $country_flag, $display_order]);
        
        $message = "تم إضافة الرقم بنجاح!";
        $message_type = "success";
    } catch(PDOException $e) {
        $message = "خطأ في إضافة الرقم: " . $e->getMessage();
        $message_type = "error";
    }
}

// تعديل رقم
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $country_code = $_POST['country_code'];
    $phone_number = $_POST['phone_number'];
    $country_name = $_POST['country_name'];
    $country_flag = $_POST['country_flag'];
    $display_order = $_POST['display_order'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE whatsapp_numbers 
            SET country_code = ?, 
                phone_number = ?, 
                country_name = ?, 
                country_flag = ?, 
                display_order = ?,
                is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$country_code, $phone_number, $country_name, $country_flag, $display_order, $is_active, $id]);
        
        $message = "تم تعديل الرقم بنجاح!";
        $message_type = "success";
    } catch(PDOException $e) {
        $message = "خطأ في تعديل الرقم: " . $e->getMessage();
        $message_type = "error";
    }
}

// حذف رقم
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM whatsapp_numbers WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = "تم حذف الرقم بنجاح!";
        $message_type = "success";
    } catch(PDOException $e) {
        $message = "خطأ في حذف الرقم: " . $e->getMessage();
        $message_type = "error";
    }
}

// جلب جميع الأرقام
$stmt = $pdo->query("SELECT * FROM whatsapp_numbers ORDER BY     id");
$numbers = $stmt->fetchAll();

// تحضير البيانات للتحرير
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM whatsapp_numbers WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_whatsapp.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أرقام الواتساب - أكاديمية قادة المستقبل</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo h1 {
            font-size: 24px;
            font-weight: 700;
        }
        
        .logo span {
            color: #ffd700;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: white;
            color: #667eea;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-title {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 700;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #a5d6a7;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 2px solid #ef9a9a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #666;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .btn-danger {
            background: #ff4757;
            color: white;
        }
        
        .btn-danger:hover {
            background: #ff3742;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }
        
        .btn-edit {
            background: #2ed573;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .btn-delete {
            background: #ff4757;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .btn i {
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: #f8f9fa;
            color: #667eea;
            font-weight: 700;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-active {
            background: #2ed573;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-inactive {
            background: #ff4757;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .flag-emoji {
            font-size: 24px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-whatsapp fa-2x"></i>
                <h1>إدارة أرقام الواتساب <span>أكاديمية قادة المستقبل</span></h1>
            </div>
            <a href="?logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </header>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- بطاقة الإضافة/التعديل -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-<?php echo $edit_data ? 'edit' : 'plus'; ?>"></i>
                <?php echo $edit_data ? 'تعديل رقم' : 'إضافة رقم جديد'; ?>
            </h2>
            
            <form method="POST">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="country_code">رمز الدولة:</label>
                        <input type="text" 
                               id="country_code" 
                               name="country_code" 
                               value="<?php echo $edit_data ? $edit_data['country_code'] : '+966'; ?>"
                               required
                               placeholder="مثال: +966">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">رقم الهاتف:</label>
                        <input type="text" 
                               id="phone_number" 
                               name="phone_number" 
                               value="<?php echo $edit_data ? $edit_data['phone_number'] : ''; ?>"
                               required
                               placeholder="مثال: 500000000">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="country_name">اسم الدولة:</label>
                        <input type="text" 
                               id="country_name" 
                               name="country_name" 
                               value="<?php echo $edit_data ? $edit_data['country_name'] : ''; ?>"
                               required
                               placeholder="مثال: السعودية">
                    </div>
                    
                    <div class="form-group">
                        <label for="country_flag">رمز العلم:</label>
                        <select id="country_flag" name="country_flag" required>
                            <option value="">اختر رمز العلم</option>
                            <option value="🇸🇦" <?php echo ($edit_data && $edit_data['country_flag'] == '🇸🇦') ? 'selected' : ''; ?>>🇸🇦 السعودية</option>
                            <option value="🇦🇪" <?php echo ($edit_data && $edit_data['country_flag'] == '🇦🇪') ? 'selected' : ''; ?>>🇦🇪 الإمارات</option>
                            <option value="🇶🇦" <?php echo ($edit_data && $edit_data['country_flag'] == '🇶🇦') ? 'selected' : ''; ?>>🇶🇦 قطر</option>
                            <option value="🇰🇼" <?php echo ($edit_data && $edit_data['country_flag'] == '🇰🇼') ? 'selected' : ''; ?>>🇰🇼 الكويت</option>
                            <option value="🇧🇭" <?php echo ($edit_data && $edit_data['country_flag'] == '🇧🇭') ? 'selected' : ''; ?>>🇧🇭 البحرين</option>
                            <option value="🇴🇲" <?php echo ($edit_data && $edit_data['country_flag'] == '🇴🇲') ? 'selected' : ''; ?>>🇴🇲 عمان</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="display_order">ترتيب العرض:</label>
                        <input type="number" 
                               id="display_order" 
                               name="display_order" 
                               value="<?php echo $edit_data ? $edit_data['display_order'] : '0'; ?>"
                               min="0"
                               placeholder="رقم الترتيب">
                    </div>
                    
                    <?php if ($edit_data): ?>
                    <div class="form-group">
                        <label>الحالة:</label>
                        <div class="checkbox-group">
                            <input type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   <?php echo $edit_data['is_active'] ? 'checked' : ''; ?>>
                            <label for="is_active" style="margin-bottom: 0;">نشط</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="fas fa-save"></i> تحديث الرقم
                        </button>
                        <a href="admin_whatsapp.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    <?php else: ?>
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة رقم
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> إعادة تعيين
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- بطاقة عرض الأرقام -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-list"></i>
                قائمة أرقام الواتساب (<?php echo count($numbers); ?>)
            </h2>
            
            <?php if (count($numbers) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العلم</th>
                            <th>الدولة</th>
                            <th>رقم الهاتف</th>
                            <th>الترتيب</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($numbers as $index => $number): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td class="flag-emoji"><?php echo htmlspecialchars($number['country']); ?></td>
                                <td><?php echo htmlspecialchars($number['country']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($number['country'] . ' ' . $number['phone_number']); ?></strong>
                                </td>
                                <td><?php echo $number['id']; ?></td>
                                <td>
                                   
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($number['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?php echo $number['id']; ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> تعديل
                                        </a>
                                        <a href="?delete=<?php echo $number['id']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('هل أنت متأكد من حذف هذا الرقم؟');">
                                            <i class="fas fa-trash"></i> حذف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-inbox fa-4x" style="margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3 style="margin-bottom: 10px;">لا توجد أرقام</h3>
                    <p>لم يتم إضافة أي أرقام واتساب بعد.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- بطاقة المعلومات -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-info-circle"></i>
                معلومات هامة
            </h2>
            <div class="info-content">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="background: #e8f5e9; padding: 20px; border-radius: 10px; border-right: 4px solid #4caf50;">
                        <h3 style="color: #2e7d32; margin-bottom: 10px;">
                            <i class="fas fa-lightbulb"></i> نصائح
                        </h3>
                        <ul style="color: #555; padding-right: 20px;">
                            <li>استخدم رمز الدولة الصحيح (مثال: +966 للسعودية)</li>
                            <li>أدخل رقم الهاتف بدون مسافات أو شرطات</li>
                            <li>يمكنك تغيير ترتيب العرض للتحكم في الظهور</li>
                        </ul>
                    </div>
                    
                    <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; border-right: 4px solid #2196f3;">
                        <h3 style="color: #1565c0; margin-bottom: 10px;">
                            <i class="fas fa-exclamation-triangle"></i> ملاحظات
                        </h3>
                        <ul style="color: #555; padding-right: 20px;">
                            <li>الرقم غير النشط لن يظهر في الموقع</li>
                            <li>يتم حفظ التغييرات تلقائياً</li>
                            <li>لا يمكن استرجاع الأرقام المحذوفة</li>
                        </ul>
                    </div>
                </div>
                
                <div style="margin-top: 20px; background: #fff8e1; padding: 20px; border-radius: 10px; border-right: 4px solid #ffb300;">
                    <h3 style="color: #f57c00; margin-bottom: 10px;">
                        <i class="fas fa-code"></i> استخدام الرقم في الموقع
                    </h3>
                    <p style="color: #555;">لتضمين الرقم في الموقع، استخدم الرابط التالي:</p>
                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; direction: ltr; text-align: left; overflow-x: auto;">
https://wa.me/[رقم الهاتف]
مثال: https://wa.me/966500000000
                    </pre>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // إضافة رسالة تأكيد قبل الحذف
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('هل أنت متأكد من حذف هذا الرقم؟')) {
                        e.preventDefault();
                    }
                });
            });
            
            // إخفاء الرسالة بعد 5 ثوانٍ
            const message = document.querySelector('.message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        message.style.display = 'none';
                    }, 500);
                }, 5000);
            }
            
            // تحديد رمز العلم بناءً على اسم الدولة
            const countryNameInput = document.getElementById('country_name');
            const countryFlagSelect = document.getElementById('country_flag');
            
            if (countryNameInput && countryFlagSelect) {
                countryNameInput.addEventListener('change', function() {
                    const countryName = this.value.toLowerCase();
                    let flagValue = '';
                    
                    if (countryName.includes('سعود') || countryName.includes('saudi')) {
                        flagValue = '🇸🇦';
                    } else if (countryName.includes('إمارات') || countryName.includes('uae') || countryName.includes('emirates')) {
                        flagValue = '🇦🇪';
                    } else if (countryName.includes('قطر') || countryName.includes('qatar')) {
                        flagValue = '🇶🇦';
                    } else if (countryName.includes('كويت') || countryName.includes('kuwait')) {
                        flagValue = '🇰🇼';
                    } else if (countryName.includes('بحرين') || countryName.includes('bahrain')) {
                        flagValue = '🇧🇭';
                    } else if (countryName.includes('عمان') || countryName.includes('oman')) {
                        flagValue = '🇴🇲';
                    }
                    
                    if (flagValue) {
                        countryFlagSelect.value = flagValue;
                    }
                });
            }
        });
    </script>
</body>
</html>