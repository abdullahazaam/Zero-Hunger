<?php
// Start session first - NO OUTPUT before this
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../backend/db.php';

// Check login FIRST before including header
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 3) {
    header('Location: login.php'); 
    exit();
}
$ngo_id = $_SESSION['user_id'];

// Include location functions
include '../Backend/location_functions.php';

// Check if user has location set - BEFORE header
$user_location = getUserLocation($conn, $ngo_id);
if (empty($user_location['lat']) || empty($user_location['lng'])) {
    header('Location: location_setup.php');
    exit();
}

$_SESSION['user_lat'] = $user_location['lat'];
$_SESSION['user_lng'] = $user_location['lng'];
$_SESSION['user_radius'] = $user_location['radius'];
include 'header.php';
// NOW include header (after all redirects)
$pageTitle = __('dashboard') . ' - ' . __('site_title');


$my_requests = mysqli_query($conn, "
    SELECT r.*, fd.food_item, 
           u_rider.full_name as rider_name, u_rider.phone as rider_phone, 
           u_rider.bike_number, u_rider.profile_pic, u_rider.license_number,
           u_rider.user_id as rider_user_id, u_rider.avg_rating, u_rider.total_ratings
    FROM requests r 
    JOIN food_donations fd ON r.donation_id = fd.donation_id 
    LEFT JOIN users u_rider ON r.rider_id = u_rider.user_id 
    WHERE r.receiver_id = $ngo_id 
    ORDER BY r.request_id DESC
");

$unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE receiver_id = $ngo_id AND is_read = 0"))['cnt'] ?? 0;

function displayStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star" style="color:#f59e0b; font-size:11px;"></i>';
        } else {
            $stars .= '<i class="far fa-star" style="color:#dee2e6; font-size:11px;"></i>';
        }
    }
    return $stars;
}
?>

<style>
:root {
    --green-50:#f0faf4; --green-100:#d6f0e0; --green-400:#3aad6a;
    --green-500:#2e9458; --green-600:#226e42; --green-700:#174d2e;
    --blue-50:#eff6ff; --blue-100:#dbeafe; --blue-600:#2563eb; --blue-700:#1d4ed8;
    --amber-100:#fef3c7; --amber-700:#b45309;
    --gray-50:#f8f9fa; --gray-100:#f1f3f5; --gray-200:#e9ecef; --gray-300:#dee2e6;
    --gray-400:#adb5bd; --gray-500:#6c757d; --gray-700:#343a40; --gray-900:#212529;
    --radius-sm:8px; --radius-md:12px; --radius-lg:16px;
    --shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);
}

.dh-page { background:var(--gray-100); min-height:100vh; padding:2rem 0 3rem; }

/* CARD HEADER - GREEN */
.dh-card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--gray-200); box-shadow:var(--shadow-card); overflow:hidden; margin-bottom:1.25rem; }
.dh-card-header { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:1rem 1.25rem; background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; border-bottom: none; }
.dh-header-left { display:flex; align-items:center; gap:10px; }
.dh-header-icon { width:34px; height:34px; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; background: rgba(255,255,255,0.2); color: white; }
.dh-card-header h5 { margin:0; font-size:15px; font-weight:600; color: white; letter-spacing:-.01em; }
.dh-card-header p { margin:0; font-size:12px; color: rgba(255,255,255,0.85); }
.dh-card-header-right { display:flex; gap:8px; margin-left:auto; }

/* Filter Button */
.filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.2s;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    cursor: pointer;
}
.filter-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
    color: white;
}
.radius-badge {
    background: rgba(255,255,255,0.15);
    padding: 9px 8px;
    border-radius: 20px;
    font-size: 11px;
    margin-left: 5px;
}

/* Dark Mode */
body.dark-mode .dh-card-header { background: linear-gradient(135deg, #2563eb, #1d4ed8); }

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

.dh-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.dh-table thead th { background:var(--gray-50); padding:10px 14px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--gray-500); border-bottom:1.5px solid var(--gray-200); text-align:left; white-space:nowrap; }
.dh-table tbody tr { border-bottom:1px solid var(--gray-100); transition:background .1s; }
.dh-table tbody tr:hover { background:var(--gray-50); }
.dh-table tbody tr:last-child { border-bottom:none; }
.dh-table td { padding:12px 14px; color:var(--gray-700); vertical-align:middle; }

.food-cell { display:flex; align-items:center; gap:10px; }
.food-icon { width:34px; height:34px; border-radius:var(--radius-sm); background:var(--green-50); color:var(--green-600); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.food-name { font-weight:600; color:var(--gray-900); }
.donor-avatar { width:28px; height:28px; border-radius:50%; background:var(--blue-100); color:var(--blue-600); display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; margin-right:7px; vertical-align:middle; }
.loc-text { font-size:12.5px; color:var(--gray-500); max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
.price-badge { display:inline-flex; align-items:center; background:var(--green-50); color:var(--green-700); border:1px solid var(--green-100); padding:4px 10px; border-radius:20px; font-size:12.5px; font-weight:700; }
.map-btn { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:var(--blue-600); background:var(--blue-50); border:1px solid var(--blue-100); padding:5px 11px; border-radius:6px; text-decoration:none; transition:background .12s; }
.map-btn:hover { background:var(--blue-100); color:var(--blue-700); }
.claim-btn { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#fff; background:var(--green-500); border:none; padding:6px 13px; border-radius:6px; text-decoration:none; cursor:pointer; transition:background .12s; }
.claim-btn:hover { background:var(--green-600); color:#fff; }
.dh-empty { padding:2.5rem 1rem; text-align:center; color:var(--gray-400); }
.dh-empty i { font-size:36px; display:block; margin-bottom:.5rem; }
.status-badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600; }
.status-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; opacity:.7; }
.status-delivered { background:#ede9fe; color:#6d28d9; }
.status-transit    { background:var(--blue-100); color:var(--blue-700); }
.status-pending    { background:var(--amber-100); color:var(--amber-700); }
.rider-mini { display:flex; align-items:center; gap:8px; }
.rider-mini-pic { width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid var(--green-100); flex-shrink:0; }
.rider-mini-av  { width:32px; height:32px; border-radius:50%; background:var(--green-100); color:var(--green-700); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; }
.rider-mini-name { font-size:13px; font-weight:600; color:var(--gray-900); }
.rider-mini-bike { font-size:11px; color:var(--gray-400); margin-top:1px; }
.view-rider-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; font-size:12px; font-weight:600; color:var(--green-600); background:var(--green-50); border:1px solid var(--green-100); border-radius:6px; cursor:pointer; transition:background .12s; white-space:nowrap; }
.view-rider-btn:hover { background:var(--green-100); }
.chat-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; font-size:12px; font-weight:600; color:var(--blue-600); background:var(--blue-50); border:1px solid var(--blue-100); border-radius:6px; text-decoration:none; cursor:pointer; transition:background .12s; white-space:nowrap; margin-left:5px; }
.invoice-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; font-size:12px; font-weight:600; color:#17a2b8; background:#e6f7f0; border:1px solid #b8f0e0; border-radius:6px; text-decoration:none; transition:background .12s; margin-left:5px; white-space:nowrap; }
.invoice-btn:hover { background:#d0f0e8; color:#138496; }

/* Rating Modal */
.zh-modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:4000; align-items:center; justify-content:center; }
.zh-modal-backdrop.open { display:flex; }
.zh-modal { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--gray-200); box-shadow:0 24px 64px rgba(0,0,0,.18); width:100%; max-width:460px; overflow:hidden; }
.rider-profile-header { background:linear-gradient(135deg, #f59e0b, #d97706); padding:1.5rem; text-align:center; position:relative; }
.rider-profile-pic-wrap { margin:0 auto 1rem; width:80px; height:80px; border-radius:50%; border:3px solid rgba(255,255,255,.5); overflow:hidden; background:rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; }
.rider-profile-pic-wrap img { width:100%; height:100%; object-fit:cover; }
.rider-profile-pic-wrap .av-fallback { font-size:32px; font-weight:800; color:#fff; }
.rider-profile-name { font-size:1.15rem; font-weight:800; color:#fff; margin-bottom:3px; }
.modal-close-btn { position:absolute; top:12px; right:14px; background:rgba(255,255,255,.2); border:none; border-radius:6px; width:28px; height:28px; display:flex; align-items:center; justify-content:center; color:#fff; cursor:pointer; font-size:16px; }
.modal-close-btn:hover { background:rgba(255,255,255,.35); }
.rating-stars { display:flex; justify-content:center; gap:12px; margin:15px 0; }
.rating-star { font-size:32px; cursor:pointer; color:#dee2e6; transition:all 0.2s; }
.rating-star:hover { transform:scale(1.15); }
.rating-star.active { color:#f59e0b !important; }
.dh-textarea { width:100%; padding:10px 12px; font-size:13px; border:1.5px solid var(--gray-300); border-radius:var(--radius-sm); resize:vertical; min-height:80px; font-family:inherit; }
.dh-textarea:focus { border-color:var(--green-400); outline:none; box-shadow:0 0 0 3px rgba(46,148,88,.18); }
.dh-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:12px; font-size:14px; font-weight:600; color:#fff; background:var(--green-500); border:none; border-radius:var(--radius-sm); cursor:pointer; transition:background .15s; }
.dh-btn:hover { background:var(--green-600); }
.modal-close-footer { flex:1; display:flex; align-items:center; justify-content:center; gap:7px; padding:10px; font-size:13px; font-weight:600; color:var(--gray-700); background:#fff; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); cursor:pointer; }
</style>

<div class="dh-page">
<div class="container" style="max-width:1280px;">

    <!-- Profile Actions -->
    <div class="profile-actions">
        <a href="profile.php" class="profile-btn">
            <i class="fas fa-user-edit"></i> <?= __('my_profile') ?>
        </a>
    </div>

    <!-- Available Donations with Filter Button -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-boxes"></i></div>
                <div><h5><?= __('available_donations') ?></h5><p><?= __('browse_claim') ?></p></div>
            </div>
            <div class="dh-card-header-right">
                <span class="radius-badge"><?= $_SESSION['user_radius'] ?> <?= __('km_radius') ?></span>
                <a href="location_setup.php" class="filter-btn">
                    <i class="fas fa-sliders-h"></i> <?= __('filter') ?>
                </a>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead><tr><th style="width:22%;"><?= __('food_item') ?></th><th style="width:16%;"><?= __('donor') ?></th><th style="width:20%;"><?= __('pickup_location') ?></th><th style="width:10%;"><?= __('price_pkr') ?></th><th style="width:12%;"><?= __('map') ?></th><th style="width:20%;"><?= __('action') ?></th></tr></thead>
            <tbody>
            <?php
            $location_filter = getLocationFilterSQL($_SESSION['user_lat'], $_SESSION['user_lng'], 'fd', 'latitude', 'longitude', $_SESSION['user_radius']);
            $stock = mysqli_query($conn, "SELECT fd.*, u.full_name as donor_name 
                              FROM food_donations fd 
                              JOIN users u ON fd.donor_id = u.user_id 
                              WHERE fd.status = 'Available' 
                              AND $location_filter
                              ORDER BY fd.created_at DESC");
            if ($stock && mysqli_num_rows($stock) > 0):
                while($row = mysqli_fetch_assoc($stock)):
                    $initials = strtoupper(substr($row['donor_name'],0,1));
            ?>
            <tr>
                <td><div class="food-cell"><div class="food-icon"><i class="fas fa-utensils"></i></div><div><div class="food-name"><?= htmlspecialchars($row['food_item']) ?></div><div style="font-size:11px;color:var(--gray-400);"><?= __('expires_at') ?>: <?= date('d M, h:i A', strtotime($row['expiry_time'])) ?></div></div></div></td>
                <td><span class="donor-avatar"><?= $initials ?></span><?= htmlspecialchars($row['donor_name']) ?></td>
                <td><span class="loc-text" title="<?= htmlspecialchars($row['pickup_location']) ?>"><i class="fas fa-map-marker-alt" style="color:var(--gray-400);margin-right:4px;font-size:11px;"></i><?= htmlspecialchars($row['pickup_location']) ?></span></td>
                <td><span class="price-badge"><?= $row['price'] ?> PKR</span></td>
                <td><?php if($row['latitude'] && $row['longitude']): ?><a href="https://www.openstreetmap.org/?mlat=<?= $row['latitude'] ?>&mlon=<?= $row['longitude'] ?>#map=15/<?= $row['latitude'] ?>/<?= $row['longitude'] ?>" target="_blank" class="map-btn"><i class="fas fa-map"></i> <?= __('view_on_map') ?></a><?php else: ?><span style="font-size:12px;color:var(--gray-400);">N/A</span><?php endif; ?></td>
                <td><a href="delivery_setup.php?donation_id=<?= $row['donation_id'] ?>" class="claim-btn"><i class="fas fa-hand-holding-heart"></i> <?= __('claim') ?></a></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-box-open"></i><p><?= __('no_donations_available') ?></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- My Requests -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-clipboard-list"></i></div>
                <div><h5><?= __('my_requests') ?></h5><p><?= __('track_requests_rate') ?></p></div>
            </div>
        </div>
        <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead><tr><th style="width:8%;"><?= __('id') ?></th><th style="width:16%;"><?= __('food_item') ?></th><th style="width:12%;"><?= __('status') ?></th><th style="width:22%;"><?= __('rider_info') ?></th><th style="width:12%;"><?= __('fare') ?></th><th style="width:30%;"><?= __('actions') ?></th></tr></thead>
            <tbody>
            <?php if ($my_requests && mysqli_num_rows($my_requests) > 0):
                while ($req = mysqli_fetch_assoc($my_requests)):
                    $sc = $req['delivery_status']=='Delivered' ? 'status-delivered' : ($req['delivery_status']=='Assigned' ? 'status-transit' : 'status-pending');
                    $rider_initial = $req['rider_name'] ? strtoupper(substr($req['rider_name'],0,1)) : '';
                    $rating_check = mysqli_query($conn, "SELECT rating_id FROM rider_ratings WHERE request_id = {$req['request_id']}");
                    $already_rated = mysqli_num_rows($rating_check) > 0;
            ?>
            <tr>
                <td style="color:var(--gray-400);font-size:13px;">#<?= $req['request_id'] ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($req['food_item']) ?></td>
                <td><span class="status-badge <?= $sc ?>"><?= $req['delivery_status'] ?></span></td>
                <td>
                    <?php if ($req['rider_id'] && $req['rider_name']): ?>
                    <div class="rider-mini">
                        <?php if(!empty($req['profile_pic']) && file_exists('../'.$req['profile_pic'])): ?>
                            <img src="../<?= $req['profile_pic'] ?>" class="rider-mini-pic" alt="">
                        <?php else: ?>
                            <div class="rider-mini-av"><?= $rider_initial ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="rider-mini-name"><?= htmlspecialchars($req['rider_name']) ?></div>
                            <div class="rider-mini-bike"><i class="fas fa-motorcycle" style="font-size:10px;margin-right:3px;"></i><?= htmlspecialchars($req['bike_number'] ?: 'N/A') ?></div>
                            <div class="rider-mini-bike"><i class="fas fa-star" style="color:#f59e0b;font-size:10px;margin-right:3px;"></i><?= displayStars($req['avg_rating'] ?? 0) ?> (<?= $req['total_ratings'] ?? 0 ?>)</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <span style="font-size:12.5px;color:var(--gray-400);"><i class="fas fa-clock" style="margin-right:4px;"></i><?= __('not_assigned_yet') ?></span>
                    <?php endif; ?>
                </td>
                <td><span class="price-badge">Rs. <?= $req['base_fare'] ?? '0' ?></span></td>
                <td>
                    <?php if($req['rider_id'] && $req['rider_name']): ?>
                        <button class="view-rider-btn" onclick='openRiderModal(<?= json_encode(["name"=>$req["rider_name"],"phone"=>$req["rider_phone"]?: "N/A","bike"=>$req["bike_number"]?: "N/A","license"=>$req["license_number"]?: "N/A","pic"=>(!empty($req["profile_pic"]) && file_exists("../".$req["profile_pic"])) ? "../".$req["profile_pic"] : "","rider_id"=>$req["rider_user_id"],"rating"=>$req["avg_rating"]??0]) ?>)'>
                            <i class="fas fa-id-card"></i> <?= __('view_rider') ?>
                        </button>
                        <a href="messages.php?to=<?= $req['rider_user_id'] ?>" class="chat-btn"><i class="fas fa-comment-dots"></i> <?= __('chat') ?></a>
                        
                        <?php if($req['delivery_status'] == 'Delivered'): ?>
                            <a href="invoice.php?id=<?= $req['request_id'] ?>" class="invoice-btn" target="_blank">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                            <?php if($already_rated): ?>
                                <span style="font-size:12px;color:var(--green-600);margin-left:5px;"><i class="fas fa-check-circle"></i> <?= __('rated') ?></span>
                            <?php else: ?>
                                <button class="view-rider-btn" onclick="openRatingModal('<?= addslashes($req['rider_name']) ?>', <?= $req['rider_user_id'] ?>, <?= $req['request_id'] ?>, '<?= !empty($req['profile_pic']) && file_exists('../'.$req['profile_pic']) ? '../'.$req['profile_pic'] : '' ?>')" style="background:#f59e0b; color:#fff; border-color:#f59e0b; margin-left:5px;">
                                    <i class="fas fa-star"></i> <?= __('rate_rider') ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php elseif($req['delivery_status']=='Delivered'): ?>
                        <span style="font-size:12.5px;color:var(--green-600);"><i class="fas fa-check-circle" style="margin-right:4px;"></i><?= __('delivered') ?></span>
                    <?php else: ?>
                        <span style="font-size:12.5px;color:var(--gray-400);"><i class="fas fa-hourglass-half" style="margin-right:4px;"></i><?= __('waiting_for_rider') ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-inbox"></i><p><?= __('no_requests_yet') ?></p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>

<!-- Rating Modal -->
<div class="zh-modal-backdrop" id="ratingModal">
    <div class="zh-modal">
        <div class="rider-profile-header">
            <button class="modal-close-btn" onclick="closeRatingModal()">&times;</button>
            <div class="rider-profile-pic-wrap" id="ratingRiderPic"><span class="av-fallback" id="ratingRiderInitial">R</span></div>
            <div class="rider-profile-name" id="ratingRiderName">Rider Name</div>
            <div class="rider-profile-role"><?= __('rate_your_delivery') ?></div>
        </div>
        <div style="padding:1.25rem; text-align:center;">
            <input type="hidden" id="ratingRequestId" value="">
            <input type="hidden" id="ratingRiderId" value="">
            <div class="rating-stars" id="ratingStars">
                <i class="fas fa-star rating-star" data-rating="1"></i>
                <i class="fas fa-star rating-star" data-rating="2"></i>
                <i class="fas fa-star rating-star" data-rating="3"></i>
                <i class="fas fa-star rating-star" data-rating="4"></i>
                <i class="fas fa-star rating-star" data-rating="5"></i>
            </div>
            <div id="ratingText" style="margin-top:5px; font-size:13px; color:#f59e0b; font-weight:500;"><?= __('click_star_to_rate') ?></div>
            <div style="margin-top:15px;">
                <textarea id="ratingFeedback" class="dh-textarea" rows="3" placeholder="<?= __('share_experience_rider') ?>"></textarea>
            </div>
            <div style="display:flex; gap:12px; margin-top:20px;">
                <button class="dh-btn" onclick="submitRating()" style="background:#f59e0b; flex:1;"><i class="fas fa-paper-plane"></i> <?= __('submit_rating') ?></button>
                <button class="modal-close-footer" onclick="closeRatingModal()" style="flex:1;"><i class="fas fa-times"></i> <?= __('later') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Rider Profile Modal -->
<div class="zh-modal-backdrop" id="riderModal">
    <div class="zh-modal">
        <div class="rider-profile-header" style="background:linear-gradient(135deg, var(--green-500), var(--green-600));">
            <button class="modal-close-btn" onclick="document.getElementById('riderModal').classList.remove('open')">&times;</button>
            <div class="rider-profile-pic-wrap" id="riderPicWrap"><span class="av-fallback" id="riderInitial"></span></div>
            <div class="rider-profile-name" id="modalRiderName"></div>
            <div class="rider-profile-role"><?= __('delivery_rider') ?></div>
        </div>
        <div style="padding:1.25rem;">
            <div class="info-row" style="display:flex; align-items:center; gap:12px; padding:8px 0;"><div class="info-icon green"><i class="fas fa-phone-alt"></i></div><div><div class="info-label"><?= __('phone') ?></div><div class="info-value" id="modalRiderPhone"></div></div></div>
            <div class="info-row" style="display:flex; align-items:center; gap:12px; padding:8px 0;"><div class="info-icon amber"><i class="fas fa-motorcycle"></i></div><div><div class="info-label"><?= __('bike_no') ?></div><div class="info-value" id="modalRiderBike"></div></div></div>
            <div class="info-row" style="display:flex; align-items:center; gap:12px; padding:8px 0;"><div class="info-icon orange"><i class="fas fa-id-badge"></i></div><div><div class="info-label"><?= __('license') ?></div><div class="info-value" id="modalRiderLicense"></div></div></div>
            <div class="info-row" style="display:flex; align-items:center; gap:12px; padding:8px 0;"><div class="info-icon blue"><i class="fas fa-star"></i></div><div><div class="info-label"><?= __('rating') ?></div><div class="info-value" id="modalRiderRating"></div></div></div>
        </div>
        <div class="modal-footer-btns" style="padding:1rem; border-top:1px solid var(--gray-200); display:flex; gap:8px;">
            <a href="#" id="modalChatLink" class="modal-chat-btn" style="flex:1; background:var(--blue-600); color:#fff; padding:10px; text-align:center; border-radius:8px; text-decoration:none;"><i class="fas fa-comment-dots"></i> <?= __('chat') ?></a>
            <button class="modal-close-footer" onclick="document.getElementById('riderModal').classList.remove('open')" style="flex:1;"><i class="fas fa-times"></i> <?= __('close') ?></button>
        </div>
    </div>
</div>

<script>
let selectedRating = 0;
let currentRequestId = null;
let currentRiderId = null;

document.querySelectorAll('.rating-star').forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.rating);
        document.querySelectorAll('.rating-star').forEach((s, idx) => {
            if (idx < selectedRating) s.classList.add('active');
            else s.classList.remove('active');
        });
        const texts = {1:'⭐ <?= __('poor') ?>',2:'⭐⭐ <?= __('below_average') ?>',3:'⭐⭐⭐ <?= __('average') ?>',4:'⭐⭐⭐⭐ <?= __('good') ?>',5:'⭐⭐⭐⭐⭐ <?= __('excellent') ?>'};
        document.getElementById('ratingText').textContent = texts[selectedRating] || '<?= __('click_star_to_rate') ?>';
    });
});

function openRatingModal(riderName, riderId, requestId, riderPic) {
    currentRequestId = requestId;
    currentRiderId = riderId;
    selectedRating = 0;
    document.querySelectorAll('.rating-star').forEach(s => s.classList.remove('active'));
    document.getElementById('ratingText').textContent = '<?= __('click_star_to_rate') ?>';
    document.getElementById('ratingFeedback').value = '';
    document.getElementById('ratingRiderName').textContent = riderName;
    document.getElementById('ratingRiderInitial').textContent = riderName.charAt(0).toUpperCase();
    document.getElementById('ratingRequestId').value = requestId;
    document.getElementById('ratingRiderId').value = riderId;
    const picWrap = document.getElementById('ratingRiderPic');
    const existing = picWrap.querySelector('img');
    if(existing) existing.remove();
    if(riderPic) {
        const img = document.createElement('img');
        img.src = riderPic;
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        img.onerror = function(){ this.remove(); };
        picWrap.prepend(img);
        document.getElementById('ratingRiderInitial').style.display = 'none';
    } else {
        document.getElementById('ratingRiderInitial').style.display = '';
    }
    document.getElementById('ratingModal').classList.add('open');
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.remove('open');
}

function submitRating() {
    if (selectedRating === 0) {
        Swal.fire({ icon: 'warning', title: '<?= __('rating_required') ?>', text: '<?= __('please_select_rating') ?>', confirmButtonColor: '#f59e0b' });
        return;
    }
    const feedback = document.getElementById('ratingFeedback').value;
    fetch('submit_rider_rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `request_id=${currentRequestId}&rider_id=${currentRiderId}&rating=${selectedRating}&feedback=${encodeURIComponent(feedback)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({ icon: 'success', title: '<?= __('rating_submitted') ?>', text: '<?= __('thank_you_feedback') ?>', confirmButtonColor: '#2e9458' });
            closeRatingModal();
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire({ icon: 'error', title: '<?= __('error') ?>', text: data.message || '<?= __('failed_submit_rating') ?>', confirmButtonColor: '#c0392b' });
        }
    })
    .catch(error => {
        Swal.fire({ icon: 'error', title: '<?= __('error') ?>', text: '<?= __('something_went_wrong') ?>', confirmButtonColor: '#c0392b' });
    });
}

function openRiderModal(data) {
    document.getElementById('modalRiderName').textContent = data.name;
    document.getElementById('modalRiderPhone').textContent = data.phone;
    document.getElementById('modalRiderBike').textContent = data.bike;
    document.getElementById('modalRiderLicense').textContent = data.license;
    let ratingHtml = '';
    for(let i=1; i<=5; i++) {
        if(i <= data.rating) ratingHtml += '<i class="fas fa-star" style="color:#f59e0b;"></i>';
        else ratingHtml += '<i class="far fa-star" style="color:#dee2e6;"></i>';
    }
    document.getElementById('modalRiderRating').innerHTML = ratingHtml;
    document.getElementById('riderInitial').textContent = data.name.charAt(0).toUpperCase();
    document.getElementById('modalChatLink').href = 'messages.php?to=' + data.rider_id;
    const wrap = document.getElementById('riderPicWrap');
    const existing = wrap.querySelector('img');
    if(existing) existing.remove();
    if(data.pic) {
        const img = document.createElement('img');
        img.src = data.pic;
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        wrap.prepend(img);
        document.getElementById('riderInitial').style.display = 'none';
    } else {
        document.getElementById('riderInitial').style.display = '';
    }
    document.getElementById('riderModal').classList.add('open');
}
document.getElementById('riderModal')?.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
</script>

<?php include 'footer.php'; ?>