<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$message = '';
$message_type = '';

// إضافة نوع ملف جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    try {
        $type = $_POST['type'];
        $extension = $_POST['extension'];
        $mime_type = $_POST['mime_type'];
        $max_size = $_POST['max_size'] * 1024 * 1024; // تحويل من MB إلى bytes

        // التحقق من عدم وجود الامتداد مسبقاً
        $checkStmt = $pdo->prepare("SELECT * FROM allowed_file_types WHERE extension = ?");
        $checkStmt->execute([$extension]);
        
        if ($checkStmt->fetch()) {
            throw new Exception("امتداد الملف '$extension' موجود بالفعل.");
        }

        // إضافة النوع الجديد
        $stmt = $pdo->prepare("INSERT INTO allowed_file_types (type, extension, mime_type, max_size) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$type, $extension, $mime_type, $max_size]);
        
        $message = "تم إضافة نوع الملف بنجاح!";
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// تعديل نوع ملف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
    try {
        $type = $_POST['type'];
        $extension = $_POST['extension'];
        $mime_type = $_POST['mime_type'];
        $max_size = $_POST['max_size'] * 1024 * 1024;

        // التحقق من عدم وجود الامتداد مسبقاً في سجلات أخرى
        $checkStmt = $pdo->prepare("SELECT * FROM allowed_file_types WHERE extension = ? AND id != ?");
        $checkStmt->execute([$extension, $id]);
        
        if ($checkStmt->fetch()) {
            throw new Exception("امتداد الملف '$extension' موجود بالفعل في سجل آخر.");
        }

        // تحديث النوع
        $stmt = $pdo->prepare("UPDATE allowed_file_types 
                              SET type = ?, extension = ?, mime_type = ?, max_size = ? 
                              WHERE id = ?");
        $stmt->execute([$type, $extension, $mime_type, $max_size, $id]);
        
        $message = "تم تحديث نوع الملف بنجاح!";
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// حذف نوع ملف
if ($action === 'delete' && $id > 0) {
    try {
        // حذف النوع
        $stmt = $pdo->prepare("DELETE FROM allowed_file_types WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = "تم حذف نوع الملف بنجاح!";
        $message_type = 'success';
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'danger';
    }
}

// جلب جميع أنواع الملفات
$stmt = $pdo->query("SELECT * FROM allowed_file_types ORDER BY type, extension");
$file_types = $stmt->fetchAll();

// جلب نوع ملف محدد للتعديل
$edit_type = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM allowed_file_types WHERE id = ?");
    $stmt->execute([$id]);
    $edit_type = $stmt->fetch();
}

// جلب أنواع الملفات المميزة للإحصاءات
$type_stats = $pdo->query("
    SELECT type, COUNT(*) as count, 
           SUM(max_size) as total_max_size,
           GROUP_CONCAT(extension SEPARATOR ', ') as extensions
    FROM allowed_file_types 
    GROUP BY type 
    ORDER BY type
")->fetchAll();

// دالة للحصول على اسم نوع الملف بالعربية
function getFileTypeName($type) {
    $names = [
        'image' => 'صورة',
        'video' => 'فيديو',
        'pdf' => 'ملف PDF',
        'word' => 'ملف Word',
        'excel' => 'ملف Excel',
        'powerpoint' => 'ملف PowerPoint',
        'text' => 'ملف نصي',
        'archive' => 'ملف مضغوط',
        'document' => 'مستند'
    ];
    
    return $names[$type] ?? $type;
}

// دالة للحصول على أيقونة النوع
function getTypeIcon($type) {
    $icons = [
        'image' => 'fas fa-image',
        'video' => 'fas fa-video',
        'pdf' => 'fas fa-file-pdf',
        'word' => 'fas fa-file-word',
        'excel' => 'fas fa-file-excel',
        'powerpoint' => 'fas fa-file-powerpoint',
        'text' => 'fas fa-file-alt',
        'archive' => 'fas fa-file-archive',
        'document' => 'fas fa-file'
    ];
    
    return $icons[$type] ?? 'fas fa-file';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة أنواع الملفات - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .type-card {
            border: 1px solid #e0e6ed;
            border-radius: 10px;
            transition: all 0.3s;
            height: 100%;
        }
        
        .type-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }
        
        .type-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .badge-extension {
            font-family: monospace;
            font-size: 0.85rem;
        }
        
        .max-size-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .action-buttons {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .type-card:hover .action-buttons {
            opacity: 1;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">إدارة أنواع الملفات</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                        <i class="fas fa-plus me-1"></i> إضافة نوع جديد
                    </button>
                </div>
                
                <!-- رسائل التنبيه -->
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- إحصائيات الأنواع -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="stat-card">
                            <h5 class="mb-4">إحصائيات أنواع الملفات</h5>
                            <div class="row">
                                <?php foreach($type_stats as $stat): ?>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="text-center">
                                            <i class="<?php echo getTypeIcon($stat['type']); ?> fa-2x text-primary mb-2"></i>
                                            <h6><?php echo getFileTypeName($stat['type']); ?></h6>
                                            <p class="mb-1">
                                                <span class="badge bg-info"><?php echo $stat['count']; ?> امتداد</span>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo $stat['extensions']; ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- قائمة أنواع الملفات -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">قائمة أنواع الملفات المسموح بها</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($file_types)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5>لا توجد أنواع ملفات</h5>
                                <p class="text-muted">لم يتم إضافة أي أنواع ملفات بعد.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                                    <i class="fas fa-plus me-1"></i> إضافة أول نوع
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>النوع</th>
                                            <th>الامتداد</th>
                                            <th>MIME Type</th>
                                            <th>الحد الأقصى للحجم</th>
                                            <th>تاريخ الإضافة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($file_types as $file_type): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="<?php echo getTypeIcon($file_type['type']); ?> me-2 text-primary"></i>
                                                        <span><?php echo getFileTypeName($file_type['type']); ?></span>
                                                        <span class="badge bg-light text-dark ms-2"><?php echo $file_type['type']; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-extension bg-secondary">.<?php echo $file_type['extension']; ?></span>
                                                </td>
                                                <td>
                                                    <code><?php echo $file_type['mime_type']; ?></code>
                                                </td>
                                                <td>
                                                    <span class="badge max-size-badge">
                                                        <?php echo formatFileSize($file_type['max_size']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('Y-m-d', strtotime($file_type['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="file_types.php?action=edit&id=<?php echo $file_type['id']; ?>" 
                                                           class="btn btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="file_types.php?action=delete&id=<?php echo $file_type['id']; ?>" 
                                                           class="btn btn-danger"
                                                           onclick="return confirm('هل أنت متأكد من حذف هذا النوع؟');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- ملخص الإحصائيات -->
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">عدد الأنواع</h5>
                                            <h2 class="display-6"><?php echo count($file_types); ?></h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">ملاحظات مهمة:</h6>
                                            <ul class="mb-0">
                                                <li>يجب التأكد من صحة MIME Type لكل امتداد</li>
                                                <li>يجب تعيين حد حجم مناسب لكل نوع من الملفات</li>
                                                <li>لا يمكن رفع ملفات بامتدادات غير موجودة في هذه القائمة</li>
                                                <li>تحديث هذه القائمة يتطلب إعادة تحميل الصفحة الرئيسية</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- نموذج إضافة نوع جديد -->
                <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="file_types.php?action=add">
                                <div class="modal-header">
                                    <h5 class="modal-title">إضافة نوع ملف جديد</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="type" class="form-label">نوع الملف <span class="text-danger">*</span></label>
                                            <select class="form-control" id="type" name="type" required>
                                                <option value="">اختر النوع</option>
                                                <option value="image">صورة</option>
                                                <option value="video">فيديو</option>
                                                <option value="pdf">PDF</option>
                                                <option value="word">Word</option>
                                                <option value="excel">Excel</option>
                                                <option value="powerpoint">PowerPoint</option>
                                                <option value="text">نص</option>
                                                <option value="archive">مضغوط</option>
                                                <option value="document">مستند</option>
                                            </select>
                                            <small class="text-muted">التصنيف الرئيسي للملف</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="extension" class="form-label">امتداد الملف <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="extension" name="extension" 
                                                   placeholder="مثال: jpg, pdf, mp4" required>
                                            <small class="text-muted">بدون نقطة، أحرف صغيرة فقط</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="mime_type" class="form-label">MIME Type <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="mime_type" name="mime_type" 
                                                   placeholder="مثال: image/jpeg, application/pdf" required>
                                            <small class="text-muted">نوع MIME الخاص بالامتداد</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="max_size" class="form-label">الحد الأقصى للحجم <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="max_size" name="max_size" 
                                                       min="1" max="1024" value="5" required>
                                                <span class="input-group-text">MB</span>
                                            </div>
                                            <small class="text-muted">الحجم الأقصى المسموح به للملف بالميجابايت</small>
                                        </div>
                                    </div>
                                    
                                    <!-- أنواع شائعة للإضافة السريعة -->
                                    <div class="card mt-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">أنواع ملفات شائعة</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                                            onclick="setTypeValues('image', 'jpg', 'image/jpeg', 10)">
                                                        JPG
                                                    </button>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                                            onclick="setTypeValues('image', 'png', 'image/png', 10)">
                                                        PNG
                                                    </button>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-success w-100" 
                                                            onclick="setTypeValues('pdf', 'pdf', 'application/pdf', 50)">
                                                        PDF
                                                    </button>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-info w-100" 
                                                            onclick="setTypeValues('word', 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 20)">
                                                        DOCX
                                                    </button>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" 
                                                            onclick="setTypeValues('video', 'mp4', 'video/mp4', 100)">
                                                        MP4
                                                    </button>
                                                </div>
                                                <div class="col-4 mb-2">
                                                    <button type="button" class="btn btn-sm btn-outline-warning w-100" 
                                                            onclick="setTypeValues('archive', 'zip', 'application/zip', 100)">
                                                        ZIP
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> حفظ النوع
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- نموذج تعديل نوع -->
                <?php if ($action === 'edit' && $edit_type): ?>
                    <div class="modal fade show" id="editTypeModal" style="display: block; background: rgba(0,0,0,0.5);">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" action="file_types.php?action=edit&id=<?php echo $edit_type['id']; ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">تعديل نوع الملف</h5>
                                        <a href="file_types.php" class="btn-close"></a>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_type" class="form-label">نوع الملف <span class="text-danger">*</span></label>
                                                <select class="form-control" id="edit_type" name="type" required>
                                                    <option value="">اختر النوع</option>
                                                    <option value="image" <?php echo $edit_type['type'] === 'image' ? 'selected' : ''; ?>>صورة</option>
                                                    <option value="video" <?php echo $edit_type['type'] === 'video' ? 'selected' : ''; ?>>فيديو</option>
                                                    <option value="pdf" <?php echo $edit_type['type'] === 'pdf' ? 'selected' : ''; ?>>PDF</option>
                                                    <option value="word" <?php echo $edit_type['type'] === 'word' ? 'selected' : ''; ?>>Word</option>
                                                    <option value="excel" <?php echo $edit_type['type'] === 'excel' ? 'selected' : ''; ?>>Excel</option>
                                                    <option value="powerpoint" <?php echo $edit_type['type'] === 'powerpoint' ? 'selected' : ''; ?>>PowerPoint</option>
                                                    <option value="text" <?php echo $edit_type['type'] === 'text' ? 'selected' : ''; ?>>نص</option>
                                                    <option value="archive" <?php echo $edit_type['type'] === 'archive' ? 'selected' : ''; ?>>مضغوط</option>
                                                    <option value="document" <?php echo $edit_type['type'] === 'document' ? 'selected' : ''; ?>>مستند</option>
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_extension" class="form-label">امتداد الملف <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit_extension" name="extension" 
                                                       value="<?php echo htmlspecialchars($edit_type['extension']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_mime_type" class="form-label">MIME Type <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit_mime_type" name="mime_type" 
                                                       value="<?php echo htmlspecialchars($edit_type['mime_type']); ?>" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_max_size" class="form-label">الحد الأقصى للحجم <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="edit_max_size" name="max_size" 
                                                           min="1" max="1024" 
                                                           value="<?php echo $edit_type['max_size'] / (1024 * 1024); ?>" required>
                                                    <span class="input-group-text">MB</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="file_types.php" class="btn btn-secondary">إلغاء</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> حفظ التغييرات
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // دالة لتعيين القيم في نموذج الإضافة السريعة
        function setTypeValues(type, extension, mimeType, maxSize) {
            document.getElementById('type').value = type;
            document.getElementById('extension').value = extension;
            document.getElementById('mime_type').value = mimeType;
            document.getElementById('max_size').value = maxSize;
        }
        
        // فتح نموذج التعديل عند التحميل
        <?php if ($action === 'edit' && $edit_type): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var editModal = new bootstrap.Modal(document.getElementById('editTypeModal'));
                editModal.show();
            });
        <?php endif; ?>
        
        // التحقق من صحة المدخلات
        document.addEventListener('DOMContentLoaded', function() {
            // التحقق من نموذج الإضافة
            var addForm = document.querySelector('form[action*="action=add"]');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    var extension = document.getElementById('extension').value;
                    if (!/^[a-z0-9]+$/.test(extension)) {
                        e.preventDefault();
                        alert('الامتداد يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام فقط');
                        return false;
                    }
                });
            }
            
            // التحقق من نموذج التعديل
            var editForm = document.querySelector('form[action*="action=edit"]');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    var extension = document.getElementById('edit_extension')?.value;
                    if (extension && !/^[a-z0-9]+$/.test(extension)) {
                        e.preventDefault();
                        alert('الامتداد يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام فقط');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>