<?php
include '../Backend/db.php';
include '../Backend/location_functions.php';
include 'header.php'; // First - defines __() function

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pageTitle = __('set_your_location') . ' - ' . __('site_title');
$error = "";
$success = "";
$address = "";

// Get current user location
$user_location = getUserLocation($conn, $user_id);
$current_lat = $user_location['lat'];
$current_lng = $user_location['lng'];
$current_radius = $user_location['radius'] ?? 40;

if ($current_lat && $current_lng) {
    $address = getAddressFromCoordinates($current_lat, $current_lng);
}

// Handle Save/Update - Save radius without changing location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_radius'])) {
    $radius = intval($_POST['radius'] ?? 40);
    
    if ($current_lat && $current_lng) {
        if (updateUserLocation($conn, $user_id, $current_lat, $current_lng, $radius)) {
            $success = __('radius_updated') . " <strong>$radius KM</strong> " . __('radius_info_text');
            $current_radius = $radius;
            
            // Update session
            $_SESSION['user_radius'] = $radius;
        } else {
            $error = __('failed_update_radius');
        }
    } else {
        $error = __('set_location_first');
    }
}

// Handle auto location (first time setup)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto_location'])) {
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;
    $radius = intval($_POST['radius'] ?? 40);
    
    if ($lat && $lng) {
        if (updateUserLocation($conn, $user_id, $lat, $lng, $radius)) {
            $success = __('location_set_success') . " $radius KM " . __('radius_info_text');
            $current_lat = $lat;
            $current_lng = $lng;
            $current_radius = $radius;
            $address = getAddressFromCoordinates($lat, $lng);
            
            // Redirect to dashboard after successful location setup
            $role = $_SESSION['role_id'] ?? 0;
            if ($role == 2) header("Location: donor_dashboard.php");
            elseif ($role == 3) header("Location: ngo_dashboard.php");
            elseif ($role == 4) header("Location: rider_dashboard.php");
            else header("Location: index.php");
            exit();
        } else {
            $error = __('failed_update_location');
        }
    } else {
        $error = __('could_not_detect_location');
    }
}
?>

<style>
.location-container { max-width: 700px; margin: 2rem auto; }
.location-card { background: white; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
.location-header { background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; padding: 2rem; text-align: center; }
.location-header i { font-size: 48px; margin-bottom: 1rem; }
.location-body { padding: 2rem; }
.current-location { background: #f0faf4; border-radius: 16px; padding: 1rem; margin-bottom: 1.5rem; border: 1px solid #d6f0e0; }
.current-location i { color: #2e7d32; margin-right: 8px; }
.radius-select { width: 100%; padding: 12px; border: 1.5px solid #e0e0e0; border-radius: 12px; font-size: 14px; background: white; cursor: pointer; }
.radius-select:focus { outline: none; border-color: #2e7d32; }
.btn-location { background: linear-gradient(135deg, #2e7d32, #1b5e20); border: none; border-radius: 50px; padding: 14px 28px; font-weight: 600; color: white; width: 100%; cursor: pointer; transition: all 0.3s; margin-bottom: 12px; }
.btn-location:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(46,125,50,0.3); }
.btn-save { background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; border-radius: 50px; padding: 14px 28px; font-weight: 600; color: white; width: 100%; cursor: pointer; transition: all 0.3s; }
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(37,99,235,0.3); }
.btn-secondary { background: #6c757d; border: none; border-radius: 50px; padding: 12px 24px; font-weight: 600; color: white; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; text-align: center; }
.btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
.location-note { font-size: 12px; color: #6c757d; text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e9ecef; }
.loading-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid #fff; border-radius: 50%; border-top-color: transparent; animation: spin 0.6s linear infinite; margin-left: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }
.alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 1.5rem; font-size: 14px; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
.alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
.form-label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px; }
.mb-3 { margin-bottom: 1rem; }
.button-group { display: flex; gap: 12px; margin-top: 10px; flex-wrap: wrap; }
.button-group .btn-location { flex: 2; margin-bottom: 0; }
.button-group .btn-save { flex: 1; margin-bottom: 0; }
.radius-info { display: inline-block; background: #e9ecef; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 8px; color: #495057; }
hr { margin: 1.5rem 0; border-color: #e9ecef; }

/* ========== DARK MODE STYLES ========== */
body.dark-mode .location-card {
    background: #1e1e2e;
    border: 1px solid #3a3a4a;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

body.dark-mode .location-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

body.dark-mode .location-body {
    background: #1e1e2e;
}

body.dark-mode .current-location {
    background: #2a2a3a;
    border-color: #3a3a4a;
}

body.dark-mode .current-location i {
    color: #60a5fa;
}

body.dark-mode .current-location strong,
body.dark-mode .current-location .text-muted {
    color: #e6edf3;
}

body.dark-mode .current-location small {
    color: #8b949e !important;
}

body.dark-mode .radius-select {
    background: #2a2a3a;
    border-color: #3a3a4a;
    color: #fff;
}

body.dark-mode .radius-select option {
    background: #2a2a3a;
    color: #fff;
}

body.dark-mode .radius-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}

body.dark-mode .form-label {
    color: #e6edf3;
}

body.dark-mode .radius-info {
    background: #3a3a4a;
    color: #8b949e;
}

body.dark-mode .btn-location {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .btn-location:hover {
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
}

body.dark-mode .btn-save {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

body.dark-mode .btn-save:hover {
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
}

body.dark-mode .btn-secondary {
    background: #3a3a4a;
}

body.dark-mode .btn-secondary:hover {
    background: #4a4a5a;
}

body.dark-mode .location-note {
    color: #8b949e;
    border-top-color: #3a3a4a;
}

body.dark-mode .location-note i {
    color: #60a5fa;
}

body.dark-mode .location-note strong {
    color: #e6edf3;
}

body.dark-mode hr {
    border-color: #3a3a4a;
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
</style>

<div class="location-container">
    <div class="location-card">
        <div class="location-header">
            <i class="fas fa-map-marker-alt"></i>
            <h2><?= __('set_your_location') ?></h2>
            <p><?= __('we_show_nearby') ?></p>
        </div>
        <div class="location-body">
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($current_lat && $current_lng): ?>
            <div class="current-location">
                <i class="fas fa-location-dot"></i>
                <strong><?= __('your_current_service_area') ?>:</strong><br>
                <?= htmlspecialchars($address) ?>
                <br>
                <small class="text-muted">Lat: <?= $current_lat ?> | Lng: <?= $current_lng ?> | <?= __('radius') ?>: <strong id="currentRadiusDisplay"><?= $current_radius ?></strong> KM</small>
            </div>
            <?php endif; ?>
            
            <!-- Auto Detect Location Form (First Time Setup) -->
            <form method="POST" id="autoLocationForm">
                <input type="hidden" name="auto_location" value="1">
                <input type="hidden" name="lat" id="auto_lat">
                <input type="hidden" name="lng" id="auto_lng">
                
                <div class="mb-3">
                    <label class="form-label">
                        <?= __('service_radius') ?>
                        <span class="radius-info"><?= __('how_far_services') ?></span>
                    </label>
                    <select name="radius" class="radius-select" id="radiusSelect">
                        <option value="10" <?= $current_radius == 10 ? 'selected' : '' ?>>10 <?= __('km_very_local') ?></option>
                        <option value="25" <?= $current_radius == 25 ? 'selected' : '' ?>>25 KM</option>
                        <option value="40" <?= $current_radius == 40 ? 'selected' : '' ?>>40 <?= __('km_recommended') ?></option>
                        <option value="50" <?= $current_radius == 50 ? 'selected' : '' ?>>50 KM</option>
                        <option value="100" <?= $current_radius == 100 ? 'selected' : '' ?>>100 <?= __('km_wide_area') ?></option>
                    </select>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-location" id="getLocationBtn">
                        <i class="fas fa-location-dot"></i> <?= __('use_my_location') ?>
                    </button>
                    
                    <?php if ($current_lat && $current_lng): ?>
                    <button type="button" class="btn-save" id="saveRadiusBtn">
                        <i class="fas fa-save"></i> <?= __('save_radius') ?>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Separate form for saving radius only (without changing location) -->
            <?php if ($current_lat && $current_lng): ?>
            <form method="POST" id="saveRadiusForm" style="display: none;">
                <input type="hidden" name="save_radius" value="1">
                <input type="hidden" name="radius" id="saveRadiusValue" value="<?= $current_radius ?>">
            </form>
            <?php endif; ?>
            
            <hr>
            
            <div class="location-note">
                <i class="fas fa-info-circle"></i>
                <?= __('location_note') ?>
                <br><br>
                <strong><?= __('note') ?>:</strong> 
                <ul style="text-align: left; margin-top: 8px; padding-left: 20px;">
                    <li><strong><?= __('use_my_location') ?></strong> - <?= __('first_time_setup') ?></li>
                    <li><strong><?= __('save_radius') ?></strong> - <?= __('update_only_radius') ?></li>
                </ul>
                <br>
                <small class="text-muted">📍 <?= __('location_example') ?></small>
            </div>
            
            <div style="text-align: center; margin-top: 1rem;">
                <a href="javascript:history.back()" class="btn-secondary" style="padding: 10px 20px; font-size: 13px;">
                    <i class="fas fa-arrow-left"></i> <?= __('go_back') ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('getLocationBtn').addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __('detecting_location') ?>';
    btn.disabled = true;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('auto_lat').value = position.coords.latitude;
                document.getElementById('auto_lng').value = position.coords.longitude;
                document.getElementById('autoLocationForm').submit();
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
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
});

// Save Radius Button - Update only radius without changing location
document.getElementById('saveRadiusBtn')?.addEventListener('click', function() {
    const btn = this;
    const radiusSelect = document.getElementById('radiusSelect');
    const selectedRadius = radiusSelect.value;
    const currentRadius = <?= $current_radius ?>;
    
    if (selectedRadius == currentRadius) {
        alert('<?= __('radius_already_set') ?> ' + currentRadius + ' KM. <?= __('no_changes_made') ?>');
        return;
    }
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __('saving') ?>...';
    btn.disabled = true;
    
    document.getElementById('saveRadiusValue').value = selectedRadius;
    document.getElementById('saveRadiusForm').submit();
});

// Update radius display when dropdown changes (for visual feedback)
document.getElementById('radiusSelect')?.addEventListener('change', function() {
    const selectedRadius = this.value;
    const currentRadiusDisplay = document.getElementById('currentRadiusDisplay');
    if (currentRadiusDisplay) {
        currentRadiusDisplay.innerHTML = selectedRadius;
        currentRadiusDisplay.style.color = '#2563eb';
        setTimeout(() => {
            currentRadiusDisplay.style.color = '';
        }, 1000);
    }
});
</script>

<?php include 'footer.php'; ?>