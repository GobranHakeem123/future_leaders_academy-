<?php
require_once 'config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// متغيرات للبحث والتصفية
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20; // عدد المستخدمين في كل صفحة
$offset = ($page - 1) * $limit;

// بناء الاستعلام الديناميكي
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role_filter)) {
    $conditions[] = "role = ?";
    $params[] = $role_filter;
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $conditions[] = "status = ?";
    $params[] = ($status_filter === 'active') ? 1 : 0;
}

// بناء استعلام WHERE
$where_clause = '';
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// استعلام العد الكلي
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetch()['total'];
$total_pages = ceil($total_users / $limit);

// استعلام جلب المستخدمين مع الترحيل
$users_sql = "SELECT 
                id, 
                username, 
                full_name, 
                email, 
                role, 
                status, 
                last_login, 
                created_at, 
                updated_at 
              FROM users 
              $where_clause 
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$users_stmt = $pdo->prepare($users_sql);
$users_stmt->execute($params);
$users = $users_stmt->fetchAll();

// جلب الأدوار المختلفة
$roles_stmt = $pdo->query("SELECT DISTINCT role FROM users WHERE role IS NOT NULL ORDER BY role");
$roles = $roles_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين - Future Leaders Academy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        }
        
        body {
            font-family: 'Tajawal', 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-right: 5px solid var(--primary-color);
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 30px;
        }
        
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 30px;
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 700;
            color: #495057;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
        }
        
        .badge-teacher {
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            color: white;
        }
        
        .badge-student {
            background: linear-gradient(135deg, #7209b7, #4cc9f0);
            color: white;
        }
        
        .badge-user {
            background: linear-gradient(135deg, #f72585, #7209b7);
            color: white;
        }
        
        .status-active {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: 600;
        }
        
        .search-box {
            max-width: 400px;
        }
        
        .filter-btn {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            color: #495057;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .filter-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border: none;
            margin: 0 2px;
            border-radius: 8px;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--gradient-primary);
            color: white;
            border: none;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        
        .export-btn {
            background: linear-gradient(135deg, #20c997, #0ca678);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(32, 201, 151, 0.3);
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            border-top: 4px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .stats-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- الشريط الجانبي -->
    <?php include 'sidebar.php'; ?>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <!-- رأس الصفحة -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-users me-2"></i>إدارة المستخدمين</h1>
                    <p class="text-muted mb-0">إدارة جميع المستخدمين في قاعدة البيانات</p>
                </div>
                <div>
                    <a href="add_user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>إضافة مستخدم جديد
                    </a>
                </div>
            </div>
        </div>
        
        <!-- إحصائيات سريعة -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_users; ?></div>
                    <div class="stats-label">إجمالي المستخدمين</div>
                </div>
            </div>
            <?php
            // جلب إحصائيات الأدوار
            $roles_stats = [];
            foreach ($roles as $role) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
                $stmt->execute([$role]);
                $roles_stats[$role] = $stmt->fetch()['count'];
            }
            
            // عرض أهم 3 أدوار
            $i = 0;
            foreach ($roles_stats as $role => $count) {
                if ($i < 3) {
                    $icons = ['admin' => 'fa-user-cog', 'teacher' => 'fa-chalkboard-teacher', 'student' => 'fa-user-graduate', 'user' => 'fa-user'];
                    $icon = $icons[$role] ?? 'fa-user';
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="stats-number"><?php echo $count; ?></div>
                    <div class="stats-label"><?php echo $role; ?></div>
                </div>
            </div>
            <?php
                    $i++;
                }
            }
            ?>
        </div>
        
        <!-- بطاقة البحث والتصفية -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="ابحث باسم المستخدم، الاسم الكامل أو البريد الإلكتروني"
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">الدور</label>
                        <select name="role" class="form-select">
                            <option value="">جميع الأدوار</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role; ?>" <?php echo $role_filter == $role ? 'selected' : ''; ?>>
                                <?php echo $role; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="all">جميع الحالات</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>نشط</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>تصفية
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- جدول المستخدمين -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    قائمة المستخدمين
                    <span class="badge bg-light text-dark ms-2"><?php echo $total_users; ?> مستخدم</span>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($total_users > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="60">#</th>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الدور</th>
                                <th>الحالة</th>
                                <th>آخر دخول</th>
                                <th>التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td>
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)); ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($user['full_name'] ?: 'بدون اسم'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($user['email']): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">بدون بريد</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = 'badge-user';
                                    switch ($user['role']) {
                                        case 'admin':
                                        case 'super_admin':
                                            $badge_class = 'badge-admin';
                                            break;
                                        case 'teacher':
                                            $badge_class = 'badge-teacher';
                                            break;
                                        case 'student':
                                            $badge_class = 'badge-student';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> p-2">
                                        <i class="fas 
                                            <?php 
                                            switch ($user['role']) {
                                                case 'admin': echo 'fa-user-cog'; break;
                                                case 'teacher': echo 'fa-chalkboard-teacher'; break;
                                                case 'student': echo 'fa-user-graduate'; break;
                                                default: echo 'fa-user';
                                            }
                                            ?> 
                                            me-1"></i>
                                        <?php echo htmlspecialchars($user['role'] ?: 'user'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['status'] == 1): ?>
                                    <span class="status-active">
                                        <i class="fas fa-check-circle me-1"></i>نشط
                                    </span>
                                    <?php else: ?>
                                    <span class="status-inactive">
                                        <i class="fas fa-times-circle me-1"></i>غير نشط
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                    <span class="text-muted">
                                        <?php echo date('Y-m-d H:i', strtotime($user['last_login'])); ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted">لم يسجل دخول</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?php echo date('Y-m-d', strtotime($user['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="view_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-user" 
                                                data-id="<?php echo $user['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($user['username']); ?>"
                                                title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- الترحيل -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">لا توجد مستخدمين</h4>
                    <p class="text-muted">لم يتم العثور على مستخدمين مطابقين للبحث</p>
                    <a href="get_users.php" class="btn btn-primary">
                        <i class="fas fa-redo me-1"></i>عرض جميع المستخدمين
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- بطاقة التصدير والإحصائيات -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>إحصائيات وتصدير</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>خيارات التصدير:</h6>
                        <div class="btn-group" role="group">
                            <a href="export_users.php?format=csv&<?php echo http_build_query($_GET); ?>" 
                               class="btn btn-outline-success">
                                <i class="fas fa-file-csv me-1"></i>تصدير CSV
                            </a>
                            <a href="export_users.php?format=excel&<?php echo http_build_query($_GET); ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-file-excel me-1"></i>تصدير Excel
                            </a>
                            <a href="export_users.php?format=pdf&<?php echo http_build_query($_GET); ?>" 
                               class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                            </a>
                            <button type="button" class="btn btn-outline-warning" onclick="printUsersTable()">
                                <i class="fas fa-print me-1"></i>طباعة
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>ملخص الإحصائيات:</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-user-check text-success me-2"></i>
                                <strong>المستخدمين النشطين:</strong> 
                                <?php 
                                $active_stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE status = 1");
                                echo $active_stmt->fetch()['active'];
                                ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <strong>المسجلين هذا الشهر:</strong> 
                                <?php 
                                $month_stmt = $pdo->query("SELECT COUNT(*) as this_month FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                                echo $month_stmt->fetch()['this_month'];
                                ?>
                            </li>
                            <li>
                                <i class="fas fa-sign-in-alt text-info me-2"></i>
                                <strong>آخر دخول خلال أسبوع:</strong> 
                                <?php 
                                $login_stmt = $pdo->query("SELECT COUNT(*) as recent_login FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                                echo $login_stmt->fetch()['recent_login'];
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal لحذف المستخدم -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف المستخدم "<span id="userName"></span>"؟</p>
                    <p class="text-danger"><small>هذا الإجراء لا يمكن التراجع عنه.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">حذف</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // تفعيل DataTable
        $(document).ready(function() {
            $('.table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json'
                },
                order: [[5, 'desc']], // ترتيب حسب آخر دخول
                pageLength: 20,
                responsive: true
            });
        });
        
        // حذف المستخدم
        let userIdToDelete = null;
        
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                userIdToDelete = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                document.getElementById('userName').textContent = userName;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
        
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (userIdToDelete) {
                // إرسال طلب الحذف
                fetch(`delete_user.php?id=${userIdToDelete}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id: userIdToDelete})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('تم حذف المستخدم بنجاح');
                        location.reload();
                    } else {
                        alert('حدث خطأ أثناء الحذف: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في الاتصال');
                });
                
                // إغلاق Modal
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            }
        });
        
        // دالة الطباعة
        function printUsersTable() {
            const printContent = document.querySelector('.card:has(.table)').outerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>طباعة قائمة المستخدمين</title>
                    <style>
                        body { font-family: 'Tajawal', sans-serif; padding: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
                        th { background-color: #f8f9fa; }
                        @media print {
                            @page { size: landscape; }
                        }
                    </style>
                </head>
                <body>
                    <h2 style="text-align: center; margin-bottom: 20px;">قائمة المستخدمين - Future Leaders Academy</h2>
                    <h4 style="text-align: center; color: #666; margin-bottom: 30px;">تاريخ الطباعة: ${new Date().toLocaleDateString('ar-SA')}</h4>
                    ${printContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // دالة التحديث التلقائي
        function autoRefreshTable() {
            setTimeout(() => {
                if (!document.hidden) {
                    // تحديث عدد المستخدمين فقط (ليس الجدول كاملاً)
                    fetch('get_users_count.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.total_users !== <?php echo $total_users; ?>) {
                                if (confirm('تم تحديث عدد المستخدمين. هل تريد تحديث الصفحة؟')) {
                                    location.reload();
                                }
                            }
                        });
                }
            }, 30000); // كل 30 ثانية
        }
        
        autoRefreshTable();
    </script>
</body>
</html>