<?php
include '../Backend/db.php';
include 'header.php'; // First - defines __() function

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pageTitle = __('my_profile') . ' - ' . __('site_title');
$message = "";
$error = "";

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $bio = trim($_POST['bio']);
    
    $update = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=?, bio=? WHERE user_id=?");
    $update->bind_param("ssssi", $full_name, $phone, $address, $bio, $user_id);
    
    if ($update->execute()) {
        $_SESSION['full_name'] = $full_name;
        $message = __('profile_update_success');
        logActivity($user_id, $_SESSION['role_id'], "profile_update", "Profile information updated");
    } else {
        $error = __('profile_update_failed');
    }
}

// Update password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if (password_verify($current, $user['password_hash'])) {
        if (strlen($new) < 6) {
            $error = __('password_min_length');
        } elseif ($new !== $confirm) {
            $error = __('password_mismatch');
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            mysqli_query($conn, "UPDATE users SET password_hash = '$hash' WHERE user_id = $user_id");
            $message = __('password_update_success');
            logActivity($user_id, $_SESSION['role_id'], "password_change", "Password changed");
        }
    } else {
        $error = __('current_password_incorrect');
    }
}

// Upload profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $upload_dir = '../uploads/profiles/';
    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed) && $_FILES['profile_pic']['size'] < 2000000) {
        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
            mysqli_query($conn, "UPDATE users SET profile_pic = 'uploads/profiles/$filename' WHERE user_id = $user_id");
            $message = __('profile_pic_updated');
            logActivity($user_id, $_SESSION['role_id'], "profile_pic", "Profile picture changed");
            $user['profile_pic'] = 'uploads/profiles/' . $filename;
            $_SESSION['profile_pic'] = 'uploads/profiles/' . $filename;
        } else {
            $error = __('upload_failed');
        }
    } else {
        $error = __('invalid_file_type');
    }
}

$role_names = [1=>'Admin', 2=>__('donors'), 3=>__('ngos'), 4=>__('riders')];
$role_icons = [1=>'fa-shield-alt', 2=>'fa-hand-holding-heart', 3=>'fa-building', 4=>'fa-motorcycle'];
?>

<style>
    :root {
        --primary: #2e9458;
        --primary-dark: #226e42;
        --primary-light: #d6f0e0;
        --gray-50: #f8f9fc;
        --gray-100: #f1f3f9;
        --gray-200: #e4e7ed;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    body {
        background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
        min-height: 100vh;
    }

    .profile-wrapper {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .profile-card {
        background: #ffffff;
        border-radius: 32px;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.25);
    }

    .profile-header {
        background: linear-gradient(135deg, #1e5631 0%, #2e7d32 50%, #388e3c 100%);
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
        color: white;
    }

    body.dark-mode .profile-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        pointer-events: none;
    }

    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .avatar-container {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .profile-avatar {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--shadow-lg);
        margin: 0 auto 1rem;
        overflow: hidden;
        background: linear-gradient(135deg, #fff, #f0f0f0);
        transition: all 0.3s ease;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar i {
        font-size: 70px;
        color: var(--primary);
        line-height: 140px;
    }

    .upload-btn-wrapper {
        position: relative;
        display: inline-block;
    }

    .upload-btn {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255, 255, 255, 0.5);
        border-radius: 50px;
        padding: 10px 24px;
        color: white;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .upload-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        border-color: white;
        transform: translateY(-2px);
    }

    .profile-name {
        margin-top: 1rem;
    }

    .profile-name h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.02em;
        color: white;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 8px;
        color: white;
    }

    .profile-body {
        padding: 2rem 2.5rem;
        background: white;
    }

    .form-section {
        background: var(--gray-50);
        border-radius: 24px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
    }

    .form-section:hover {
        border-color: var(--primary-light);
        box-shadow: var(--shadow-md);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--gray-200);
    }

    .section-title i {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 12px;
        font-size: 18px;
    }

    body.dark-mode .section-title i {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .section-title h4 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--gray-800);
    }

    .section-title p {
        margin: 0;
        font-size: 12px;
        color: var(--gray-500);
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-label i {
        width: 20px;
        color: var(--primary);
        margin-right: 6px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 14px;
        border: 1.5px solid var(--gray-200);
        border-radius: 16px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(46, 148, 88, 0.15);
    }

    .form-control[disabled] {
        background: var(--gray-100);
        color: var(--gray-500);
        cursor: not-allowed;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .password-strength {
        margin-top: 8px;
        height: 4px;
        background: var(--gray-200);
        border-radius: 4px;
        overflow: hidden;
    }

    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width 0.3s ease;
    }

    .strength-text {
        font-size: 11px;
        margin-top: 6px;
    }

    .btn-save {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: none;
        border-radius: 50px;
        color: white;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    body.dark-mode .btn-save {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(46, 148, 88, 0.3);
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: linear-gradient(135deg, var(--primary-light), #fff);
        padding: 1rem;
        border-radius: 20px;
        text-align: center;
        border: 1px solid rgba(46, 148, 88, 0.2);
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .stat-label {
        font-size: 11px;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #profilePicInput {
        display: none;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 2rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid var(--gray-200);
    }

    .divider span {
        padding: 0 1rem;
        color: var(--gray-400);
        font-size: 12px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .profile-body {
            padding: 1.5rem;
        }
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }
        .stat-number {
            font-size: 1rem;
        }
        .stat-label {
            font-size: 9px;
        }
        .profile-header {
            padding: 1.5rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
        }
        .profile-avatar i {
            font-size: 50px;
            line-height: 100px;
        }
    }

    /* ========== DARK MODE ADDITIONS FOR PROFILE.PHP ========== */
body.dark-mode {
    background: #0a0a0f;
}

body.dark-mode .profile-card {
    background: #1e1e2e;
    border: 1px solid #3a3a4a;
}

body.dark-mode .profile-body {
    background: #1e1e2e;
}

body.dark-mode .form-section {
    background: #2a2a3a;
    border-color: #3a3a4a;
}

body.dark-mode .form-section:hover {
    border-color: #3b82f6;
}

body.dark-mode .section-title h4,
body.dark-mode .section-title p {
    color: #e6edf3;
}

body.dark-mode .section-title {
    border-bottom-color: #3a3a4a;
}

body.dark-mode .form-label {
    color: #e6edf3;
}

body.dark-mode .form-label i {
    color: #60a5fa;
}

body.dark-mode .form-control {
    background: #1e1e2e;
    border-color: #3a3a4a;
    color: #fff;
}

body.dark-mode .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

body.dark-mode .form-control[disabled] {
    background: #2a2a3a;
    color: #8b949e;
}

body.dark-mode .form-control::placeholder {
    color: #6b7280;
}

body.dark-mode .stats-grid .stat-item {
    background: #2a2a3a;
    border-color: #3a3a4a;
}

body.dark-mode .stats-grid .stat-number {
    color: #60a5fa;
}

body.dark-mode .stats-grid .stat-label {
    color: #8b949e;
}

body.dark-mode .divider span {
    color: #8b949e;
}

body.dark-mode .divider::before,
body.dark-mode .divider::after {
    border-bottom-color: #3a3a4a;
}

body.dark-mode .alert-success {
    background: rgba(59, 130, 246, 0.15);
    border-color: rgba(59, 130, 246, 0.3);
    color: #60a5fa;
}

body.dark-mode .alert-danger {
    background: rgba(248, 113, 113, 0.15);
    border-color: rgba(248, 113, 113, 0.3);
    color: #f87171;
}

body.dark-mode .text-muted {
    color: #8b949e !important;
}

body.dark-mode .text-warning {
    color: #fbbf24 !important;
}

body.dark-mode .btn-save {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .btn-save:hover {
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

body.dark-mode .password-strength {
    background: #3a3a4a;
}

body.dark-mode .strength-text span {
    color: #8b949e !important;
}
</style>

<div class="profile-wrapper">
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-container">
                <div class="profile-avatar" id="avatarPreview">
                    <?php if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])): ?>
                        <img src="../<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                
                <div class="upload-btn-wrapper">
                    <button type="button" class="upload-btn" onclick="document.getElementById('profilePicInput').click()">
                        <i class="fas fa-camera"></i> 
                        <span><?= __('change_profile_picture') ?></span>
                    </button>
                </div>
                
                <div class="profile-name">
                    <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                    <div class="role-badge">
                        <i class="fas <?= $role_icons[$user['role_id']] ?? 'fa-user' ?>"></i>
                        <?= $role_names[$user['role_id']] ?? __('user') ?>
                    </div>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="profile_pic" id="profilePicInput" accept="image/*" onchange="this.form.submit()">
            </form>
        </div>
        
        <div class="profile-body">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle" style="font-size: 18px;"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= date('Y') ?></div>
                    <div class="stat-label"><?= __('member_since') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $user['email_verified'] ? '✓' : '⏳' ?></div>
                    <div class="stat-label"><?= __('verified') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $user['is_active'] ? __('active') : __('inactive') ?></div>
                    <div class="stat-label"><?= __('status') ?></div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user-edit"></i>
                    <div>
                        <h4><?= __('personal_information') ?></h4>
                        <p><?= __('update_profile_details') ?></p>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-user"></i> <?= __('full_name') ?></label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-envelope"></i> <?= __('email_address') ?></label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <small style="font-size: 11px; color: var(--gray-400);"><?= __('email_cannot_change') ?></small>
                    </div>
                    
                    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-phone"></i> <?= __('phone_number') ?></label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+92 XXX XXXXXXX">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-map-marker-alt"></i> <?= __('address') ?></label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="<?= __('your_full_address') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-info-circle"></i> <?= __('bio_about') ?></label>
                        <textarea name="bio" class="form-control" placeholder="<?= __('tell_about_yourself') ?>"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> <?= __('save_changes') ?></button>
                </form>
            </div>
            
            <div class="divider"><span><?= __('security') ?></span></div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-lock"></i>
                    <div>
                        <h4><?= __('change_password') ?></h4>
                        <p><?= __('update_security') ?></p>
                    </div>
                </div>
                
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="update_password" value="1">
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-key"></i> <?= __('current_password') ?></label>
                        <input type="password" name="current_password" class="form-control" placeholder="<?= __('enter_current_password') ?>" required>
                    </div>
                    
                    <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-plus-circle"></i> <?= __('new_password') ?></label>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="<?= __('min_6_characters') ?>" required>
                            <div class="password-strength"><div class="strength-bar" id="strengthBar"></div></div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-check-circle"></i> <?= __('confirm_password') ?></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="<?= __('confirm_new_password') ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-save"><i class="fas fa-sync-alt"></i> <?= __('update_password') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="form-section" style="max-width: 1000px; margin: 1rem auto;">
    <div class="section-title">
        <i class="fas fa-map-marker-alt"></i>
        <div>
            <h4><?= __('your_service_area') ?></h4>
            <p><?= __('manage_location') ?></p>
        </div>
    </div>
    
    <?php
    include '../Backend/location_functions.php';
    $user_loc = getUserLocation($conn, $user_id);
    ?>
    <div class="form-group">
        <label class="form-label"><?= __('current_location') ?></label>
        <?php if ($user_loc['lat'] && $user_loc['lng']): ?>
            <p class="text-muted">
                <i class="fas fa-location-dot text-success"></i>
                <strong><?= __('radius') ?>:</strong> <?= $user_loc['radius'] ?> KM<br>
                <strong><?= __('coordinates') ?>:</strong> <?= $user_loc['lat'] ?>, <?= $user_loc['lng'] ?><br>
                <small class="text-muted"><?= __('radius_info') ?> <?= $user_loc['radius'] ?> KM.</small>
            </p>
        <?php else: ?>
            <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> <?= __('no_location_set') ?></p>
        <?php endif; ?>
    </div>
    <a href="location_setup.php" class="btn-save" style="background: #6c757d; margin-top: 10px;">
        <i class="fas fa-map-marker-alt"></i> <?= __('update_location') ?>
    </a>
</div>

<script>
const newPassword = document.getElementById('new_password');
const strengthBar = document.getElementById('strengthBar');
const strengthText = document.getElementById('strengthText');

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = '';
        strengthText.innerHTML = '';
        return;
    }
    
    if (strength <= 1) {
        strengthBar.style.width = '25%';
        strengthBar.style.backgroundColor = '#dc3545';
        strengthText.innerHTML = '<span style="color:#dc3545;">⚠️ <?= __('weak_password') ?></span>';
    } else if (strength === 2) {
        strengthBar.style.width = '50%';
        strengthBar.style.backgroundColor = '#ffc107';
        strengthText.innerHTML = '<span style="color:#ffc107;">⚠️ <?= __('fair_password') ?></span>';
    } else if (strength === 3) {
        strengthBar.style.width = '75%';
        strengthBar.style.backgroundColor = '#17a2b8';
        strengthText.innerHTML = '<span style="color:#17a2b8;">✓ <?= __('good_password') ?></span>';
    } else {
        strengthBar.style.width = '100%';
        strengthBar.style.backgroundColor = '#28a745';
        strengthText.innerHTML = '<span style="color:#28a745;">✓ <?= __('strong_password') ?></span>';
    }
}

if (newPassword) {
    newPassword.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
}

document.getElementById('profilePicInput')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php include 'footer.php'; ?>