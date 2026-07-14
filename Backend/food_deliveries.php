<?php
session_start();
include '../backend/db.php';
$pageTitle = 'Food Deliveries - Zero Hunger';
include 'header.php';

if (isset($_POST['assign_rider'])) {
    $req_id = intval($_POST['request_id']);
    $rider_id = intval($_POST['rider_id']);
    mysqli_query($conn, "UPDATE requests SET rider_id = $rider_id, delivery_status = 'Assigned' WHERE request_id = $req_id");
    echo "<script>alert('Rider assigned!'); window.location.href='food_deliveries.php';</script>"; exit;
}

$requests = mysqli_query($conn, "SELECT r.*, fd.food_item, u.full_name as ngo_name, u.profile_pic as ngo_pic FROM requests r JOIN food_donations fd ON r.donation_id = fd.donation_id JOIN users u ON r.receiver_id = u.user_id WHERE r.status != 'Cancelled' ORDER BY r.request_id DESC");
$riders_query = mysqli_query($conn, "SELECT user_id, full_name, profile_pic FROM users WHERE role_id = 4 AND is_active = 1");
$riders_array = [];
if ($riders_query) while($r = mysqli_fetch_assoc($riders_query)) $riders_array[] = $r;
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--green-600:#226e42;--green-700:#174d2e;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;gap:12px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-card-header h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-card-header p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}
.dh-table{width:100%;border-collapse:collapse;font-size:13.5px;}
.dh-table thead th{background:var(--gray-50);padding:10px 14px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-500);border-bottom:1.5px solid var(--gray-200);text-align:left;white-space:nowrap;}
.dh-table tbody tr{border-bottom:1px solid var(--gray-100);transition:background .1s;}
.dh-table tbody tr:hover{background:var(--gray-50);}
.dh-table tbody tr:last-child{border-bottom:none;}
.dh-table td{padding:12px 14px;color:var(--gray-700);vertical-align:middle;}
.ngo-cell .ngo-av{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;margin-right:8px;overflow:hidden;}
.ngo-cell .ngo-av img{width:100%;height:100%;object-fit:cover;}
.food-pill{display:inline-flex;align-items:center;gap:5px;background:var(--green-50);border:1px solid var(--green-100);color:var(--green-700);padding:4px 10px;border-radius:6px;font-size:12.5px;font-weight:600;}
.dh-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;}
.dh-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;}
.badge-delivered{background:#ede9fe;color:#6d28d9;}
.badge-assigned{background:#dbeafe;color:#1d4ed8;}
.badge-pending{background:#fef3c7;color:#b45309;}
.dh-select{padding:7px 30px 7px 10px;font-size:13px;color:var(--gray-900);background:#fff;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);outline:none;transition:border-color .15s;font-family:inherit;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236c757d' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;min-width:170px;}
.dh-select:focus{border-color:#3aad6a;box-shadow:0 0 0 3px rgba(46,148,88,.18);}
.assign-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:12.5px;font-weight:600;color:#fff;background:var(--green-500);border:none;border-radius:var(--radius-sm);cursor:pointer;transition:background .12s;white-space:nowrap;}
.assign-btn:hover{background:var(--green-600);}
.assign-form{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.dh-empty{padding:3rem 1rem;text-align:center;color:var(--gray-400);}
.rider-option-img{width:24px;height:24px;border-radius:50%;object-fit:cover;margin-right:8px;vertical-align:middle;}

/* Dark mode overrides */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .dh-card {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .dh-table td,
body.dark-mode .dh-table th {
    color: #fff;
}
body.dark-mode .dh-select {
    background: #2a2a3a;
    border-color: #3a3a4a;
    color: #fff;
}
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-truck"></i></div>
        <div>
            <h5>Active Delivery Logistics</h5>
            <p>Assign riders to pending delivery requests</p>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="dh-table">
        <thead>
            <tr>
                <th style="width:7%;">ID</th>
                <th style="width:22%;">NGO Receiver</th>
                <th style="width:18%;">Food Item</th>
                <th style="width:15%;">Status</th>
                <th style="width:38%;">Assign Rider</th>
            </tr>
        </thead>
        <tbody>
        <?php if($requests && mysqli_num_rows($requests)>0):
            while($row = mysqli_fetch_assoc($requests)):
                $ds = $row['delivery_status'] ?? 'Pending';
                $cls = $ds=='Delivered'?'badge-delivered':($ds=='Assigned'?'badge-assigned':'badge-pending');
                $ngo_pic = $row['ngo_pic'] ?? '';
        ?>
        <tr>
            <td style="color:var(--gray-400);font-size:13px;">#<?= $row['request_id'] ?></td>
            <td class="ngo-cell">
                <span class="ngo-av">
                    <?php if (!empty($ngo_pic) && file_exists('../' . $ngo_pic)): ?>
                        <img src="../<?= htmlspecialchars($ngo_pic) ?>" alt="NGO">
                    <?php else: ?>
                        <?= strtoupper(substr($row['ngo_name'],0,1)) ?>
                    <?php endif; ?>
                </span>
                <div style="display:inline-block;">
                    <div style="font-weight:600;color:var(--gray-900);"><?= htmlspecialchars($row['ngo_name']) ?></div>
                    <div style="font-size:11px;color:var(--gray-400);">Verified NGO</div>
                </div>
            </td>
            <td><span class="food-pill"><i class="fas fa-utensils" style="font-size:11px;"></i><?= htmlspecialchars($row['food_item']) ?></span></td>
            <td><span class="dh-badge <?= $cls ?>"><?= $ds ?></span></td>
            <td>
                <form method="POST" class="assign-form">
                    <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                    <select name="rider_id" class="dh-select" required>
                        <option value="">— Choose Rider —</option>
                        <?php foreach($riders_array as $r):
                            $sel = (isset($row['rider_id']) && $row['rider_id']==$r['user_id']) ? 'selected' : '';
                            $rider_pic = $r['profile_pic'] ?? '';
                        ?>
                        <option value="<?= $r['user_id'] ?>" <?= $sel ?>>
                            <?php if (!empty($rider_pic) && file_exists('../' . $rider_pic)): ?>
                                🖼️ 
                            <?php else: ?>
                                👤 
                            <?php endif; ?>
                            <?= htmlspecialchars($r['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="assign_rider" class="assign-btn">
                        <i class="fas fa-check-circle"></i> Assign
                    </button>
                </form>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5"><div class="dh-empty"><i class="fas fa-inbox" style="font-size:36px;color:var(--gray-300);display:block;margin-bottom:.5rem;"></i><p style="margin:0;font-size:13px;">No delivery tasks found.</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>