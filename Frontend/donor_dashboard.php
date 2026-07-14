<?php
include '../Backend/db.php';
include 'header.php'; // First - defines __() function
$pageTitle = __('dashboard') . ' - ' . __('site_title');

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 2) {
    header('Location: login.php');
    exit();
}

$donor_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Include location functions
include '../Backend/location_functions.php';

// Check if user has location set
$user_location = getUserLocation($conn, $donor_id);
if (empty($user_location['lat']) || empty($user_location['lng'])) {
    header('Location: location_setup.php');
    exit();
}

$_SESSION['user_lat'] = $user_location['lat'];
$_SESSION['user_lng'] = $user_location['lng'];
$_SESSION['user_radius'] = $user_location['radius'];

if (isset($_POST['add_donation'])) {
    $food_item = mysqli_real_escape_string($conn, trim($_POST['food_item']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $quantity = mysqli_real_escape_string($conn, trim($_POST['quantity']));
    $expiry_time = mysqli_real_escape_string($conn, $_POST['expiry_time']);
    $pickup_location = mysqli_real_escape_string($conn, trim($_POST['pickup_location']));
    $price = intval($_POST['price']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);
    $status = 'Available';

    if ($price < 100 || $price > 500) {
        $error = __('price_error');
    } elseif (empty($food_item) || empty($quantity) || empty($expiry_time) || empty($pickup_location)) {
        $error = __('required_fields_error');
    } else {
        $stmt = $conn->prepare("INSERT INTO food_donations (donor_id, food_item, description, quantity, expiry_time, pickup_location, price, status, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssisss", $donor_id, $food_item, $description, $quantity, $expiry_time, $pickup_location, $price, $status, $lat, $lng);
        if ($stmt->execute()) {
            $success = __('donation_success');
        } else {
            $error = __('donation_failed');
        }
        $stmt->close();
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM food_donations WHERE donation_id = $delete_id AND donor_id = $donor_id");
    header("Location: donor_dashboard.php");
    exit();
}

$my_donations = mysqli_query($conn, "SELECT * FROM food_donations WHERE donor_id = $donor_id ORDER BY donation_id DESC");

$unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE receiver_id = $donor_id AND is_read = 0"))['cnt'] ?? 0;

$ngo_contacts = mysqli_query($conn, "
    SELECT DISTINCT u.user_id, u.full_name, u.profile_pic, fd.food_item,
           (SELECT COUNT(*) FROM messages WHERE sender_id = u.user_id AND receiver_id = $donor_id AND is_read = 0) as unread
    FROM requests r
    JOIN food_donations fd ON r.donation_id = fd.donation_id
    JOIN users u ON r.receiver_id = u.user_id
    WHERE fd.donor_id = $donor_id
    ORDER BY r.request_id DESC
    LIMIT 10
");
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

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
    }

    .dh-page { background: var(--gray-100); min-height: 100vh; padding: 2rem 0 3rem; }
    
    /* CARD HEADER - GREEN LIKE LOGIN PAGE */
    .dh-card { background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 1.25rem; }
    .dh-card-header { display: flex; align-items: center; gap: 10px; padding: 1rem 1.25rem; background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; border-bottom: none; }
    .dh-header-icon { width: 34px; height: 34px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; background: rgba(255,255,255,0.2); color: white; }
    .dh-card-header h5 { margin: 0; font-size: 15px; font-weight: 600; color: white; }
    .dh-card-header p { margin: 0; font-size: 12px; color: rgba(255,255,255,0.85); }
    .dh-card-header-right { margin-left: auto; }
    
    /* Dark Mode - Card Headers Blue */
    body.dark-mode .dh-card-header { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    
    .dh-card-body { padding: 1.25rem; }

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .profile-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 40px;
        text-decoration: none;
        transition: all 0.2s;
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }
    .profile-btn:hover {
        background: var(--green-50);
        border-color: var(--green-400);
        color: var(--green-600);
        transform: translateY(-2px);
    }

    .inbox-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; font-size:13px; font-weight:600; color:#2563eb; background:#eff6ff; border:1.5px solid #dbeafe; border-radius:var(--radius-sm); text-decoration:none; transition:background .12s; }
    .inbox-btn:hover { background:#dbeafe; color:#1d4ed8; }
    .inbox-btn .badge-count { background:#e85a30; color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:10px; }

    .ngo-contact-row { display:flex; align-items:center; gap:10px; padding:.7rem 1rem; border-bottom:1px solid var(--gray-100); transition:background .1s; }
    .ngo-contact-row:last-child { border-bottom:none; }
    .ngo-contact-row:hover { background:var(--gray-50); }
    .ngo-av { width:36px; height:36px; border-radius:50%; background:#dbeafe; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; overflow:hidden; }
    .ngo-av img { width:100%; height:100%; object-fit:cover; }
    .ngo-info { flex:1; min-width:0; }
    .ngo-name { font-size:13.5px; font-weight:600; color:var(--gray-900); }
    .ngo-food { font-size:11.5px; color:var(--gray-400); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .chat-btn { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; font-size:12px; font-weight:600; color:#2563eb; background:#eff6ff; border:1px solid #dbeafe; border-radius:6px; text-decoration:none; transition:background .12s; white-space:nowrap; position:relative; }
    .chat-btn:hover { background:#dbeafe; color:#1d4ed8; }
    .chat-btn .notif-dot { position:absolute; top:-3px; right:-3px; width:8px; height:8px; background:#e85a30; border-radius:50%; border:2px solid #fff; }
    
    .dh-field { margin-bottom: 1rem; }
    .dh-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--gray-700); margin-bottom: 5px; }
    .dh-label .req { color: var(--green-500); margin-left: 2px; }
    .dh-input, .dh-select, .dh-textarea { width: 100%; padding: 9px 12px; font-size: 13.5px; color: var(--gray-900); background: #fff; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit; box-sizing: border-box; }
    .dh-input:focus, .dh-select:focus, .dh-textarea:focus { border-color: var(--green-400); box-shadow: 0 0 0 3px rgba(46,148,88,.18); }
    .dh-textarea { resize: vertical; min-height: 72px; }
    .dh-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236c757d' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }
    
    .search-container { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-input { flex: 1; padding: 10px 12px; font-size: 13px; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); outline: none; }
    .search-input:focus { border-color: var(--green-400); box-shadow: 0 0 0 3px rgba(46,148,88,.18); }
    .search-btn { padding: 10px 18px; background: var(--green-500); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; transition: background 0.2s; }
    .search-btn:hover { background: var(--green-600); }
    
    /* Map Container with relative position for button */
    .map-container {
        position: relative;
    }
    #map { height: 250px; width: 100%; border-radius: var(--radius-sm); border: 1.5px solid var(--gray-300); margin-bottom: 10px; }
    
    /* Current Location Button - Floating Circle Button */
    .current-loc-btn {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 1px solid var(--gray-300);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        z-index: 1000;
        font-size: 18px;
        color: var(--green-600);
    }
    .current-loc-btn:hover {
        background: var(--green-50);
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .current-loc-btn:active {
        transform: scale(0.95);
    }
    body.dark-mode .current-loc-btn {
        background: #2a2a3a;
        border-color: #3a3a4a;
        color: #3b82f6;
    }
    body.dark-mode .current-loc-btn:hover {
        background: #1e1e2e;
    }
    
    .address-preview { background: var(--green-50); border: 1px solid var(--green-100); border-radius: var(--radius-sm); padding: 8px 12px; font-size: 12px; color: var(--green-700); margin-top: 8px; }
    
    .dh-input-hint { font-size: 11.5px; color: var(--gray-400); margin-top: 4px; }
    .dh-btn { display: block; width: 100%; padding: 10px; font-size: 14px; font-weight: 600; color: #fff; background: var(--green-500); border: none; border-radius: var(--radius-sm); cursor: pointer; transition: background .15s; }
    .dh-btn:hover { background: var(--green-600); }
    .dh-alert { padding: 9px 12px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; }
    .dh-alert.danger { background: #fff0f0; border: 1px solid #ffc9c9; color: #c0392b; }
    .dh-alert.success { background: var(--green-50); border: 1px solid var(--green-100); color: var(--green-600); }
    .dh-divider { height: 1px; background: var(--gray-200); margin: 1rem 0; }
    
    .dh-table-wrap { overflow-x: auto; }
    .dh-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .dh-table thead th { background: var(--gray-50); padding: 10px 12px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: var(--gray-500); border-bottom: 1.5px solid var(--gray-200); text-align: left; }
    .dh-table tbody tr { border-bottom: 1px solid var(--gray-100); }
    .dh-table tbody tr:hover { background: var(--gray-50); }
    .dh-table td { padding: 11px 12px; color: var(--gray-700); vertical-align: middle; }
    .dh-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
    .dh-badge.available { background: var(--green-100); color: var(--green-700); }
    .dh-badge.claimed { background: var(--gray-200); color: var(--gray-500); }
    .dh-del-btn { font-size: 12px; font-weight: 600; color: #c0392b; background: #fff0f0; border: 1px solid #ffc9c9; border-radius: 6px; padding: 4px 10px; text-decoration: none; display: inline-block; }
    .dh-del-btn:hover { background: #ffe0e0; }
    .dh-empty { padding: 2.5rem 1rem; text-align: center; color: var(--gray-400); }
    .dh-empty i { font-size: 36px; display: block; margin-bottom: .5rem; }
    .dh-stars { display: flex; gap: 6px; margin-top: 6px; }
    .dh-star-btn { background: none; border: none; cursor: pointer; font-size: 22px; color: var(--gray-300); padding: 0; transition: color .12s; }
    .dh-star-btn.active, .dh-star-btn:hover { color: #f59e0b; }
    
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #fff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.6s linear infinite;
        margin-left: 8px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<div class="dh-page">
<div class="container" style="max-width: 1180px;">
    
    <!-- Profile Actions Row -->
    <div class="profile-actions">
        <a href="profile.php" class="profile-btn"><i class="fas fa-user-edit"></i> <?= __('my_profile') ?></a>
        <a href="messages.php" class="profile-btn">
            <i class="fas fa-envelope"></i> <?= __('messages_unread') ?>
            <?php if($unread > 0): ?>
                <span class="badge bg-danger ms-1" style="font-size: 10px;"><?= $unread ?></span>
            <?php endif; ?>
        </a>
    </div>
    
    <div class="row g-4">
        <!-- LEFT: Donate Food Form -->
        <div class="col-md-4">
            <div class="dh-card">
                <div class="dh-card-header">
                    <div class="dh-header-icon"><i class="fas fa-seedling"></i></div>
                    <div><h5><?= __('donate_surplus_food') ?></h5><p><?= __('fill_details_list') ?></p></div>
                </div>
                <div class="dh-card-body">
                    <?php if (!empty($error)): ?>
                    <div class="dh-alert danger"><i class="fas fa-exclamation-circle"></i> <?= $error; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                    <div class="dh-alert success"><i class="fas fa-check-circle"></i> <?= $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" id="donationForm">
                        <div class="dh-field">
                            <label class="dh-label"><?= __('food_item_name') ?> <span class="req">*</span></label>
                            <input type="text" name="food_item" class="dh-input" placeholder="<?= __('eg_biryani') ?>" required>
                        </div>

                        <div class="row g-2">
                            <div class="col-7">
                                <div class="dh-field">
                                    <label class="dh-label"><?= __('quantity') ?> <span class="req">*</span></label>
                                    <input type="text" name="quantity" class="dh-input" placeholder="e.g. 10 plates" required>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="dh-field">
                                    <label class="dh-label"><?= __('price') ?> (PKR) <span class="req">*</span></label>
                                    <input type="number" name="price" min="100" max="500" class="dh-input" placeholder="100–500" required>
                                    <div class="dh-input-hint"><?= __('price_between') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="dh-field">
                            <label class="dh-label"><?= __('pin_location_map') ?> <span class="req">*</span></label>
                            <div class="search-container">
                                <input type="text" id="searchInput" class="search-input" placeholder="<?= __('search_location') ?>">
                                <button type="button" class="search-btn" id="searchBtn"><i class="fas fa-search"></i> Search</button>
                            </div>
                            <div class="map-container">
                                <div id="map"></div>
                                <button type="button" class="current-loc-btn" id="currentLocationBtn" title="<?= __('my_location') ?>">
                                    <i class="fas fa-location-dot"></i>
                                </button>
                            </div>
                            <div id="addressPreview" class="address-preview">
                                <i class="fas fa-map-marker-alt"></i> <span id="selectedAddress"><?= __('select_location') ?></span>
                            </div>
                        </div>

                        <div class="dh-field">
                            <label class="dh-label"><?= __('pickup_address') ?> <span class="req">*</span></label>
                            <input type="text" name="pickup_location" id="pickup_location" class="dh-input" placeholder="<?= __('address_autofill') ?>" required readonly>
                        </div>

                        <input type="hidden" name="lat" id="lat" value="24.8607">
                        <input type="hidden" name="lng" id="lng" value="67.0011">

                        <div class="dh-field">
                            <label class="dh-label"><?= __('expiry_date_time') ?> <span class="req">*</span></label>
                            <input type="datetime-local" name="expiry_time" class="dh-input" required>
                        </div>

                        <div class="dh-field">
                            <label class="dh-label"><?= __('description_optional') ?></label>
                            <textarea name="description" class="dh-textarea" placeholder="<?= __('additional_details') ?>"></textarea>
                        </div>

                        <button type="submit" name="add_donation" class="dh-btn"><i class="fas fa-paper-plane me-2"></i><?= __('publish_donation') ?></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: Table + Feedback -->
        <div class="col-md-8 d-flex flex-column gap-4">
            <!-- Donations Table -->
            <div class="dh-card">
                <div class="dh-card-header">
                    <div class="dh-header-icon"><i class="fas fa-list-alt"></i></div>
                    <div><h5><?= __('my_donation_logs') ?></h5><p><?= __('track_status') ?></p></div>
                </div>
                <div class="dh-table-wrap">
                    <table class="dh-table">
                        <thead><tr><th style="width:8%"><?= __('id') ?></th><th style="width:28%"><?= __('food_item') ?></th><th style="width:12%"><?= __('price_pkr') ?></th><th style="width:18%"><?= __('quantity') ?></th><th style="width:14%"><?= __('status') ?></th><th style="width:20%"><?= __('action') ?></th></tr></thead>
                        <tbody>
                        <?php if ($my_donations && mysqli_num_rows($my_donations) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($my_donations)): ?>
                            <tr>
                                <td style="color:var(--gray-400);">#<?= $row['donation_id'] ?></td>
                                <td class="food-name"><?= htmlspecialchars($row['food_item']) ?></td>
                                <td><?= $row['price'] ?> PKR</td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><span class="dh-badge <?= $row['status'] == 'Available' ? 'available' : 'claimed' ?>"><?= $row['status'] ?></span></td>
                                <td><?php if ($row['status'] == 'Available'): ?>
                                        <a href="donor_dashboard.php?delete_id=<?= $row['donation_id'] ?>" class="dh-del-btn" onclick="return confirm('<?= __('remove_listing') ?>');"><i class="fas fa-trash-alt"></i> <?= __('delete') ?></a>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= __('locked') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-box-open"></i> <?= __('no_donations_yet') ?></div></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- NGO Contacts Card -->
            <div class="dh-card">
                <div class="dh-card-header">
                    <div class="dh-header-icon"><i class="fas fa-building"></i></div>
                    <div><h5><?= __('ngo_contacts') ?></h5><p><?= __('ngos_connected') ?></p></div>
                </div>
                <?php
                $ngo_count = $ngo_contacts ? mysqli_num_rows($ngo_contacts) : 0;
                if($ngo_count > 0):
                    while($ngo = mysqli_fetch_assoc($ngo_contacts)):
                        $ni = strtoupper(substr($ngo['full_name'], 0, 1));
                ?>
                <div class="ngo-contact-row">
                    <div class="ngo-av">
                        <?php if(!empty($ngo['profile_pic']) && file_exists('../'.$ngo['profile_pic'])): ?>
                            <img src="../<?= $ngo['profile_pic'] ?>" alt="">
                        <?php else: ?>
                            <?= $ni ?>
                        <?php endif; ?>
                    </div>
                    <div class="ngo-info">
                        <div class="ngo-name"><?= htmlspecialchars($ngo['full_name']) ?></div>
                        <div class="ngo-food"><i class="fas fa-utensils" style="font-size:10px;margin-right:3px;"></i><?= htmlspecialchars($ngo['food_item']) ?></div>
                    </div>
                    <a href="messages.php?to=<?= $ngo['user_id'] ?>" class="chat-btn">
                        <i class="fas fa-comment-dots"></i> <?= __('chat') ?>
                        <?php if($ngo['unread'] > 0): ?><span class="notif-dot"></span><?php endif; ?>
                    </a>
                </div>
                <?php endwhile; else: ?>
                <div style="padding:1.5rem 1rem;text-align:center;color:var(--gray-400);font-size:13px;">
                    <i class="fas fa-building" style="font-size:28px;display:block;margin-bottom:.4rem;color:var(--gray-300);"></i>
                    <?= __('no_ngos_connected') ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Feedback Form -->
            <div class="dh-card">
                <div class="dh-card-header">
                    <div class="dh-header-icon"><i class="fas fa-comment-dots"></i></div>
                    <div><h5><?= __('community_feedback') ?></h5><p><?= __('share_experience') ?></p></div>
                </div>
                <div class="dh-card-body">
                    <form method="POST" action="submit_feedback.php">
                        <div class="dh-field">
                            <label class="dh-label"><?= __('your_message') ?> <span class="req">*</span></label>
                            <textarea name="message" class="dh-textarea" rows="3" placeholder="<?= __('tell_us_experience') ?>" required></textarea>
                        </div>
                        <div class="dh-field">
                            <label class="dh-label"><?= __('rating') ?></label>
                            <div class="dh-stars" id="starContainer">
                                <button type="button" class="dh-star-btn" data-val="1">★</button>
                                <button type="button" class="dh-star-btn" data-val="2">★</button>
                                <button type="button" class="dh-star-btn" data-val="3">★</button>
                                <button type="button" class="dh-star-btn" data-val="4">★</button>
                                <button type="button" class="dh-star-btn" data-val="5">★</button>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                        </div>
                        <div class="dh-divider"></div>
                        <button type="submit" class="dh-btn" style="background:#1b5e20;"><i class="fas fa-paper-plane me-2"></i><?= __('submit_feedback') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var map, marker;

function initMap() {
    map = L.map('map').setView([24.8607, 67.0011], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);
    marker = L.marker([24.8607, 67.0011], {draggable: true}).addTo(map);
    marker.on('dragend', function(e) { var pos = e.target.getLatLng(); document.getElementById('lat').value = pos.lat; document.getElementById('lng').value = pos.lng; getAddress(pos.lat, pos.lng); });
    map.on('click', function(e) { marker.setLatLng(e.latlng); document.getElementById('lat').value = e.latlng.lat; document.getElementById('lng').value = e.latlng.lng; getAddress(e.latlng.lat, e.latlng.lng); });
    getAddress(24.8607, 67.0011);
}

function centerOnCurrentLocation() {
    if (navigator.geolocation) {
        const btn = document.getElementById('currentLocationBtn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                const tempMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'current-location-marker',
                        html: '<div style="background:#3b82f6; width:14px; height:14px; border-radius:50%; border:2px solid white; box-shadow:0 0 0 2px #3b82f6;"></div>',
                        iconSize: [14, 14],
                        popupAnchor: [0, -7]
                    })
                }).addTo(map).bindPopup('<?= __('your_current_location') ?>').openPopup();
                setTimeout(function() { map.removeLayer(tempMarker); }, 3000);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            },
            function(error) {
                let errorMsg = "<?= __('could_not_detect_location') ?>";
                alert(errorMsg);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        );
    } else {
        alert("<?= __('geolocation_not_supported') ?>");
    }
}

async function getAddress(lat, lng) {
    try {
        document.getElementById('selectedAddress').innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __('loading_address') ?>';
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        const address = data.display_name || `${lat}, ${lng}`;
        document.getElementById('pickup_location').value = address;
        document.getElementById('selectedAddress').innerHTML = address;
    } catch(e) {
        document.getElementById('pickup_location').value = `${lat}, ${lng}`;
        document.getElementById('selectedAddress').innerHTML = `${lat}, ${lng}`;
    }
}

function selectLocation(lat, lng, address) {
    if (!lat || !lng) return;
    map.setView([parseFloat(lat), parseFloat(lng)], 16);
    marker.setLatLng([parseFloat(lat), parseFloat(lng)]);
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
    document.getElementById('pickup_location').value = address;
    document.getElementById('selectedAddress').innerHTML = address;
}

$(function() {
    $("#searchInput").autocomplete({
        source: function(request, response) {
            var searchTerm = request.term;
            if (searchTerm.length < 2) { response([]); return; }
            $("#searchBtn").html('<i class="fas fa-spinner fa-spin"></i>');
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search",
                dataType: "json",
                data: { q: searchTerm, format: "json", limit: 10, countrycodes: "pk", addressdetails: 1, dedupe: 1 },
                success: function(data) {
                    if (data && data.length > 0) {
                        response($.map(data, function(item) {
                            var shortLabel = item.display_name;
                            if (shortLabel.length > 80) shortLabel = shortLabel.substring(0, 77) + '...';
                            return { label: shortLabel, value: item.display_name, lat: item.lat, lon: item.lon };
                        }));
                    } else { response([{ label: "No results found. Try different keywords.", value: "", lat: null, lon: null }]); }
                    $("#searchBtn").html('<i class="fas fa-search"></i> Search');
                },
                error: function() { response([]); $("#searchBtn").html('<i class="fas fa-search"></i> Search'); }
            });
        },
        minLength: 2,
        select: function(event, ui) { if (ui.item.lat && ui.item.lon) selectLocation(ui.item.lat, ui.item.lon, ui.item.value); return false; }
    });
    $("#searchBtn").click(function() {
        var selectedValue = $("#searchInput").val();
        if (!selectedValue.trim()) { alert('<?= __('please_enter_location') ?>'); return; }
        if (selectedLat && selectedLon) selectLocation(selectedLat, selectedLon, selectedLocation);
        else performDirectSearch(selectedValue);
    });
    $("#searchInput").on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); $("#searchBtn").click(); } });
    
    document.getElementById('currentLocationBtn').addEventListener('click', centerOnCurrentLocation);
});

$(document).on('autocompleteselect', function(event, ui) {
    if (ui.item && ui.item.lat && ui.item.lon) { selectedLat = ui.item.lat; selectedLon = ui.item.lon; selectedLocation = ui.item.value; }
});

async function performDirectSearch(query) {
    $("#searchBtn").html('<i class="fas fa-spinner fa-spin"></i>');
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&countrycodes=pk`);
        const data = await response.json();
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat), lon = parseFloat(data[0].lon), displayName = data[0].display_name;
            selectLocation(lat, lon, displayName);
        } else { alert('<?= __('location_not_found') ?>'); }
    } catch(e) { alert('<?= __('error_searching_location') ?>'); console.error(e); }
    finally { $("#searchBtn").html('<i class="fas fa-search"></i> Search'); }
}

const stars = document.querySelectorAll('.dh-star-btn');
let current = 5;
function setStars(val) {
    stars.forEach(s => s.classList.toggle('active', parseInt(s.dataset.val) <= val));
    document.getElementById('ratingValue').value = val;
    current = val;
}
setStars(5);
stars.forEach(s => {
    s.addEventListener('click', () => setStars(parseInt(s.dataset.val)));
    s.addEventListener('mouseenter', () => stars.forEach(x => x.classList.toggle('active', parseInt(x.dataset.val) <= parseInt(s.dataset.val))));
    s.addEventListener('mouseleave', () => setStars(current));
});
document.addEventListener('DOMContentLoaded', function() { initMap(); });
</script>

<?php include 'footer.php'; ?>