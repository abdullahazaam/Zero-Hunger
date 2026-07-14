<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0;

// Language selection - WORKING
$lang = $_COOKIE['language'] ?? 'en';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    setcookie('language', $lang, time() + (86400 * 30), "/");
    // Refresh page to apply language
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Load language file
$translations = [];
$lang_file = __DIR__ . '/languages/' . $lang . '.php';
if (file_exists($lang_file)) {
    $translations = require $lang_file;
}

// Helper function for translation
function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <title><?php echo isset($pageTitle) ? $pageTitle : __('site_title'); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Zero Hunger - Connect surplus food with those in need. Donate food, request food packages, and help reduce food waste in Pakistan.">
    <meta name="keywords" content="food donation, zero hunger, ngo, food redistribution, charity, Pakistan, donate food, food waste">
    <meta name="author" content="Abdullah Azaam">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="Zero Hunger - Food Redistribution Network">
    <meta property="og:description" content="Connect surplus food with those in need. Donate or request food packages today!">
    <meta property="og:image" content="http://localhost/Zero%20Hunger/Frontend/img/logo.png">
    <meta property="og:url" content="http://localhost/Zero%20Hunger/Frontend/">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="http://localhost/Zero%20Hunger/Frontend/">
    
    <link rel="icon" href="img/favicon.ico">
    
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-XXXXXXXXXX');
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* ========== LIGHT MODE (Default) ========== */
        :root {
            --bg-primary: #f8f9fa;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e9ecef;
            --green-50: #f0faf4;
            --green-100: #d6f0e0;
            --green-400: #3aad6a;
            --green-500: #2e9458;
            --green-600: #226e42;
            --green-700: #174d2e;
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --amber-100: #fef3c7;
            --amber-700: #b45309;
            --orange-100: #ffedd5;
            --orange-600: #ea580c;
            --gray-50: #f8f9fa;
            --gray-100: #f1f3f5;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #adb5bd;
            --gray-500: #6c757d;
            --gray-700: #343a40;
            --gray-900: #212529;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
        }
        
        /* ========== DARK MODE - BLACK & BLUE (Like OneSpirit) ========== */
        body.dark-mode {
            --bg-primary: #0a0a0f;
            --bg-card: #0d1117;
            --text-primary: #ffffff;
            --text-secondary: #8b949e;
            --border-color: #21262d;
            --green-50: #0d2818;
            --green-100: #0d2818;
            --green-400: #3b82f6;
            --green-500: #3b82f6;
            --green-600: #2563eb;
            --green-700: #1d4ed8;
            --blue-50: #0d2818;
            --blue-100: #0d2818;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --amber-100: #1a1a2e;
            --amber-700: #3b82f6;
            --orange-100: #1a1a2e;
            --orange-600: #3b82f6;
            --gray-50: #161b22;
            --gray-100: #1a1a2e;
            --gray-200: #21262d;
            --gray-300: #30363d;
            --gray-400: #8b949e;
            --gray-500: #c9d1d9;
            --gray-700: #e6edf3;
            --gray-900: #ffffff;
            background: #0a0a0f;
        }
        
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
            font-family: 'Segoe UI', 'Poppins', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Card Styles for Dark Mode */
        body.dark-mode .dh-card,
        body.dark-mode .stat-card,
        body.dark-mode .chart-card,
        body.dark-mode .msg-shell,
        body.dark-mode .msg-sidebar,
        body.dark-mode .msg-chat,
        body.dark-mode .profile-card,
        body.dark-mode .feedback-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }
        
        body.dark-mode .dh-card-header,
        body.dark-mode .chart-header,
        body.dark-mode .msg-sidebar-header,
        body.dark-mode .msg-chat-header,
        body.dark-mode .zh-stats {
            background: var(--gray-50);
            border-color: var(--border-color);
        }
        
        /* Button Styles for Dark Mode */
        body.dark-mode .btn-login,
        body.dark-mode .btn-register,
        body.dark-mode .zh-btn-primary,
        body.dark-mode .claim-btn,
        body.dark-mode .deliver-btn,
        body.dark-mode .accept-btn,
        body.dark-mode .assign-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        body.dark-mode .btn-login:hover,
        body.dark-mode .zh-btn-primary:hover,
        body.dark-mode .claim-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        
        /* Badge Styles for Dark Mode */
        body.dark-mode .dh-badge.available,
        body.dark-mode .status-active,
        body.dark-mode .enroll-approved {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        
        body.dark-mode .dh-badge.claimed,
        body.dark-mode .status-inactive,
        body.dark-mode .status-suspended {
            background: rgba(248, 113, 113, 0.2);
            color: #f87171;
        }
        
        /* Table Styles for Dark Mode */
        body.dark-mode .dh-table thead th {
            background: var(--gray-50);
            color: var(--gray-400);
            border-color: var(--border-color);
        }
        
        body.dark-mode .dh-table tbody tr {
            border-color: var(--border-color);
        }
        
        body.dark-mode .dh-table tbody tr:hover {
            background: var(--gray-50);
        }
        
        /* Input Styles for Dark Mode */
        body.dark-mode .dh-input,
        body.dark-mode .dh-select,
        body.dark-mode .dh-textarea,
        body.dark-mode .form-control,
        body.dark-mode .search-input {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        body.dark-mode .dh-input:focus,
        body.dark-mode .dh-select:focus,
        body.dark-mode .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        /* Modal Styles for Dark Mode */
        body.dark-mode .zh-modal {
            background: var(--bg-card);
            border-color: var(--border-color);
        }
        
        body.dark-mode .zh-modal-header {
            background: var(--gray-50);
            border-color: var(--border-color);
        }
        
        /* Alert Styles for Dark Mode */
        body.dark-mode .dh-alert.success {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            color: #60a5fa;
        }
        
        body.dark-mode .dh-alert.danger {
            background: rgba(248, 113, 113, 0.15);
            border-color: rgba(248, 113, 113, 0.3);
            color: #f87171;
        }
        
        /* Map Styles for Dark Mode */
        body.dark-mode #map {
            filter: brightness(0.8) contrast(1.2);
        }
        
        /* Stats Card for Dark Mode */
        body.dark-mode .stat-card {
            background: var(--bg-card);
            border-color: var(--border-color);
        }
        
        body.dark-mode .stat-icon.green {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        body.dark-mode .stat-icon.blue {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        body.dark-mode .stat-icon.amber {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        body.dark-mode .stat-icon.orange {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        body.dark-mode .stat-number {
            color: #60a5fa;
        }
        
        /* Progress Card for Dark Mode */
        body.dark-mode .progress-card {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
        }
        
        /* Quick Links for Dark Mode */
        body.dark-mode .quick-link {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        body.dark-mode .quick-link:hover {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }
        
        /* Pagination for Dark Mode */
        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        body.dark-mode .pagination a:hover,
        body.dark-mode .pagination .active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        /* Footer for Dark Mode */
        body.dark-mode #footer {
            background-color: #0a0a0f !important;
            border-top-color: #3b82f6 !important;
        }
        
        body.dark-mode .bg-black {
            background-color: #050508 !important;
        }
        
        /* Avatar Styles for Dark Mode */
        body.dark-mode .user-av,
        body.dark-mode .donor-av,
        body.dark-mode .ngo-av,
        body.dark-mode .rider-av,
        body.dark-mode .person-av {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        /* Message Bubble for Dark Mode */
        body.dark-mode .bubble.sent {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        body.dark-mode .bubble.recv {
            background: var(--gray-50);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        /* Location Section for Dark Mode */
        body.dark-mode .current-location,
        body.dark-mode .address-preview {
            background: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        body.dark-mode .current-location i,
        body.dark-mode .address-preview i {
            color: #60a5fa;
        }
        
        .modern-header {
            background: var(--bg-card);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--border-color);
        }
        
        .navbar-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #2e9458, #1b5e20);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(46,148,88,0.3);
        }
        
        body.dark-mode .logo-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }
        
        .logo-icon i {
            font-size: 24px;
            color: white;
        }
        
        .logo-text h3 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        body.dark-mode .logo-text h3 {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .logo-text p {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin: -2px 0 0;
            letter-spacing: 0.5px;
        }
        
        .nav-links {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .nav-link-modern {
            font-weight: 600;
            font-size: 1rem;
            padding: 0.6rem 1.3rem;
            color: var(--text-primary);
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
        }
        
        .nav-link-modern:hover {
            background: linear-gradient(135deg, #f0faf4, #e8f5e9);
            color: #2e9458;
            transform: translateY(-2px);
        }
        
        body.dark-mode .nav-link-modern:hover {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }
        
        .nav-link-modern.active {
            background: linear-gradient(135deg, #2e9458, #226e42);
            color: white;
            box-shadow: 0 4px 12px rgba(46,148,88,0.25);
        }
        
        body.dark-mode .nav-link-modern.active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 4px 12px rgba(59,130,246,0.25);
        }
        
        .right-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .icon-circle {
            width: 44px;
            height: 44px;
            background: var(--gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-primary);
            border: none;
            position: relative;
        }
        
        .icon-circle:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
        }
        
        .icon-circle i {
            font-size: 1.2rem;
        }
        
        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e85a30;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
        }
        
        .lang-dropdown, .user-dropdown {
            position: relative;
        }
        
        .lang-menu, .dropdown-custom {
            position: absolute;
            top: 52px;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-width: 160px;
            display: none;
            z-index: 100;
            overflow: hidden;
        }
        
        .lang-menu.show, .dropdown-custom.show {
            display: block;
            animation: fadeIn 0.2s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .lang-menu a, .dropdown-custom a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text-primary);
            font-size: 13px;
            transition: background 0.2s;
        }
        
        .lang-menu a:hover, .dropdown-custom a:hover {
            background: var(--gray-100);
        }
        
        .user-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 0.4rem 1rem 0.4rem 0.6rem;
            border-radius: 50px;
            background: var(--gray-100);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        
        .user-trigger:hover {
            background: var(--gray-200);
        }
        
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #2e9458, #1b5e20);
        }
        
        body.dark-mode .user-avatar {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-avatar i {
            font-size: 0.9rem;
            color: white;
        }
        
        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 6px 0;
        }
        
        .auth-buttons {
            display: flex;
            gap: 12px;
        }
        
        .login-btn {
            background: transparent;
            border: 2px solid #2e9458;
            color: #2e9458;
            padding: 0.55rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        body.dark-mode .login-btn {
            border-color: #3b82f6;
            color: #60a5fa;
        }
        
        .login-btn:hover {
            background: #2e9458;
            color: white;
            transform: translateY(-1px);
        }
        
        body.dark-mode .login-btn:hover {
            background: #3b82f6;
            color: white;
        }
        
        .register-btn {
            background: linear-gradient(135deg, #2e9458, #226e42);
            border: none;
            color: white;
            padding: 0.55rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(46,148,88,0.25);
        }
        
        body.dark-mode .register-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 2px 6px rgba(59,130,246,0.25);
        }
        
        .register-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(46,148,88,0.35);
        }
        
        body.dark-mode .register-btn:hover {
            box-shadow: 0 4px 12px rgba(59,130,246,0.35);
        }
        
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 15px;
        }
        
        .loader-overlay.show {
            display: flex;
        }
        
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #fff;
            border-top: 3px solid #2e9458;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        body.dark-mode .loader-spinner {
            border-top-color: #3b82f6;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .modern-header {
                padding: 0.8rem 0;
            }
            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                margin-top: 1rem;
                flex-wrap: wrap;
            }
            .user-name {
                display: none;
            }
            .logo-text h3 {
                font-size: 1.2rem;
            }
            .logo-text p {
                display: none;
            }
            .icon-circle {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>

<div id="loaderOverlay" class="loader-overlay">
    <div class="loader-spinner"></div>
    <div class="loader-text" style="color:white;">Loading...</div>
</div>

<div class="modern-header">
    <div class="container-fluid px-4">
        <div class="navbar-custom">
            <!-- Logo -->
            <a href="index.php" class="navbar-brand">
                <div class="logo-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="logo-text">
                    <h3>Zero Hunger</h3>
                    <p>Food Redistribution Network</p>
                </div>
            </a>
            
            <!-- Navigation Links -->
            <div class="nav-links">
                <a href="index.php" class="nav-link-modern <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> <?= __('home') ?>
                </a>
                <a href="about.php" class="nav-link-modern <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">
                    <i class="fas fa-info-circle"></i> <?= __('about') ?>
                </a>
                
                <?php if ($is_logged_in && $user_role == 2): ?>
                <a href="donor_dashboard.php" class="nav-link-modern <?= basename($_SERVER['PHP_SELF']) == 'donor_dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-hand-holding-heart"></i> <?= __('dashboard') ?>
                </a>
                <?php elseif ($is_logged_in && $user_role == 3): ?>
                <a href="ngo_dashboard.php" class="nav-link-modern <?= basename($_SERVER['PHP_SELF']) == 'ngo_dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i> <?= __('dashboard') ?>
                </a>
                <?php elseif ($is_logged_in && $user_role == 4): ?>
                <a href="rider_dashboard.php" class="nav-link-modern <?= basename($_SERVER['PHP_SELF']) == 'rider_dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-motorcycle"></i> <?= __('dashboard') ?>
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Right Icons -->
            <div class="right-icons">
                <a href="messages.php" class="icon-circle" style="position: relative;">
                    <i class="fas fa-bell"></i>
                    <?php
                    if(isset($_SESSION['user_id'])){
                        $uid = $_SESSION['user_id'];
                        $unread_q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE receiver_id=$uid AND is_read=0");
                        $unread = mysqli_fetch_assoc($unread_q)['cnt'] ?? 0;
                        if($unread > 0){
                            echo '<span class="badge-notification">'.$unread.'</span>';
                        }
                    }
                    ?>
                </a>
                
                <div class="icon-circle" id="darkModeToggle">
                    <i class="fas fa-moon"></i>
                </div>
                
                <!-- Language Switcher - WORKING -->
                <div class="lang-dropdown">
                    <div class="icon-circle" id="langToggle">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="lang-menu" id="langMenu">
                        <a href="?lang=en">
                            <img src="https://flagcdn.com/w20/gb.png" width="20" alt="English">
                            English
                        </a>
                        <a href="?lang=ur">
                            <img src="https://flagcdn.com/w20/pk.png" width="20" alt="Urdu">
                            اردو
                        </a>
                    </div>
                </div>
                
                <?php if ($is_logged_in): ?>
                <div class="user-dropdown">
                    <div class="user-trigger" id="userTrigger">
                        <div class="user-avatar">
                            <?php 
                            $profile_pic = $_SESSION['profile_pic'] ?? '';
                            if (!empty($profile_pic) && file_exists('../' . $profile_pic)): 
                            ?>
                                <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                    </div>
                    <div class="dropdown-custom" id="userDropdown">
                        <a href="<?php 
                            if($user_role == 2) echo 'donor_dashboard.php';
                            elseif($user_role == 3) echo 'ngo_dashboard.php';
                            elseif($user_role == 4) echo 'rider_dashboard.php';
                            else echo 'index.php';
                        ?>">
                            <i class="fas fa-chart-line"></i> <?= __('dashboard') ?>
                        </a>
                        <a href="profile.php">
                            <i class="fas fa-user-edit"></i> <?= __('profile') ?>
                        </a>
                        <a href="messages.php">
                            <i class="fas fa-envelope"></i> <?= __('messages') ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" style="color: #dc2626;">
                            <i class="fas fa-sign-out-alt"></i> <?= __('logout') ?>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="login-btn"><?= __('login') ?></a>
                    <a href="register.php" class="register-btn"><?= __('register') ?></a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    darkModeToggle.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('darkMode', 'disabled');
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
}

// Language Dropdown
const langToggle = document.getElementById('langToggle');
const langMenu = document.getElementById('langMenu');

if (langToggle) {
    langToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        langMenu.classList.toggle('show');
    });
}

// User Dropdown
const userTrigger = document.getElementById('userTrigger');
const userDropdown = document.getElementById('userDropdown');

if (userTrigger) {
    userTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });
}

document.addEventListener('click', () => {
    if (langMenu) langMenu.classList.remove('show');
    if (userDropdown) userDropdown.classList.remove('show');
});

function showLoader() {
    const loader = document.getElementById('loaderOverlay');
    if (loader) loader.classList.add('show');
}
function hideLoader() {
    const loader = document.getElementById('loaderOverlay');
    if (loader) loader.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function() {
    initDarkMode();
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showLoader();
        });
    });
});

function showToast(icon, title, message) {
    Swal.fire({
        icon: icon,
        title: title,
        text: message,
        timer: 3000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}
</script>
</body>
</html>