<?php
include '../Backend/db.php';
include 'header.php'; // First - defines __() function
$pageTitle = __('home') . ' - ' . __('site_title');

// Location-based food filtering for home page
if (isset($_SESSION['user_id'])) {
    include '../Backend/location_functions.php';
    $user_location = getUserLocation($conn, $_SESSION['user_id']);
    if (!empty($user_location['lat']) && !empty($user_location['lng'])) {
        $location_filter = getLocationFilterSQL($user_location['lat'], $user_location['lng'], 'fd', 'latitude', 'longitude', $user_location['radius']);
        $food_stock_query = "SELECT fd.*, u.full_name FROM food_donations fd 
                             JOIN users u ON fd.donor_id = u.user_id 
                             WHERE fd.status = 'Available' 
                             AND $location_filter
                             ORDER BY fd.donation_id DESC LIMIT 8";
    } else {
        $food_stock_query = "SELECT fd.*, u.full_name FROM food_donations fd 
                             JOIN users u ON fd.donor_id = u.user_id 
                             WHERE fd.status = 'Available' 
                             ORDER BY fd.donation_id DESC LIMIT 8";
    }
} else {
    $food_stock_query = "SELECT fd.*, u.full_name FROM food_donations fd 
                         JOIN users u ON fd.donor_id = u.user_id 
                         WHERE fd.status = 'Available' 
                         ORDER BY fd.donation_id DESC LIMIT 8";
}
$food_stock = mysqli_query($conn, $food_stock_query);
?>

<style>
    :root {
        --green-50:  #f0faf4;
        --green-100: #d6f0e0;
        --green-400: #3aad6a;
        --green-500: #2e9458;
        --green-600: #226e42;
        --green-700: #174d2e;
        --gray-50:  #f8f9fa;
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

    /* Dark Mode Variables */
    body.dark-mode {
        --green-50: #0d2818;
        --green-100: #0d2818;
        --green-400: #3b82f6;
        --green-500: #3b82f6;
        --green-600: #2563eb;
        --green-700: #1d4ed8;
        --gray-50: #161b22;
        --gray-100: #0d1117;
        --gray-200: #21262d;
        --gray-300: #30363d;
        --gray-400: #8b949e;
        --gray-500: #c9d1d9;
        --gray-700: #e6edf3;
        --gray-900: #ffffff;
        background-color: #0a0a0f;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
        overflow-x: hidden; 
        background-color: var(--gray-100);
        transition: background-color 0.3s, color 0.3s;
    }

    /* ============ HERO SLIDER ============ */
    .zh-hero {
        position: relative;
        width: 100%;
        height: 89vh;
        min-height: 500px;
        max-height: 700px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    
    .hero-slider {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 1.2s ease-in-out;
        z-index: 1;
    }
    
    .hero-slide.active {
        opacity: 1;
        z-index: 2;
    }
    
    /* Fix for image switching flicker */
    .hero-slide.fade-out {
        opacity: 0 !important;
        z-index: 1;
        transition: opacity 0.8s ease-in-out;
    }
    
    .hero-slide.fade-in {
        opacity: 1 !important;
        z-index: 3;
        transition: opacity 1.2s ease-in-out;
    }
    
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.35) 100%);
        z-index: 10;
    }
    
    body.dark-mode .hero-overlay {
        background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.5) 100%);
    }
    
    .hero-content {
        position: relative;
        z-index: 12;
        max-width: 850px;
        margin: 0 auto;
        padding: 1.5rem;
        animation: fadeInUp 0.8s ease;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .zh-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(46,148,88,0.85);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255,255,255,0.25);
        border-radius: 50px;
        padding: 6px 18px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 1rem;
    }
    
    body.dark-mode .zh-hero-badge {
        background: rgba(59, 130, 246, 0.85);
    }
    
    .zh-hero h1 {
        font-size: clamp(1.8rem, 5vw, 3.2rem);
        font-weight: 800;
        color: white;
        line-height: 1.2;
        margin-bottom: 0.8rem;
        text-shadow: 0 2px 15px rgba(0,0,0,0.2);
    }
    
    .zh-hero h1 span { color: #4ade80; }
    
    body.dark-mode .zh-hero h1 span { color: #60a5fa; }
    
    .zh-hero p {
        font-size: 1rem;
        color: rgba(255,255,255,0.92);
        max-width: 600px;
        margin: 0 auto 1.5rem;
        line-height: 1.5;
    }
    
    .zh-hero-btns {
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .zh-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #2e9458, #1b5e20);
        color: #fff;
        padding: 12px 28px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(46,148,88,0.3);
    }
    
    body.dark-mode .zh-btn-primary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .zh-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(46,148,88,0.4);
        color: white;
    }
    
    body.dark-mode .zh-btn-primary:hover {
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    .zh-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(8px);
        color: white;
        padding: 12px 28px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: 1.5px solid rgba(255,255,255,0.4);
        transition: all 0.3s ease;
    }
    
    .zh-btn-outline:hover {
        background: rgba(255,255,255,0.25);
        border-color: rgba(255,255,255,0.7);
        transform: translateY(-3px);
        color: white;
    }
    
    .scroll-down {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 15;
        cursor: pointer;
    }
    
    .scroll-down a {
        color: white;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    
    .scroll-down a:hover { opacity: 1; }
    .scroll-down span { font-size: 11px; font-weight: 500; letter-spacing: 1px; }
    .scroll-down i { font-size: 12px; animation: bounce 1.5s infinite; }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(5px); }
    }
    
    /* Slider Controls */
    .slider-controls {
        position: absolute;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 15;
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        border: 2px solid rgba(255,255,255,0.3);
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0;
    }
    
    .slider-dot.active {
        background: #4ade80;
        border-color: #4ade80;
        transform: scale(1.2);
        box-shadow: 0 0 15px rgba(74, 222, 128, 0.4);
    }
    
    body.dark-mode .slider-dot.active {
        background: #60a5fa;
        border-color: #60a5fa;
        box-shadow: 0 0 15px rgba(96, 165, 250, 0.4);
    }
    
    .slider-dot:hover {
        transform: scale(1.1);
        background: rgba(255,255,255,0.7);
    }
    
    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 15;
        background: rgba(0,0,0,0.3);
        backdrop-filter: blur(8px);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .slider-arrow:hover {
        background: rgba(0,0,0,0.5);
        transform: translateY(-50%) scale(1.05);
    }
    
    .slider-arrow.prev { left: 20px; }
    .slider-arrow.next { right: 20px; }
    
    body.dark-mode .slider-arrow {
        background: rgba(0,0,0,0.5);
    }
    
    body.dark-mode .slider-arrow:hover {
        background: rgba(0,0,0,0.7);
    }
    
    @media (max-width: 768px) {
        .slider-arrow { display: none; }
        .slider-controls { bottom: 60px; }
        .zh-hero { height: 75vh; min-height: 450px; }
        .zh-hero h1 { font-size: 1.6rem; }
        .zh-hero p { font-size: 0.85rem; }
        .zh-btn-primary, .zh-btn-outline { padding: 8px 20px; font-size: 12px; }
        .scroll-down { bottom: 12px; }
    }

    /* ============ STATS SECTION ============ */
    .zh-stats {
        background: white;
        padding: 2.5rem 0;
        position: relative;
        z-index: 5;
    }
    
    body.dark-mode .zh-stats {
        background: var(--gray-50);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .stat-card {
        text-align: center;
        padding: 1.5rem 1rem;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
    }
    
    body.dark-mode .stat-card {
        background: var(--gray-100);
        border-color: var(--gray-200);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--green-400);
        box-shadow: var(--shadow-card);
    }
    
    .stat-icon {
        width: 55px;
        height: 55px;
        background: var(--green-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.8rem;
    }
    
    body.dark-mode .stat-icon {
        background: rgba(59, 130, 246, 0.15);
    }
    
    .stat-icon i { 
        font-size: 24px; 
        color: var(--green-500); 
    }
    
    body.dark-mode .stat-icon i {
        color: #60a5fa;
    }
    
    .stat-number { 
        font-size: 2rem; 
        font-weight: 800; 
        color: var(--green-600); 
        margin-bottom: 0.2rem; 
    }
    
    body.dark-mode .stat-number {
        color: #60a5fa;
    }
    
    .stat-label { 
        font-size: 0.8rem; 
        font-weight: 600; 
        color: var(--gray-600); 
        text-transform: uppercase; 
    }
    
    body.dark-mode .stat-label {
        color: var(--gray-400);
    }
    
    .stat-sub { 
        font-size: 0.65rem; 
        color: var(--gray-400); 
        margin-top: 0.2rem; 
    }
    
    body.dark-mode .stat-sub {
        color: var(--gray-500);
    }
    
    @media (max-width: 768px) {
        .stats-grid { 
            grid-template-columns: repeat(2, 1fr); 
            gap: 1rem; 
        }
        .stat-number { font-size: 1.5rem; }
    }
    
    /* ============ FOOD SECTION ============ */
    .zh-section {
        padding: 3rem 0;
        background: var(--gray-100);
    }
    
    body.dark-mode .zh-section {
        background: var(--gray-100);
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    
    .section-header h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }
    
    body.dark-mode .section-header h2 {
        color: var(--gray-200);
    }
    
    .section-header p {
        font-size: 0.95rem;
        color: var(--gray-500);
        max-width: 600px;
        margin: 0 auto;
    }
    
    body.dark-mode .section-header p {
        color: var(--gray-400);
    }
    
    .section-header .badge {
        display: inline-block;
        background: var(--green-100);
        color: var(--green-600);
        padding: 0.25rem 0.8rem;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-bottom: 0.8rem;
    }
    
    body.dark-mode .section-header .badge {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
    
    .food-card {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow-card);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    
    body.dark-mode .food-card {
        background: var(--gray-50);
        border-color: var(--gray-200);
    }
    
    .food-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        border-color: var(--green-400);
    }
    
    .food-card-top {
        background: linear-gradient(135deg, var(--green-50), #fff);
        padding: 1rem;
        border-bottom: 1px solid var(--green-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    body.dark-mode .food-card-top {
        background: linear-gradient(135deg, var(--gray-100), var(--gray-50));
        border-bottom-color: var(--gray-200);
    }
    
    .food-card-top h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--green-700);
        margin: 0;
    }
    
    body.dark-mode .food-card-top h4 {
        color: #60a5fa;
    }
    
    .live-dot {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #fff;
        border: 1px solid var(--green-100);
        color: var(--green-600);
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 30px;
    }
    
    body.dark-mode .live-dot {
        background: var(--gray-100);
        border-color: var(--gray-300);
        color: #60a5fa;
    }
    
    .live-dot::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--green-400);
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    
    .food-card-body {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .food-donor {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0.6rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px solid var(--gray-100);
    }
    
    body.dark-mode .food-donor {
        border-bottom-color: var(--gray-200);
    }
    
    .food-donor-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--green-100);
        color: var(--green-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }
    
    body.dark-mode .food-donor-avatar {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
    
    .food-donor span {
        font-weight: 600;
        font-size: 13px;
        color: var(--gray-700);
    }
    
    body.dark-mode .food-donor span {
        color: var(--gray-400);
    }
    
    .food-desc {
        font-size: 0.8rem;
        color: var(--gray-500);
        line-height: 1.4;
        margin-bottom: 0.8rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    body.dark-mode .food-desc {
        color: var(--gray-400);
    }
    
    .food-meta {
        background: var(--gray-50);
        border-radius: var(--radius-sm);
        padding: 0.6rem;
        margin-bottom: 0.8rem;
    }
    
    body.dark-mode .food-meta {
        background: var(--gray-100);
    }
    
    .food-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        padding: 0.2rem 0;
    }
    
    .food-meta-row + .food-meta-row {
        border-top: 1px solid var(--gray-200);
        margin-top: 0.2rem;
        padding-top: 0.2rem;
    }
    
    body.dark-mode .food-meta-row {
        border-top-color: var(--gray-200);
    }
    
    .food-meta-lbl { 
        color: var(--gray-500); 
        font-weight: 500; 
    }
    
    body.dark-mode .food-meta-lbl {
        color: var(--gray-500);
    }
    
    .food-meta-val { 
        font-weight: 700; 
        color: var(--gray-800); 
    }
    
    body.dark-mode .food-meta-val {
        color: var(--gray-300);
    }
    
    .food-meta-val.price { 
        color: var(--green-600); 
        font-size: 0.9rem; 
    }
    
    body.dark-mode .food-meta-val.price {
        color: #60a5fa;
    }
    
    .food-meta-val.expiry { 
        color: #c0392b; 
    }
    
    body.dark-mode .food-meta-val.expiry { 
        color: #f87171; 
    }
    
    .food-claim-btn {
        display: block;
        width: 100%;
        padding: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
        background: var(--green-500);
        border: none;
        border-radius: var(--radius-sm);
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    body.dark-mode .food-claim-btn {
        background: #3b82f6;
    }
    
    .food-claim-btn:hover {
        background: var(--green-600);
        transform: translateY(-2px);
    }
    
    body.dark-mode .food-claim-btn:hover {
        background: #2563eb;
    }
    
    .food-disabled-btn {
        display: block;
        width: 100%;
        padding: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #fff;
        background: #6c757d;
        border: none;
        border-radius: var(--radius-sm);
        text-align: center;
        text-decoration: none;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    body.dark-mode .food-disabled-btn {
        background: var(--gray-300);
    }
    
    .food-login-btn {
        display: block;
        width: 100%;
        padding: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--green-600);
        background: var(--green-50);
        border: 1.5px solid var(--green-100);
        border-radius: var(--radius-sm);
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    body.dark-mode .food-login-btn {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
        color: #60a5fa;
    }
    
    .food-login-btn:hover { 
        background: var(--green-100); 
    }
    
    body.dark-mode .food-login-btn:hover {
        background: rgba(59, 130, 246, 0.2);
    }
    
    .zh-empty {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--gray-200);
    }
    
    body.dark-mode .zh-empty {
        background: var(--gray-50);
        border-color: var(--gray-200);
    }
    
    .zh-empty i {
        font-size: 3rem;
        color: var(--gray-300);
        margin-bottom: 0.8rem;
        display: block;
    }
    
    body.dark-mode .zh-empty i {
        color: var(--gray-400);
    }
    
    .zh-empty p { 
        font-size: 0.9rem; 
        color: var(--gray-500); 
    }
    
    body.dark-mode .zh-empty p {
        color: var(--gray-400);
    }
    
    /* ============ FEEDBACK SECTION ============ */
    .zh-feedback-section {
        background: white;
        padding: 3rem 0;
    }
    
    body.dark-mode .zh-feedback-section {
        background: var(--gray-50);
    }
    
    .feedback-card {
        max-width: 550px;
        margin: 0 auto;
        background: var(--gray-50);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        border: 1px solid var(--gray-200);
    }
    
    body.dark-mode .feedback-card {
        background: var(--gray-100);
        border-color: var(--gray-200);
    }
    
    .feedback-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1.2rem;
    }
    
    .feedback-icon {
        width: 45px;
        height: 45px;
        background: var(--green-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    body.dark-mode .feedback-icon {
        background: rgba(59, 130, 246, 0.15);
    }
    
    .feedback-icon i {
        font-size: 20px;
        color: var(--green-500);
    }
    
    body.dark-mode .feedback-icon i {
        color: #60a5fa;
    }
    
    .feedback-header h3 { 
        margin: 0; 
        font-size: 1.2rem; 
        font-weight: 700; 
        color: var(--gray-900);
    }
    
    body.dark-mode .feedback-header h3 {
        color: var(--gray-200);
    }
    
    .feedback-header p { 
        margin: 0; 
        font-size: 0.8rem; 
        color: var(--gray-500); 
    }
    
    body.dark-mode .feedback-header p {
        color: var(--gray-400);
    }
    
    .dh-textarea {
        width: 100%;
        padding: 10px;
        border: 1.5px solid var(--gray-300);
        border-radius: var(--radius-sm);
        resize: vertical;
        min-height: 80px;
        font-family: inherit;
        background: white;
        color: var(--gray-900);
    }
    
    body.dark-mode .dh-textarea {
        background: var(--gray-50);
        border-color: var(--gray-300);
        color: var(--gray-200);
    }
    
    .dh-textarea:focus {
        border-color: var(--green-400);
        outline: none;
        box-shadow: 0 0 0 3px rgba(46,148,88,.18);
    }
    
    body.dark-mode .dh-textarea:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    
    .dh-textarea::placeholder {
        color: var(--gray-500);
    }
    
    body.dark-mode .dh-textarea::placeholder {
        color: var(--gray-500);
    }
    
    .dh-stars {
        display: flex;
        gap: 8px;
        margin: 8px 0;
    }
    
    .dh-star-btn {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        transition: all 0.2s;
        padding: 0;
        line-height: 1;
        color: #dee2e6;
    }
    
    body.dark-mode .dh-star-btn {
        color: #4a4a5a;
    }
    
    .dh-star-btn.active, .dh-star-btn:hover {
        color: #f59e0b !important;
        transform: scale(1.15);
    }
    
    body.dark-mode .dh-star-btn.active, 
    body.dark-mode .dh-star-btn:hover {
        color: #f59e0b !important;
        transform: scale(1.15);
    }
    
    .rating-label-text {
        margin-top: 8px;
        font-size: 13px;
        color: #2e9458 !important;
        transition: color 0.3s ease;
        font-weight: 500;
    }
    
    body.dark-mode .rating-label-text {
        color: #60a5fa !important;
    }
    
    .dh-btn {
        width: 100%;
        padding: 10px;
        background: var(--green-500);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    body.dark-mode .dh-btn {
        background: #3b82f6;
    }
    
    .dh-btn:hover {
        background: var(--green-600);
        transform: translateY(-2px);
    }
    
    body.dark-mode .dh-btn:hover {
        background: #2563eb;
    }
    
    .dh-divider {
        height: 1px;
        background: var(--gray-200);
        margin: 1.2rem 0;
    }
    
    body.dark-mode .dh-divider {
        background: var(--gray-200);
    }
</style>

<!-- Hero Section with Image Slider -->
<div class="zh-hero">
    <div class="hero-slider" id="heroSlider">
        <?php
        $hero_images = [
            'HeroSection1.jpg',
            'HeroSection2.jpg',
            'HeroSection3.png',
            'HeroSection4.png',
            'HeroSection5.png'
        ];
        foreach($hero_images as $index => $img):
            $active_class = ($index === 0) ? 'active' : '';
        ?>
        <div class="hero-slide <?= $active_class ?>" style="background-image: url('uploads/<?= $img ?>');"></div>
        <?php endforeach; ?>
    </div>
    
    <div class="hero-overlay"></div>
    
    <!-- Slider Controls -->
    <button class="slider-arrow prev" id="prevSlide" aria-label="Previous slide">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="slider-arrow next" id="nextSlide" aria-label="Next slide">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <div class="slider-controls" id="sliderControls">
        <?php for($i = 0; $i < 5; $i++): ?>
        <button class="slider-dot <?= ($i === 0) ? 'active' : '' ?>" data-index="<?= $i ?>"></button>
        <?php endfor; ?>
    </div>
    
    <div class="hero-content">
        <div class="zh-hero-badge"><i class="fas fa-circle"></i> <?= __('live_network') ?></div>
        <h1><?= __('hero_title') ?></h1>
        <p><?= __('hero_subtitle') ?></p>
        <div class="zh-hero-btns">
            <a href="register.php" class="zh-btn-primary"><i class="fas fa-hand-holding-heart"></i> <?= __('become_donor') ?></a>
            <a href="login.php" class="zh-btn-outline"><i class="fas fa-box-open"></i> <?= __('request_food') ?></a>
        </div>
    </div>
    
    <div class="scroll-down">
        <a href="javascript:void(0)" onclick="document.getElementById('stats').scrollIntoView({behavior:'smooth'})">
            <span><?= __('scroll_down') ?></span>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</div>

<!-- Stats Section -->
<div class="zh-stats" id="stats">
    <div class="container">
        <div class="stats-grid">
            <?php
            $total_donations = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM food_donations"))[0] ?? 0;
            $completed = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM food_donations WHERE status='Delivered'"))[0] ?? 0;
            $available = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM food_donations WHERE status='Available'"))[0] ?? 0;
            $total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0] ?? 0;
            ?>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <div class="stat-number"><?= $total_donations ?></div>
                <div class="stat-label"><?= __('total_donations') ?></div>
                <div class="stat-sub"><?= __('food_items_donated') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <div class="stat-number"><?= $completed ?></div>
                <div class="stat-label"><?= __('deliveries_completed') ?></div>
                <div class="stat-sub"><?= __('successfully_delivered') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-number"><?= $available ?></div>
                <div class="stat-label"><?= __('live_now') ?></div>
                <div class="stat-sub"><?= __('available_for_claim') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?= $total_users ?></div>
                <div class="stat-label"><?= __('registered_users') ?></div>
                <div class="stat-sub"><?= __('active_community_members') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Food Listings Section -->
<div class="zh-section">
<div class="container">
    <div class="section-header">
        <span class="badge"><i class="fas fa-fire"></i> <?= __('fresh_available') ?></span>
        <h2><?= __('live_food_donations') ?></h2>
        <p><?= __('food_description') ?></p>
    </div>

    <div class="row g-4">
    <?php
    if ($food_stock && mysqli_num_rows($food_stock) > 0) {
        while ($food = mysqli_fetch_assoc($food_stock)) {
            $initials = strtoupper(substr($food['full_name'], 0, 1));
            $expiry_fmt = date('d M, h:i A', strtotime($food['expiry_time']));
            $user_role = $_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0;
    ?>
        <div class="col-md-6 col-lg-3">
            <div class="food-card">
                <div class="food-card-top">
                    <h4><?= htmlspecialchars($food['food_item']) ?></h4>
                    <span class="live-dot"><?= __('live_now') ?></span>
                </div>
                <div class="food-card-body">
                    <div class="food-donor">
                        <div class="food-donor-avatar"><?= $initials ?></div>
                        <span><?= htmlspecialchars($food['full_name']) ?></span>
                    </div>
                    <div class="food-desc">
                        <?= htmlspecialchars($food['description'] ?: 'Freshly prepared quality food.') ?>
                    </div>
                    <div class="food-meta">
                        <div class="food-meta-row">
                            <span class="food-meta-lbl"><i class="fas fa-weight-hanging"></i> <?= __('quantity') ?></span>
                            <span class="food-meta-val"><?= htmlspecialchars($food['quantity']) ?></span>
                        </div>
                        <div class="food-meta-row">
                            <span class="food-meta-lbl"><i class="fas fa-tag"></i> <?= __('price') ?></span>
                            <span class="food-meta-val price">Rs. <?= $food['price'] ?></span>
                        </div>
                        <div class="food-meta-row">
                            <span class="food-meta-lbl"><i class="far fa-clock"></i> <?= __('expires') ?></span>
                            <span class="food-meta-val expiry"><?= $expiry_fmt ?></span>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if($user_role == 3): ?>
                            <a href="claim_food.php?donation_id=<?= $food['donation_id'] ?>" class="food-claim-btn" onclick="return confirm('<?= __('claim_food') ?>')">
                                <i class="fas fa-hand-holding-heart"></i> <?= __('claim_now') ?>
                            </a>
                        <?php else: ?>
                            <button class="food-disabled-btn" disabled><i class="fas fa-lock"></i> <?= __('only_ngo_can_claim') ?></button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="food-login-btn"><i class="fas fa-sign-in-alt"></i> <?= __('login_to_claim') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php }} else { ?>
        <div class="col-12">
            <div class="zh-empty">
                <i class="fas fa-box-open"></i>
                <h4><?= __('no_active_donations') ?></h4>
                <p><?= __('no_donations_message') ?></p>
                <a href="register.php" class="zh-btn-primary" style="display: inline-block; margin-top: 1rem; padding: 8px 20px;">
                    <i class="fas fa-hand-holding-heart"></i> <?= __('become_donor') ?>
                </a>
            </div>
        </div>
    <?php } ?>
    </div>
</div>
</div>

<!-- Feedback Section -->
<div class="zh-feedback-section">
<div class="container">
    <div class="feedback-card">
        <div class="feedback-header">
            <div class="feedback-icon"><i class="fas fa-comment-dots"></i></div>
            <div>
                <h3><?= __('community_feedback') ?></h3>
                <p><?= __('share_experience') ?></p>
            </div>
        </div>
        
        <form method="POST" action="submit_feedback.php">
            <div class="mb-3">
                <textarea name="message" class="dh-textarea" rows="3" placeholder="<?= __('tell_us_experience') ?>" required></textarea>
            </div>
            <div class="mb-3">
                <label class="dh-label"><?= __('rating') ?></label>
                <div class="dh-stars" id="starContainer">
                    <button type="button" class="dh-star-btn" data-val="1">★</button>
                    <button type="button" class="dh-star-btn" data-val="2">★</button>
                    <button type="button" class="dh-star-btn" data-val="3">★</button>
                    <button type="button" class="dh-star-btn" data-val="4">★</button>
                    <button type="button" class="dh-star-btn" data-val="5">★</button>
                </div>
                <input type="hidden" name="rating" id="ratingValue" value="5">
                <div class="rating-label-text" id="ratingLabel">5 stars — <?= __('excellent_service') ?></div>
            </div>
            <div class="dh-divider"></div>
            <button type="submit" class="dh-btn"><i class="fas fa-paper-plane"></i> <?= __('submit_feedback') ?></button>
        </form>
    </div>
</div>
</div>

<script>
// Hero Slider with Improved Logic
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.getElementById('prevSlide');
    const nextBtn = document.getElementById('nextSlide');
    let currentIndex = 0;
    let slideInterval;
    let isTransitioning = false;
    const intervalTime = 4000;

    function goToSlide(index) {
        if (isTransitioning || index === currentIndex) return;
        isTransitioning = true;
        
        // Remove active from all
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Add active to new
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        
        currentIndex = index;
        
        setTimeout(() => {
            isTransitioning = false;
        }, 1200);
    }

    function nextSlide() {
        let newIndex = (currentIndex + 1) % slides.length;
        goToSlide(newIndex);
    }

    function prevSlide() {
        let newIndex = (currentIndex - 1 + slides.length) % slides.length;
        goToSlide(newIndex);
    }

    function startAutoPlay() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, intervalTime);
    }

    function stopAutoPlay() {
        clearInterval(slideInterval);
    }

    // Dot click
    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            if (index !== currentIndex) {
                stopAutoPlay();
                goToSlide(index);
                startAutoPlay();
            }
        });
    });

    // Arrow clicks
    prevBtn.addEventListener('click', function() {
        stopAutoPlay();
        prevSlide();
        startAutoPlay();
    });

    nextBtn.addEventListener('click', function() {
        stopAutoPlay();
        nextSlide();
        startAutoPlay();
    });

    // Keyboard
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            stopAutoPlay();
            prevSlide();
            startAutoPlay();
        } else if (e.key === 'ArrowRight') {
            stopAutoPlay();
            nextSlide();
            startAutoPlay();
        }
    });

    // Hover pause
    const heroSection = document.querySelector('.zh-hero');
    heroSection.addEventListener('mouseenter', stopAutoPlay);
    heroSection.addEventListener('mouseleave', startAutoPlay);

    // Start
    startAutoPlay();
});

function scrollToStats() { 
    const stats = document.getElementById('stats'); 
    if(stats) stats.scrollIntoView({ behavior: 'smooth', block: 'start' }); 
}

// Stars functionality
const labels = ['','1 star — <?= __('poor') ?>','2 stars — <?= __('below_average') ?>','3 stars — <?= __('average') ?>','4 stars — <?= __('good') ?>','5 stars — <?= __('excellent_service') ?>'];
const stars = document.querySelectorAll('.dh-star-btn');
let current = 5;

function setStars(val) {
    stars.forEach(s => s.classList.toggle('active', parseInt(s.dataset.val) <= val));
    document.getElementById('ratingValue').value = val;
    document.getElementById('ratingLabel').textContent = labels[val];
    current = val;
}

setStars(5);
stars.forEach(s => {
    s.addEventListener('click', () => setStars(parseInt(s.dataset.val)));
    s.addEventListener('mouseenter', () => stars.forEach(x => x.classList.toggle('active', parseInt(x.dataset.val) <= parseInt(s.dataset.val))));
    s.addEventListener('mouseleave', () => setStars(current));
});
</script>

<?php include 'footer.php'; ?>