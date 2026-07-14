<?php
// NO OUTPUT BEFORE THIS LINE - NO SPACES, NO ECHO
include '../Backend/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Handle POST requests BEFORE any output or includes
if(isset($_POST['accept_offer'])) {
    $user_id = $_SESSION['user_id'];
    $req_id = intval($_POST['req_id']);
    mysqli_query($conn, "UPDATE requests SET rider_id = $user_id, delivery_status = 'Assigned' WHERE request_id = $req_id");
    header("Location: rider_dashboard.php");
    exit();
}
if(isset($_POST['place_bid'])) {
    $user_id = $_SESSION['user_id'];
    $req_id = intval($_POST['req_id']); 
    $price = intval($_POST['price']);
    mysqli_query($conn, "INSERT INTO delivery_bids (request_id, rider_id, bid_price, status) VALUES ($req_id, $user_id, $price, 'Pending')");
    header("Location: rider_dashboard.php");
    exit();
}
if(isset($_POST['mark_delivered'])) {
    $user_id = $_SESSION['user_id'];
    $req_id = intval($_POST['req_id']);
    $res = mysqli_query($conn, "SELECT donation_id, receiver_id FROM requests WHERE request_id = $req_id");
    $row = mysqli_fetch_assoc($res); 
    $d_id = $row['donation_id'];
    mysqli_query($conn, "UPDATE requests SET delivery_status = 'Delivered' WHERE request_id = $req_id");
    mysqli_query($conn, "UPDATE food_donations SET status = 'Delivered' WHERE donation_id = $d_id");
    header("Location: rider_dashboard.php"); 
    exit();
}

// Now include header after POST handling
include 'header.php'; // First - defines __() function

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 4) {
    header('Location: login.php'); 
    exit();
}
$user_id = $_SESSION['user_id'];
$pageTitle = __('dashboard') . ' - ' . __('site_title');

// Include location functions
include '../Backend/location_functions.php';

// Check if user has location set
$user_location = getUserLocation($conn, $user_id);
if (empty($user_location['lat']) || empty($user_location['lng'])) {
    header('Location: location_setup.php');
    exit();
}

$_SESSION['user_lat'] = $user_location['lat'];
$_SESSION['user_lng'] = $user_location['lng'];
$_SESSION['user_radius'] = $user_location['radius'];

$enroll_check = mysqli_query($conn, "SELECT enrollment_completed, is_active, full_name, phone, bike_number, license_number, profile_pic FROM users WHERE user_id = $user_id");
$rider_info = mysqli_fetch_assoc($enroll_check);

if (!$rider_info['enrollment_completed']) { 
    header('Location: rider_enrollment.php'); 
    exit(); 
}

if ($rider_info['is_active'] == 0) {
    echo "<script>Swal.fire({icon:'error',title:'Account Suspended',text:'Your account has been suspended. Please contact admin.',confirmButtonText:'OK'}).then(()=>{window.location.href='logout.php';});</script>"; 
    exit();
}

$rating_query = mysqli_query($conn, "SELECT avg_rating, total_ratings FROM users WHERE user_id = $user_id");
$rating_data = mysqli_fetch_assoc($rating_query);
$avg_rating = $rating_data['avg_rating'] ?? 0;
$total_ratings = $rating_data['total_ratings'] ?? 0;

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

$my_tasks = mysqli_query($conn, "
    SELECT r.*, fd.food_item,
           r.pickup_lat as p_lat, r.pickup_lng as p_lng,
           r.drop_lat   as d_lat, r.drop_lng   as d_lng,
           u_ngo.full_name as ngo_name, u_ngo.user_id as ngo_user_id
    FROM requests r
    JOIN food_donations fd ON r.donation_id = fd.donation_id
    JOIN users u_ngo ON r.receiver_id = u_ngo.user_id
    WHERE r.rider_id = $user_id AND r.delivery_status = 'Assigned'
");

// Get pending delivery requests with location filter
$location_filter = getLocationFilterSQL($_SESSION['user_lat'], $_SESSION['user_lng'], 'r', 'pickup_lat', 'pickup_lng', $_SESSION['user_radius']);
$tasks = mysqli_query($conn, "SELECT r.*, fd.food_item, fd.pickup_location
                              FROM requests r 
                              JOIN food_donations fd ON r.donation_id = fd.donation_id 
                              WHERE r.delivery_status = 'Pending' 
                              AND (r.rider_id IS NULL OR r.rider_id = 0)
                              AND $location_filter
                              ORDER BY r.request_id DESC");

$unread = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM messages WHERE receiver_id=$user_id AND is_read=0"))['c'] ?? 0;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<style>
:root{
    --green-50:#f0faf4; --green-100:#d6f0e0; --green-400:#3aad6a;
    --green-500:#2e9458; --green-600:#226e42; --green-700:#174d2e;
    --blue-50:#eff6ff; --blue-100:#dbeafe; --blue-500:#3b82f6; --blue-600:#2563eb;
    --amber-50:#fffbeb; --amber-100:#fef3c7; --amber-600:#d97706; --amber-700:#b45309;
    --orange-50:#fff7ed; --orange-100:#ffedd5; --orange-600:#ea580c;
    --gray-50:#f8f9fa; --gray-100:#f1f3f5; --gray-200:#e9ecef; --gray-300:#dee2e6;
    --gray-400:#adb5bd; --gray-500:#6c757d; --gray-700:#343a40; --gray-900:#212529;
    --radius-sm:8px; --radius-md:12px; --radius-lg:16px;
    --shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);
}

.dh-page { background:var(--gray-100); min-height:100vh; padding:2rem 0 3rem; }

/* CARD HEADER - GREEN */
.dh-card { background:#fff; border-radius:var(--radius-lg); border:1px solid var(--gray-200); box-shadow:var(--shadow-card); overflow:hidden; margin-bottom:1.25rem; }
.dh-card-header { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:1rem 1.25rem; background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; border-bottom: none; flex-wrap:wrap; }
.dh-header-left { display:flex; align-items:center; gap:10px; }
.dh-header-icon { width:34px; height:34px; border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; background: rgba(255,255,255,0.2); color: white; }
.dh-card-header h5 { margin:0; font-size:15px; font-weight:600; color: white; }
.dh-card-header p  { margin:0; font-size:12px; color: rgba(255,255,255,0.85); }
.dh-card-header-right { display:flex; gap:8px; margin-left:auto; }
.dh-card-body { padding:1.1rem 1.25rem; }

/* Filter Button only (no location button in header now) */
.map-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}
.map-filter-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
    color: white;
}

/* Map Container with relative position for button */
.map-container {
    position: relative;
}
#map { height:380px; width:100%; border-radius:var(--radius-sm); }

/* Current Location Button - Floating Circle Button on Map */
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

.profile-card { background:linear-gradient(135deg, var(--green-500), var(--green-600)); color:#fff; border-radius:var(--radius-lg); padding:1.1rem 1.4rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap; box-shadow:var(--shadow-card); }
.profile-pic { width:64px; height:64px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.5); background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden; }
.profile-pic img { width:100%; height:100%; object-fit:cover; }
.profile-info h4 { margin:0; font-size:1.05rem; font-weight:700; }
.profile-info p  { margin:.25rem 0 0; font-size:.8rem; opacity:.85; }
.profile-badge   { margin-left:auto; background:rgba(255,255,255,.2); padding:.3rem .9rem; border-radius:20px; font-size:.75rem; }
.inbox-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; font-size:13px; font-weight:600; color:var(--blue-600); background:var(--blue-50); border:1.5px solid var(--blue-100); border-radius:var(--radius-sm); text-decoration:none; position:relative; transition:background .12s; }
.inbox-btn:hover { background:var(--blue-100); color:var(--blue-700); }
.inbox-btn .badge-count { background:#e85a30; color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:10px; }
.task-row { display:flex; align-items:center; flex-wrap:wrap; gap:10px; padding:1rem 1.25rem; border-bottom:1px solid var(--gray-100); }
.task-row:last-child { border-bottom:none; }
.task-food-icon { width:45px; height:45px; border-radius:var(--radius-sm); background:var(--green-50); color:var(--green-600); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.task-info { flex:1; min-width:0; }
.task-food-name { font-size:15px; font-weight:700; color:var(--gray-900); margin-bottom:5px; }
.task-route { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--gray-500); flex-wrap:wrap; }
.loc-pill { display:inline-flex; align-items:center; gap:6px; background:var(--gray-100); border:1px solid var(--gray-200); border-radius:5px; padding:6px 12px; font-size:11.5px; color:var(--gray-700); max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.fare-badge { display:inline-flex; align-items:center; background:var(--amber-50); color:var(--amber-700); border:1px solid var(--amber-100); padding:5px 12px; border-radius:20px; font-size:13px; font-weight:700; }
.task-actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.deliver-btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; font-size:12.5px; font-weight:600; color:#fff; background:var(--green-500); border:none; border-radius:var(--radius-sm); cursor:pointer; transition:background .12s; }
.deliver-btn:hover { background:var(--green-600); }
.chat-ngo-btn { display:inline-flex; align-items:center; gap:5px; padding:7px 13px; font-size:12.5px; font-weight:600; color:var(--blue-600); background:var(--blue-50); border:1.5px solid var(--blue-100); border-radius:var(--radius-sm); text-decoration:none; transition:background .12s; white-space:nowrap; }
.chat-ngo-btn:hover { background:var(--blue-100); color:var(--blue-700); }
.dh-table { width:100%; border-collapse:collapse; font-size:13.5px; }
.dh-table thead th { background:var(--gray-50); padding:12px 14px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--gray-500); border-bottom:1.5px solid var(--gray-200); text-align:left; }
.dh-table tbody tr { border-bottom:1px solid var(--gray-100); transition:background .1s; }
.dh-table tbody tr:hover { background:var(--gray-50); }
.dh-table td { padding:12px 14px; color:var(--gray-700); vertical-align:middle; }
.food-name-cell { font-weight:700; color:var(--gray-900); }
.dh-input { padding:7px 10px; font-size:13px; color:var(--gray-900); background:#fff; border:1.5px solid var(--gray-300); border-radius:var(--radius-sm); outline:none; width:90px; }
.dh-input:focus { border-color:var(--green-400); box-shadow:0 0 0 3px rgba(46,148,88,.18); }
.accept-btn, .bid-btn { display:inline-flex; align-items:center; gap:5px; padding:7px 14px; font-size:12.5px; font-weight:600; border:none; border-radius:var(--radius-sm); cursor:pointer; transition:background .12s; }
.accept-btn { color:#fff; background:var(--green-500); } .accept-btn:hover { background:var(--green-600); }
.bid-btn    { color:#fff; background:var(--blue-600);  } .bid-btn:hover    { background:var(--blue-500);  }
.dh-empty { padding:3rem 1rem; text-align:center; color:var(--gray-400); }
.dh-empty i { font-size:40px; display:block; margin-bottom:.5rem; }
</style>

<div class="dh-page">
<div class="container" style="max-width:1200px;">

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

    <!-- Profile Banner with Rating -->
    <div class="profile-card">
        <?php 
        $profile_pic_path = $rider_info['profile_pic'] ?? '';
        $full_pic_path = '../' . $profile_pic_path;
        if (!empty($profile_pic_path) && file_exists($full_pic_path)): 
        ?>
            <div class="profile-pic"><img src="../<?= htmlspecialchars($profile_pic_path) ?>" alt="Profile"></div>
        <?php else: ?>
            <div class="profile-pic"><i class="fas fa-user fa-2x"></i></div>
        <?php endif; ?>
        <div class="profile-info">
            <h4><i class="fas fa-user-check me-1"></i> <?= htmlspecialchars($rider_info['full_name']) ?></h4>
            <p><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($rider_info['phone'] ?: __('not_provided')) ?> &nbsp;|&nbsp;
               <i class="fas fa-motorcycle me-1"></i> <?= htmlspecialchars($rider_info['bike_number'] ?: __('not_provided')) ?></p>
        </div>
        <div class="profile-badge">
            <i class="fas fa-star" style="color: #f59e0b;"></i> <?= number_format($avg_rating, 1) ?> (<?= $total_ratings ?> <?= __('reviews') ?>)
        </div>
    </div>

    <!-- Map with Filter Button -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-route"></i></div>
                <div><h5><?= __('live_delivery_routes') ?></h5><p><?= __('assigned_delivery_paths') ?></p></div>
            </div>
            <div class="dh-card-header-right">
                <a href="location_setup.php" class="map-filter-btn">
                    <i class="fas fa-sliders-h"></i> <?= __('filter') ?>
                </a>
            </div>
        </div>
        <div class="dh-card-body">
            <div class="map-container">
                <div id="map"></div>
                <button type="button" class="current-loc-btn" id="currentLocationBtn" title="<?= __('my_location') ?>">
                    <i class="fas fa-location-dot"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Assigned Tasks -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-clipboard-check"></i></div>
                <div><h5><?= __('assigned_tasks') ?></h5><p><?= __('active_deliveries') ?></p></div>
            </div>
        </div>

        <?php if (mysqli_num_rows($my_tasks) > 0):
            mysqli_data_seek($my_tasks, 0);
            while($t = mysqli_fetch_assoc($my_tasks)):
        ?>
        <div class="task-row">
            <div class="task-food-icon"><i class="fas fa-utensils"></i></div>
            <div class="task-info">
                <div class="task-food-name"><?= htmlspecialchars($t['food_item']) ?></div>
                <div class="task-route">
                    <span class="loc-pill" id="pickup_addr_<?= $t['request_id'] ?>">
                        <i class="fas fa-map-marker-alt" style="color:var(--green-500);flex-shrink:0;"></i>
                        <span><?= __('loading_pickup') ?>...</span>
                    </span>
                    <i class="fas fa-arrow-right" style="color:var(--gray-400);font-size:10px;"></i>
                    <span class="loc-pill" id="drop_addr_<?= $t['request_id'] ?>">
                        <i class="fas fa-flag-checkered" style="color:#e85a30;flex-shrink:0;"></i>
                        <span><?= __('loading_drop') ?>...</span>
                    </span>
                    <span class="fare-badge">Rs. <?= $t['base_fare'] ?></span>
                </div>
            </div>
            <div class="task-actions">
                <form method="POST" style="display:contents;">
                    <input type="hidden" name="req_id" value="<?= $t['request_id'] ?>">
                    <button type="submit" name="mark_delivered" class="deliver-btn">
                        <i class="fas fa-check-circle"></i> <?= __('mark_delivered') ?>
                    </button>
                </form>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="dh-empty"><i class="fas fa-inbox"></i><p><?= __('no_active_tasks') ?></p></div>
        <?php endif; ?>
    </div>

    <!-- Marketplace -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-store"></i></div>
                <div><h5><?= __('rider_marketplace') ?></h5><p><?= __('open_delivery_requests') ?></p></div>
            </div>
        </div>

        <?php if (mysqli_num_rows($tasks) > 0): ?>
        <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead>
                <tr>
                    <th style="width:20%;"><?= __('food_item') ?></th>
                    <th style="width:35%;"><?= __('pickup_location') ?></th>
                    <th style="width:15%;"><?= __('base_fare') ?></th>
                    <th style="width:30%;"><?= __('action') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($tasks)): ?>
            <tr>
                <td class="food-name-cell"><?= htmlspecialchars($row['food_item']) ?></td>
                <td><span class="loc-pill" style="max-width:300px;"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['pickup_location'] ?? __('location_not_set')) ?></span></td>
                <td><span class="fare-badge">Rs. <?= $row['base_fare'] ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="req_id" value="<?= $row['request_id'] ?>">
                            <button type="submit" name="accept_offer" class="accept-btn"><i class="fas fa-check"></i> <?= __('accept') ?></button>
                        </form>
                        <form method="POST" style="display:contents;">
                            <input type="hidden" name="req_id" value="<?= $row['request_id'] ?>">
                            <input type="number" name="price" class="dh-input" placeholder="<?= __('bid') ?>" min="1" required>
                            <button type="submit" name="place_bid" class="bid-btn"><i class="fas fa-gavel"></i> <?= __('bid') ?></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="dh-empty"><i class="fas fa-store-slash"></i><p><?= __('no_open_requests') ?> (<?= $_SESSION['user_radius'] ?> <?= __('km_radius') ?>).</p></div>
        <?php endif; ?>
    </div>

</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
// Global map variable
var map;

// Function to center map on user's current location
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
                map.setView([lat, lng], 14);
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

async function getAddr(lat, lng, elId, isDrop=false) {
    if(!lat||!lng||lat==='NULL'||lng==='NULL') return;
    try {
        const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
        const d = await r.json();
        let addr = d.display_name || `${lat},${lng}`;
        if(addr.length>60) addr = addr.substring(0,57)+'...';
        const icon = isDrop ? '<i class="fas fa-flag-checkered" style="color:#e85a30;flex-shrink:0;"></i>' : '<i class="fas fa-map-marker-alt" style="color:var(--green-500);flex-shrink:0;"></i>';
        const span = document.getElementById(elId);
        if(span) span.innerHTML = icon + '<span>' + addr + '</span>';
    } catch(e) {}
}

<?php mysqli_data_seek($my_tasks,0); while($t=mysqli_fetch_assoc($my_tasks)): if($t['p_lat']&&$t['p_lng']): ?>
getAddr(<?= $t['p_lat'] ?>, <?= $t['p_lng'] ?>, 'pickup_addr_<?= $t['request_id'] ?>');
<?php endif; if($t['d_lat']&&$t['d_lng']): ?>
getAddr(<?= $t['d_lat'] ?>, <?= $t['d_lng'] ?>, 'drop_addr_<?= $t['request_id'] ?>', true);
<?php endif; endwhile; ?>

// Initialize map
map = L.map('map').setView([<?= $_SESSION['user_lat'] ?>, <?= $_SESSION['user_lng'] ?>], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(map);

<?php mysqli_data_seek($my_tasks,0); while($t=mysqli_fetch_assoc($my_tasks)): if($t['p_lat']&&$t['p_lng']&&$t['d_lat']&&$t['d_lng']): ?>
L.Routing.control({ waypoints:[L.latLng(<?= $t['p_lat'] ?>,<?= $t['p_lng'] ?>),L.latLng(<?= $t['d_lat'] ?>,<?= $t['d_lng'] ?>)], routeWhileDragging:false, lineOptions:{styles:[{color:'#2e9458',weight:4,opacity:.8}]}, show:false }).addTo(map);
<?php endif; endwhile; ?>

// Add current location button event listener
document.getElementById('currentLocationBtn').addEventListener('click', centerOnCurrentLocation);
</script>

<?php include 'footer.php'; ?>