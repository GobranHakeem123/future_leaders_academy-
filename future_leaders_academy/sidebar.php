<!-- قائمة التنقل الجانبية -->

<style>
    
        .sidebar {
            background: var(--gradient-primary);
            min-height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            width: 280px;
       
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar:hover {
            width: 280px;
        }
        
        .sidebar .logo {
            padding: 25px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .sidebar .logo h4 {
            background: linear-gradient(45deg, #fff, #4cc9f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 15px 25px;
            margin: 5px 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: rgba(255, 255, 255, 0.1);
            transition: width 0.3s ease;
            border-radius: 12px;
        }
        
        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            width: 100%;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            transform: translateX(-5px);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-left: 10px;
            font-size: 1.2rem;
        }
</style>
  <!-- Sidebar -->
            <nav class="sidebar d-none d-md-block">
                <div class="logo text-center">
                    <h4 class="mb-0">📁 إدارة الملفات</h4>
                    <small class="text-white opacity-75">لوحة التحكم المتكاملة</small>
                </div>
                
                <div class="sidebar-sticky pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i>
                                <span>الصفحة الرئيسية</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_work.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>إضافة ملف جديد</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="categories.php">
                                <i class="fas fa-tags"></i>
                                <span>إدارة الفئات</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="statistics.php">
                                <i class="fas fa-chart-bar"></i>
                                <span>التقارير والإحصائيات</span>
                            </a>
                        </li>
                    </ul>
                    
                    <!-- User Profile and Logout -->
                    <div class="mt-5 px-3">
                        <div class="user-profile" onclick="showLogoutModal()">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-info">
                                <h6 class="mb-0 text-white"><?php echo $_SESSION['username'] ?? 'المسؤول'; ?></h6>
                                <small class="text-white opacity-75">مدير النظام</small>
                            </div>
                        </div>
                        
                        <!-- Logout Button in Sidebar -->
                        <div class="mt-3 px-3">
                            <button class="btn btn-logout w-100" onclick="showLogoutModal()">
                                <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                            </button>
                        </div>
                    </div>
                </div>
            </nav>