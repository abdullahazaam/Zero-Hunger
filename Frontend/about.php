<?php
include '../Backend/db.php';
include 'header.php';
$pageTitle = __('about') . ' - ' . __('site_title');


// Creator ka data
$creator_name = "Abdullah Azaam";
$creator_email = "abdullahazaam1505@gmail.com";
$creator_phone = "+923320829327";
$creator_profile_pic = "uploads/My Pic.jpg";
?>

<style>
:root {
    --green-50: #f0faf4;
    --green-100: #d6f0e0;
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
    --gray-900: #212529;
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
    --shadow-lg: 0 10px 30px rgba(0,0,0,0.1);
}

.about-page {
    background: var(--gray-100);
    min-height: 100vh;
    padding: 2rem 0 3rem;
}

.dh-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

/* CARD HEADER - GREEN LIKE LOGIN PAGE */
.dh-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #2e7d32, #1b5e20);
    color: white;
}

.dh-header-icon {
    width: 45px;
    height: 45px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    background: rgba(255,255,255,0.2);
    color: white;
    flex-shrink: 0;
}

.dh-card-header h5 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
}

.dh-card-header p {
    margin: 0;
    font-size: 13px;
    color: rgba(255,255,255,0.85);
}

.dh-card-body {
    padding: 1.5rem;
}

/* Dark Mode - Card Headers Blue */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

/* Creator Profile */
.creator-profile {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 0.5rem 0;
}

.creator-avatar {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid white;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.creator-avatar:hover {
    transform: scale(1.02);
    box-shadow: 0 15px 35px rgba(46,148,88,0.2);
}

.creator-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.creator-info {
    flex: 1;
}

.creator-name {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--gray-900);
    margin-bottom: 0.25rem;
    letter-spacing: -0.5px;
}

.creator-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    color: white;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.creator-bio {
    font-size: 14px;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 1rem;
}

/* Contact Info */
.contact-info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.contact-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    background: var(--gray-50);
    border-radius: 40px;
    border: 1px solid var(--gray-200);
    text-decoration: none;
    transition: all 0.25s ease;
}

.contact-item i {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--green-100);
    border-radius: 50%;
    color: var(--green-600);
    font-size: 13px;
    transition: all 0.25s ease;
}

.contact-item span {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-700);
}

.contact-item:hover {
    transform: translateY(-3px);
    border-color: var(--green-400);
    box-shadow: var(--shadow-card);
}

.contact-item:hover i {
    background: var(--green-500);
    color: white;
}

.contact-item:hover span {
    color: var(--green-600);
}

/* Mission Grid */
.mission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
}

.mission-card {
    background: var(--gray-50);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    text-align: center;
    border: 1px solid var(--gray-200);
    transition: all 0.2s;
}

.mission-card:hover {
    transform: translateY(-3px);
    border-color: var(--green-400);
    box-shadow: var(--shadow-card);
}

.mission-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 28px;
}

.mission-icon.red { background: #fee2e2; color: #dc2626; }
.mission-icon.green { background: var(--green-100); color: var(--green-600); }
.mission-icon.blue { background: #dbeafe; color: #2563eb; }
.mission-icon.amber { background: #fef3c7; color: #d97706; }

.mission-card h4 {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--gray-900);
}

.mission-card p {
    font-size: 12px;
    color: var(--gray-500);
    line-height: 1.5;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-box {
    background: linear-gradient(135deg, var(--green-500), var(--green-600));
    border-radius: var(--radius-md);
    padding: 1.25rem;
    color: white;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 11px;
    opacity: 0.9;
}

/* Price Box */
.price-box {
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.price-box i {
    font-size: 30px;
    color: #ea580c;
}

.price-box p {
    margin: 0;
    font-size: 13px;
    color: var(--gray-700);
    line-height: 1.5;
    flex: 1;
}

.price-box strong {
    color: #ea580c;
}

/* Quote Section */
.quote-section {
    background: linear-gradient(135deg, var(--green-50), #fff);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    text-align: center;
    border: 1px solid var(--green-100);
}

.quote-text {
    font-size: 1rem;
    font-style: italic;
    color: var(--gray-700);
    line-height: 1.6;
    margin-bottom: 0.75rem;
}

.quote-author {
    font-size: 13px;
    color: var(--green-600);
    font-weight: 600;
}

/* Dark Mode */
body.dark-mode .creator-avatar {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .creator-title {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .contact-item:hover {
    border-color: #3b82f6;
}

body.dark-mode .contact-item:hover i {
    background: #3b82f6;
}

body.dark-mode .stat-box {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .quote-section {
    background: linear-gradient(135deg, #1e1e2e, #2a2a3a);
    border-color: #3a3a4a;
}

body.dark-mode .price-box {
    background: #1e1e2e;
    border-color: #3a3a4a;
}

body.dark-mode .price-box i {
    color: #60a5fa;
}

body.dark-mode .price-box strong {
    color: #60a5fa;
}

/* Responsive */
@media (max-width: 768px) {
    .creator-profile {
        flex-direction: column;
        text-align: center;
    }
    .creator-title {
        margin: 0 auto 0.75rem;
    }
    .contact-info {
        justify-content: center;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .creator-name {
        font-size: 1.4rem;
    }
    .creator-avatar {
        width: 110px;
        height: 110px;
    }
}
</style>

<div class="about-page">
<div class="container" style="max-width: 1000px;">

    <!-- Creator Profile Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <h5><?= __('meet_creator') ?></h5>
                <p><?= __('vision_behind') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="creator-profile">
                <div class="creator-avatar">
                    <img src="<?= $creator_profile_pic ?>" alt="Profile Picture">
                </div>
                <div class="creator-info">
                    <h2 class="creator-name"><?= htmlspecialchars($creator_name) ?></h2>
                    <div class="creator-title">
                        <i class="fas fa-crown"></i> <?= __('founder_developer') ?>
                    </div>
                    <p class="creator-bio">
                        <?= __('creator_bio') ?>
                    </p>
                    <div class="contact-info">
                        <a href="tel:<?= $creator_phone ?>" class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <span><?= __('call') ?? 'Call' ?></span>
                        </a>
                        <a href="mailto:<?= $creator_email ?>" class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?= __('email') ?></span>
                        </a>
                        <a href="https://wa.me/923320829327" target="_blank" class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission & Vision Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-bullseye"></i>
            </div>
            <div>
                <h5><?= __('our_mission_vision') ?></h5>
                <p><?= __('why_exists') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon red"><i class="fas fa-trash-alt"></i></div>
                    <h4><?= __('stop_food_waste') ?></h4>
                    <p><?= __('stop_food_waste_desc') ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon green"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4><?= __('earn_rewards') ?></h4>
                    <p><?= __('earn_rewards_desc') ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon blue"><i class="fas fa-chart-line"></i></div>
                    <h4><?= __('affordable_access') ?></h4>
                    <p><?= __('affordable_access_desc') ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon amber"><i class="fas fa-shield-alt"></i></div>
                    <h4><?= __('respect_dignity') ?></h4>
                    <p><?= __('respect_dignity_desc') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Purpose Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <div>
                <h5><?= __('what_is_zero_hunger') ?></h5>
                <p><?= __('platform_purpose') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <p style="font-size: 14px; line-height: 1.7; color: var(--gray-700); margin-bottom: 1rem;">
                <?= __('zero_hunger_desc') ?>
            </p>
            <p style="font-size: 14px; line-height: 1.7; color: var(--gray-700); margin-bottom: 1rem;">
                <?= __('zero_hunger_desc2') ?>
            </p>
            <div class="price-box mt-3">
                <i class="fas fa-tag"></i>
                <p>
                    <strong><?= __('why_nominal_pricing') ?></strong> <?= __('nominal_pricing_desc') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- How It Works Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div>
                <h5><?= __('how_it_works_title') ?></h5>
                <p><?= __('simple_3_step') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon green"><i class="fas fa-utensils"></i></div>
                    <h4><?= __('step1_how') ?></h4>
                    <p><?= __('step1_how_desc') ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon blue"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4><?= __('step2_how') ?></h4>
                    <p><?= __('step2_how_desc') ?></p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon amber"><i class="fas fa-truck"></i></div>
                    <h4><?= __('step3_how') ?></h4>
                    <p><?= __('step3_how_desc') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Impact Stats Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-chart-simple"></i>
            </div>
            <div>
                <h5><?= __('our_impact') ?></h5>
                <p><?= __('making_difference') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="stats-grid">
                <?php
                $total_donations = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM food_donations"))[0] ?? 0;
                $total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0] ?? 0;
                $total_deliveries = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM requests WHERE delivery_status = 'Delivered'"))[0] ?? 0;
                $total_ngos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role_id = 3"))[0] ?? 0;
                ?>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($total_donations) ?></div>
                    <div class="stat-label"><?= __('food_items_donated_count') ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($total_users) ?></div>
                    <div class="stat-label"><?= __('registered_users_count') ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($total_deliveries) ?></div>
                    <div class="stat-label"><?= __('successful_deliveries') ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?= number_format($total_ngos) ?></div>
                    <div class="stat-label"><?= __('active_ngo_partners') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quote Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-quote-left"></i>
            </div>
            <div>
                <h5><?= __('our_inspiration') ?></h5>
                <p><?= __('words_drive_us') ?></p>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="quote-section">
                <div class="quote-text">
                    <i class="fas fa-quote-left me-2" style="color: var(--green-400);"></i>
                    <?= __('quote_text') ?>
                    <i class="fas fa-quote-right ms-2" style="color: var(--green-400);"></i>
                </div>
                <div class="quote-author">
                    — <?= __('quote_author') ?>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<?php include 'footer.php'; ?>