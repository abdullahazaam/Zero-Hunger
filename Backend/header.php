
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= isset($pageTitle) ? $pageTitle : 'Zero Hunger - Dashboard'; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Dark Mode Variables */
        :root {
            --green-50: #f0faf4;
            --green-100: #d6f0e0;
            --green-200: #aee0c3;
            --green-400: #3aad6a;
            --green-500: #2e9458;
            --green-600: #226e42;
            --green-700: #174d2e;
            --gray-50: #f8f9fa;
            --gray-100: #f1f3f5;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #adb5bd;
            --gray-500: #6c757d;
            --gray-700: #343a40;
            --gray-900: #111827;
            --sidebar-w: 260px;
            --topbar-h: 60px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
            --shadow-sidebar: 2px 0 20px rgba(0,0,0,.07);
        }
        
        /* Dark Mode */
        body.dark-mode {
            --gray-50: #1e1e2e;
            --gray-100: #2a2a3a;
            --gray-200: #3a3a4a;
            --gray-300: #4a4a5a;
            --gray-400: #6a6a7a;
            --gray-500: #8a8a9a;
            --gray-700: #c0c0d0;
            --gray-900: #ffffff;
            background: #1a1a2e;
        }
        
        body.dark-mode .dh-card,
        body.dark-mode .zh-sidebar,
        body.dark-mode .zh-topbar,
        body.dark-mode .stat-card {
            background: #2a2a3a;
            border-color: #3a3a4a;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            margin: 0;
            padding: 0;
            color: var(--gray-900);
            transition: background 0.3s, color 0.3s;
        }
        
        /* Dark Mode Toggle */
        .dark-mode-toggle {
            background: var(--gray-100);
            border: 1px solid var(--gray-300);
            border-radius: 30px;
            padding: 5px 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .zh-layout { display: flex; min-height: 100vh; }
        
        .zh-sidebar {
            width: var(--sidebar-w);
            background: #fff;
            border-right: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sidebar);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            z-index: 1040;
            transition: transform .25s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .zh-sidebar::-webkit-scrollbar { width: 5px; }
        .zh-sidebar::-webkit-scrollbar-track { background: var(--gray-100); border-radius: 10px; }
        .zh-sidebar::-webkit-scrollbar-thumb { background: var(--gray-400); border-radius: 10px; }
        
        .zh-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1.1rem 1.25rem 1rem;
            border-bottom: 1px solid var(--gray-100);
            text-decoration: none;
        }
        .zh-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--green-500);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .zh-brand-name { font-size: 15px; font-weight: 800; color: var(--gray-900); }
        .zh-brand-sub { font-size: 11px; color: var(--gray-400); margin-top: 1px; }
        
        .zh-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: .85rem 1.25rem;
            border-bottom: 1px solid var(--gray-100);
        }
        .zh-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid var(--green-100); }
        .zh-user-name { font-size: 13px; font-weight: 600; color: var(--gray-900); }
        .zh-user-role { font-size: 11px; color: var(--gray-400); margin-top: 1px; }
        
        .zh-nav { padding: .75rem .75rem 2rem; flex: 1; }
        .zh-nav-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--gray-400);
            padding: .6rem .6rem .3rem;
        }
        .zh-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 500;
            color: var(--gray-700);
            text-decoration: none;
            transition: background .12s;
            margin-bottom: 2px;
        }
        .zh-nav-link i { width: 18px; text-align: center; font-size: 13px; color: var(--gray-400); }
        .zh-nav-link:hover { background: var(--gray-100); color: var(--gray-900); }
        .zh-nav-link:hover i { color: var(--green-500); }
        .zh-nav-link.active { background: var(--green-50); color: var(--green-700); font-weight: 600; }
        
        .zh-main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .zh-topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .zh-sidebar-toggle {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
        }
        .zh-search {
            flex: 1;
            max-width: 320px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray-100);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 0 12px;
            height: 36px;
        }
        .zh-search:focus-within { border-color: var(--green-400); background: #fff; }
        .zh-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13px;
            color: var(--gray-900);
            width: 100%;
        }
        .zh-topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .zh-profile-wrap { position: relative; }
        .zh-profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            padding: 4px 10px 4px 5px;
            cursor: pointer;
        }
        .zh-profile-btn img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
        .zh-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            background: #fff;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            min-width: 180px;
            overflow: hidden;
            display: none;
            z-index: 2000;
        }
        .zh-dropdown.open { display: block; }
        .zh-dropdown a {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            text-decoration: none;
        }
        .zh-dropdown a:hover { background: var(--gray-50); color: var(--gray-900); }
        .zh-dropdown-divider { height: 1px; background: var(--gray-100); margin: 3px 0; }
        .zh-dropdown a.logout { color: #c0392b; }
        
        .zh-page-content { flex: 1; padding: 1.75rem; background: var(--gray-100); }
        
        @media (max-width: 991px) {
            .zh-sidebar { transform: translateX(-100%); }
            .zh-sidebar.open { transform: translateX(0); }
            .zh-main { margin-left: 0; }
            .zh-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.35);
                z-index: 1035;
            }
            .zh-overlay.show { display: block; }
        }
    </style>
</head>
<body>
<div class="zh-layout">

<aside class="zh-sidebar" id="zhSidebar">
    <a href="dashboard.php" class="zh-brand">
        <div class="zh-brand-icon"><i class="fas fa-seedling"></i></div>
        <div>
            <div class="zh-brand-name">Zero Hunger</div>
            <div class="zh-brand-sub">Admin Portal</div>
        </div>
    </a>

    <div class="zh-user-card">
        <div class="zh-avatar-status">
            <img class="zh-avatar" src="img\My Pic.jpg" alt="Admin">
        </div>
        <div>
            <div class="zh-user-name"><?= $_SESSION['admin_name'] ?? 'Admin' ?></div>
            <div class="zh-user-role">System Control</div>
        </div>
    </div>

    <nav class="zh-nav">
        <div class="zh-nav-label">Overview</div>
        <a href="dashboard.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="zh-nav-label">Management</div>
        <a href="roles.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'roles.php' ? 'active' : '' ?>">
            <i class="fas fa-shield-alt"></i> Platform Roles
        </a>
        <a href="users.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Platform Users
        </a>
        <a href="food_donations.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'food_donations.php' ? 'active' : '' ?>">
            <i class="fas fa-hand-holding-heart"></i> Food Donations
        </a>
        <a href="requests.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'requests.php' ? 'active' : '' ?>">
            <i class="fas fa-bullhorn"></i> Food Requests
        </a>
        <a href="manage_riders.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_riders.php' ? 'active' : '' ?>">
            <i class="fas fa-motorcycle"></i> Riders
        </a>

        <div class="zh-nav-label">Logistics</div>
        <a href="food_deliveries.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'food_deliveries.php' ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> Food Deliveries
        </a>
        <a href="view_all_deliveries.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'view_all_deliveries.php' ? 'active' : '' ?>">
            <i class="fas fa-map-marked-alt"></i> Live Tracking
        </a>

        <div class="zh-nav-label">Insights</div>
        <a href="feedback.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : '' ?>">
            <i class="fas fa-comments"></i> User Feedback
        </a>
        <a href="reports.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Analytics Reports
        </a>

        <!-- New Admin Features -->
        <div class="zh-nav-label">System</div>
        <!-- <a href="activity_logs.php" class="zh-nav-link <?= basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i> Activity Logs
        </a> -->
        <a href="export_reports.php?type=donations" class="zh-nav-link">
            <i class="fas fa-file-excel"></i> Export Reports
        </a>

        <div class="zh-nav-label" style="margin-top:.5rem;"></div>
        <a href="admin_logout.php" class="zh-nav-link" style="color:#c0392b;">
            <i class="fas fa-sign-out-alt" style="color:#c0392b;"></i> Logout
        </a>
    </nav>
</aside>

<div class="zh-overlay" id="zhOverlay"></div>

<div class="zh-main">
    <nav class="zh-topbar">
        <button class="zh-sidebar-toggle" id="zhToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="zh-search d-none d-md-flex">
            <i class="fas fa-search"></i>
            <input type="search" id="globalSearch" placeholder="Search portal...">
        </div>

        <div class="zh-topbar-right">
            <div class="dark-mode-toggle" id="darkModeToggle">
                <i class="fas fa-moon"></i>
                <span class="d-none d-md-inline">Dark Mode</span>
            </div>
            
            <div class="zh-profile-wrap">
                <div class="zh-profile-btn" id="zhProfileBtn">
                    <img src="img\My Pic.jpg" alt="Admin">
                    <span class="d-none d-lg-inline"><?= $_SESSION['admin_name'] ?? 'Admin' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="zh-dropdown" id="zhDropdown">
                    <!-- <a href="activity_logs.php"><i class="fas fa-history"></i> Activity Logs</a> -->
                    <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                    <div class="zh-dropdown-divider"></div>
                    <a href="admin_logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="zh-page-content">

<script>
// Sidebar toggle
const sidebar = document.getElementById('zhSidebar');
const overlay = document.getElementById('zhOverlay');
const toggle = document.getElementById('zhToggle');

toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
});

// Profile dropdown
const profileBtn = document.getElementById('zhProfileBtn');
const dropdown = document.getElementById('zhDropdown');
profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.classList.toggle('open');
});
document.addEventListener('click', () => dropdown.classList.remove('open'));

// Dark Mode Toggle
const darkModeToggle = document.getElementById('darkModeToggle');
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    darkModeToggle.innerHTML = '<i class="fas fa-sun"></i><span class="d-none d-md-inline">Light Mode</span>';
}
darkModeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i><span class="d-none d-md-inline">Light Mode</span>';
    } else {
        localStorage.setItem('darkMode', 'disabled');
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i><span class="d-none d-md-inline">Dark Mode</span>';
    }
});

// Global Search
document.getElementById('globalSearch')?.addEventListener('keyup', function(e) {
    const searchTerm = this.value.toLowerCase();
    const tables = document.querySelectorAll('.dh-table tbody tr');
    tables.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>