<?php
include '../Backend/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load language file manually to get translations for pageTitle
$lang = $_COOKIE['language'] ?? 'en';
$translations = [];
$lang_file = __DIR__ . '/languages/' . $lang . '.php';
if (file_exists($lang_file)) {
    $translations = require $lang_file;
}

// Simple translation function for pageTitle only (will be replaced by header.php's function)
function __temp($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

// Now set pageTitle using temp function
if (!isset($_GET['donation_id'])) { header('Location: ngo_dashboard.php'); exit(); }
$donation_id = intval($_GET['donation_id']);
$pageTitle = __temp('delivery_setup') . ' - ' . __temp('site_title');

// Now include header (it will redeclare __() but that's fine - it will override)
include 'header.php';

// Note: After header.php is included, the proper __() function is available

$donor_data = mysqli_query($conn, "SELECT fd.*, u.full_name as donor_name, u.phone FROM food_donations fd JOIN users u ON fd.donor_id = u.user_id WHERE fd.donation_id = $donation_id");
$d_loc = mysqli_fetch_assoc($donor_data);
$saved_lat = $d_loc['latitude'] ?? 24.8607;
$saved_lng = $d_loc['longitude'] ?? 67.0011;

if (isset($_POST['submit_delivery'])) {
    $p_lat = $_POST['p_lat']; $p_lng = $_POST['p_lng'];
    $d_lat = $_POST['d_lat']; $d_lng = $_POST['d_lng'];
    $mode  = $_POST['delivery_mode'];
    $fare  = floatval($_POST['base_fare']);
    $status = ($mode == 'Self') ? 'Delivered' : 'Pending';

    mysqli_query($conn, "INSERT INTO requests (donation_id, receiver_id, pickup_lat, pickup_lng, drop_lat, drop_lng, base_fare, status, delivery_status) VALUES ($donation_id, ".$_SESSION['user_id'].", '$p_lat', '$p_lng', '$d_lat', '$d_lng', $fare, 'Pending', '$status')");
    mysqli_query($conn, "UPDATE food_donations SET status = 'Claimed' WHERE donation_id = $donation_id");
    echo "<script>alert('" . __('delivery_setup_success') . "'); window.location.href='ngo_dashboard.php';</script>";
}

function getAddressFromCoords($lat, $lng) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZeroHungerApp/1.0');
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return $data['display_name'] ?? "$lat, $lng";
}

$pickup_address = getAddressFromCoords($saved_lat, $saved_lng);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<style>
    :root {
        --green-50:  #f0faf4;
        --green-100: #d6f0e0;
        --green-400: #3aad6a;
        --green-500: #2e9458;
        --green-600: #226e42;
        --green-700: #174d2e;
        --blue-50:   #eff6ff;
        --blue-100:  #dbeafe;
        --blue-500:  #3b82f6;
        --blue-600:  #2563eb;
        --orange-50: #fff7ed;
        --orange-100:#ffedd5;
        --orange-600:#ea580c;
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

    .dh-page { background: var(--gray-100); min-height: 100vh; padding: 2rem 0 3rem; }
    
    /* CARD HEADER - GREEN LIKE LOGIN PAGE */
    .dh-card { background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 1.25rem; }
    .dh-card-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 1rem 1.25rem; background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; }
    .dh-header-left { display: flex; align-items: center; gap: 10px; }
    .dh-header-icon { width: 34px; height: 34px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; background: rgba(255,255,255,0.2); color: white; }
    .dh-card-header h5 { margin:0; font-size:15px; font-weight:600; color: white; }
    .dh-card-header p { margin:0; font-size:12px; color: rgba(255,255,255,0.85); }
    .dh-card-header-right { display: flex; gap: 8px; margin-left: auto; }
    
    /* Dark Mode - Card Headers Blue */
    body.dark-mode .dh-card-header { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    
    .dh-card-body { padding: 1.25rem; }

    .info-pills { display: flex; flex-wrap: wrap; gap: 10px; padding: 1rem 1.25rem; background: var(--gray-50); }
    .info-pill { display: flex; align-items: center; gap: 7px; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-sm); padding: 7px 12px; font-size: 13px; }
    .info-pill i { color: var(--gray-400); font-size: 13px; }
    .info-pill strong { color: var(--gray-900); font-weight: 600; }

    .search-container { position: relative; margin-bottom: 12px; display: flex; gap: 8px; }
    .search-input { flex: 1; padding: 10px 12px; font-size: 13px; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); outline: none; }
    .search-input:focus { border-color: var(--green-400); box-shadow: 0 0 0 3px rgba(46,148,88,.18); }
    .search-btn { padding: 10px 18px; background: var(--green-500); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; transition: background 0.2s; }
    .search-btn:hover { background: var(--green-600); }
    
    .ui-autocomplete {
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-sm);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
    }
    .ui-menu-item { padding: 8px 12px; font-size: 13px; cursor: pointer; border-bottom: 1px solid var(--gray-100); }
    .ui-menu-item:hover { background: var(--green-50); }
    .ui-state-active { background: var(--green-500) !important; color: white !important; }

    .mode-toggle { display: flex; gap: 8px; margin-bottom: 1rem; }
    .mode-btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--gray-300); background: #fff; color: var(--gray-500); transition: all .15s; }
    .mode-btn .step-num { width: 20px; height: 20px; border-radius: 50%; background: var(--gray-200); color: var(--gray-600); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
    .mode-btn.active-pickup { border-color: var(--green-400); color: var(--green-600); background: var(--green-50); }
    .mode-btn.active-pickup .step-num { background: var(--green-500); color: #fff; }
    .mode-btn.active-drop { border-color: #e85a30; color: #c0390d; background: #fff5f0; }
    .mode-btn.active-drop .step-num { background: #e85a30; color: #fff; }

    .map-hint { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--gray-400); margin-bottom: 8px; }
    
    /* Map Container with relative position for button */
    .map-container { position: relative; }
    #map { height: 420px; width: 100%; border-radius: var(--radius-sm); border: 1.5px solid var(--gray-300); overflow: hidden; cursor: crosshair; }
    
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
    .current-loc-btn:active { transform: scale(0.95); }
    body.dark-mode .current-loc-btn {
        background: #2a2a3a;
        border-color: #3a3a4a;
        color: #3b82f6;
    }
    body.dark-mode .current-loc-btn:hover { background: #1e1e2e; }
    
    .map-legend { display: flex; gap: 16px; margin-top: 10px; flex-wrap: wrap; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--gray-500); }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
    .legend-dot.pickup { background: var(--green-500); }
    .legend-dot.drop   { background: #e85a30; }

    .address-display { background: var(--green-50); border: 1px solid var(--green-100); border-radius: var(--radius-sm); padding: 8px 12px; margin-top: 10px; font-size: 12px; color: var(--green-700); }
    .address-display i { margin-right: 6px; }

    .dh-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--gray-700); margin-bottom: 5px; }
    .dh-label .req { color: var(--green-500); margin-left: 2px; }
    .dh-input, .dh-select { width: 100%; padding: 9px 12px; font-size: 13.5px; color: var(--gray-900); background: #fff; border: 1.5px solid var(--gray-300); border-radius: var(--radius-sm); outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit; }
    .dh-input:focus, .dh-select:focus { border-color: var(--green-400); box-shadow: 0 0 0 3px rgba(46,148,88,.18); }
    .dh-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236c757d' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }
    .dh-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 11px; font-size: 14px; font-weight: 600; color: #fff; background: var(--green-500); border: none; border-radius: var(--radius-sm); cursor: pointer; transition: background .15s; }
    .dh-btn:hover { background: var(--green-600); }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--gray-500); font-size: 13px; text-decoration: none; margin-bottom: 1.25rem; transition: color .12s; }
    .back-link:hover { color: var(--gray-700); }
    
    /* Location lock indicator */
    .location-lock { display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; }
    .location-lock i { font-size: 11px; }
</style>

<div class="dh-page">
<div class="container" style="max-width: 980px;">

    <a href="ngo_dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> <?= __('back_to_dashboard') ?>
    </a>

    <!-- Donation Info Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-info-circle"></i></div>
                <div>
                    <h5><?= __('donation') ?> #<?= $donation_id ?> — <?= htmlspecialchars($d_loc['food_item'] ?? __('food_item')) ?></h5>
                    <p><?= __('review_donation_details') ?></p>
                </div>
            </div>
            <div class="dh-card-header-right">
                <div class="location-lock"><i class="fas fa-lock"></i> <?= __('pickup_location_locked') ?></div>
            </div>
        </div>
        <div class="info-pills">
            <div class="info-pill"><i class="fas fa-user"></i><span><?= __('donor') ?>:</span><strong><?= htmlspecialchars($d_loc['donor_name'] ?? '—') ?></strong></div>
            <div class="info-pill"><i class="fas fa-weight-hanging"></i><span><?= __('quantity') ?>:</span><strong><?= htmlspecialchars($d_loc['quantity'] ?? '—') ?></strong></div>
            <div class="info-pill"><i class="fas fa-tag"></i><span><?= __('price') ?>:</span><strong><?= $d_loc['price'] ?? '0' ?> PKR</strong></div>
            <div class="info-pill"><i class="fas fa-map-marker-alt"></i><span><?= __('pickup') ?>:</span><strong style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($pickup_address) ?></strong></div>
        </div>
    </div>

    <!-- Map Card -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div><h5><?= __('set_dropoff_location') ?></h5><p><?= __('pickup_auto_set') ?></p></div>
            </div>
        </div>
        <div class="dh-card-body">
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="<?= __('search_dropoff_location') ?>">
                <button type="button" class="search-btn" onclick="searchSelectedLocation()"><i class="fas fa-search"></i> <?= __('search') ?></button>
            </div>

            <!-- Mode Toggle - Drop only enabled by default -->
            <div class="mode-toggle">
                <button type="button" id="btn-pickup" class="mode-btn active-pickup" onclick="setMode('pickup')" disabled style="opacity:0.6; cursor:not-allowed;">
                    <span class="step-num">1</span> <?= __('pickup_point_locked') ?>
                </button>
                <button type="button" id="btn-drop" class="mode-btn active-drop" onclick="setMode('drop')">
                    <span class="step-num">2</span> <?= __('dropoff_point') ?>
                </button>
            </div>

            <div class="map-hint"><i class="fas fa-hand-pointer"></i><span id="mapHintText"><?= __('click_to_set_dropoff') ?></span></div>
            
            <div class="map-container">
                <div id="map"></div>
                <button type="button" class="current-loc-btn" id="currentLocationBtn" title="<?= __('my_location') ?>">
                    <i class="fas fa-location-dot"></i>
                </button>
            </div>

            <div id="pickupAddressDisplay" class="address-display">
                <i class="fas fa-map-marker-alt"></i> <?= __('pickup_address') ?>: <span id="pickupAddrText"><?= htmlspecialchars($pickup_address) ?></span>
            </div>
            <div id="dropAddressDisplay" class="address-display">
                <i class="fas fa-flag-checkered"></i> <?= __('dropoff_address') ?>: <span id="dropAddrText"><?= __('click_to_set_dropoff') ?></span>
            </div>

            <div class="map-legend">
                <div class="legend-item"><div class="legend-dot pickup"></div> <?= __('pickup_locked') ?></div>
                <div class="legend-item"><div class="legend-dot drop"></div> <?= __('dropoff_click_to_set') ?></div>
            </div>
        </div>
    </div>

    <!-- Delivery Options Form -->
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-left">
                <div class="dh-header-icon"><i class="fas fa-truck"></i></div>
                <div><h5><?= __('delivery_options') ?></h5><p><?= __('choose_mode_set_fare') ?></p></div>
            </div>
        </div>
        <div class="dh-card-body">
            <form method="POST">
                <input type="hidden" name="p_lat" id="p_lat" value="<?= $saved_lat ?>">
                <input type="hidden" name="p_lng" id="p_lng" value="<?= $saved_lng ?>">
                <input type="hidden" name="d_lat" id="d_lat">
                <input type="hidden" name="d_lng" id="d_lng">

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="dh-label"><?= __('delivery_mode') ?> <span class="req">*</span></label>
                        <select name="delivery_mode" class="dh-select" required>
                            <option value="Self"><?= __('self_pickup') ?></option>
                            <option value="Assign"><?= __('assign_to_rider') ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="dh-label"><?= __('base_fare') ?> (PKR) <span class="req">*</span></label>
                        <input type="number" name="base_fare" class="dh-input" placeholder="<?= __('eg_200') ?>" min="0" required>
                    </div>
                </div>
                <button type="submit" name="submit_delivery" class="dh-btn"><i class="fas fa-check-circle"></i> <?= __('finalize_request_delivery') ?></button>
            </form>
        </div>
    </div>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
$(function() {
    $("#searchInput").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "https://nominatim.openstreetmap.org/search",
                dataType: "json",
                data: { q: request.term, format: "json", limit: 8, countrycodes: "pk", addressdetails: 1 },
                success: function(data) {
                    response($.map(data, function(item) {
                        return { label: item.display_name, value: item.display_name, lat: item.lat, lon: item.lon };
                    }));
                },
                error: function() { response([]); }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            if (ui.item) { selectedLat = ui.item.lat; selectedLon = ui.item.lon; selectedLocation = ui.item.value; return false; }
        }
    }).on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); searchSelectedLocation(); } });
});

var selectedLat = null, selectedLon = null, selectedLocation = null;

function searchSelectedLocation() {
    if (selectedLat && selectedLon) {
        var lat = parseFloat(selectedLat), lng = parseFloat(selectedLon);
        map.setView([lat, lng], 16);
        if (mode === 'drop') {
            if (dMarker) map.removeLayer(dMarker);
            dMarker = L.marker([lat, lng], {icon: redIcon, draggable: true}).addTo(map).bindPopup('<b><?= __('dropoff_point') ?></b>').openPopup();
            document.getElementById('d_lat').value = lat; document.getElementById('d_lng').value = lng;
            getAddressFromCoords(lat, lng, false);
            dMarker.on('dragend', function(ev) { var pos = ev.target.getLatLng(); document.getElementById('d_lat').value = pos.lat; document.getElementById('d_lng').value = pos.lng; getAddressFromCoords(pos.lat, pos.lng, false); });
        }
    } else { alert('<?= __('select_location_first') ?>'); }
}

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

var map = L.map('map').setView([<?= $saved_lat ?>, <?= $saved_lng ?>], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '© OpenStreetMap'}).addTo(map);

var greenIcon = new L.Icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png', iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41] });
var redIcon = new L.Icon({ iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png', shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png', iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41] });

// Pickup marker (locked, not draggable)
var pMarker = L.marker([<?= $saved_lat ?>, <?= $saved_lng ?>], {icon: greenIcon, draggable: false}).addTo(map).bindPopup('<b><?= __('pickup_point') ?></b><br><?= __('donor_location_locked') ?>').openPopup();
var dMarker = null;
var mode = 'drop'; // Default to drop mode

function setMode(m) { 
    if (m === 'pickup') return;
    mode = m; 
    document.getElementById('btn-pickup').className = 'mode-btn active-pickup';
    document.getElementById('btn-drop').className = 'mode-btn active-drop';
    document.getElementById('mapHintText').textContent = '<?= __('click_to_set_dropoff') ?>';
}

async function getAddressFromCoords(lat, lng, isPickup) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        const address = data.display_name || `${lat}, ${lng}`;
        if (isPickup) { 
            document.getElementById('pickupAddrText').innerHTML = address; 
        } else { 
            document.getElementById('dropAddrText').innerHTML = address; 
            document.getElementById('dropAddressDisplay').style.display = 'block';
        }
    } catch(e) { console.log('Error:', e); }
}

// Initialize pickup address display
getAddressFromCoords(<?= $saved_lat ?>, <?= $saved_lng ?>, true);

map.on('click', function(e) {
    if (mode === 'drop') {
        if (dMarker) map.removeLayer(dMarker);
        dMarker = L.marker(e.latlng, {icon: redIcon, draggable: true}).addTo(map).bindPopup('<b><?= __('dropoff_point') ?></b>').openPopup();
        document.getElementById('d_lat').value = e.latlng.lat;
        document.getElementById('d_lng').value = e.latlng.lng;
        getAddressFromCoords(e.latlng.lat, e.latlng.lng, false);
        dMarker.on('dragend', function(ev) { 
            var pos = ev.target.getLatLng(); 
            document.getElementById('d_lat').value = pos.lat; 
            document.getElementById('d_lng').value = pos.lng; 
            getAddressFromCoords(pos.lat, pos.lng, false); 
        });
    }
});

// Add current location button event listener
document.getElementById('currentLocationBtn').addEventListener('click', centerOnCurrentLocation);
</script>

<?php include 'footer.php'; ?>