<?php
include '../Backend/db.php';
include 'header.php';

$query = "SELECT r.*, fd.food_item, 
          u_donor.full_name AS donor_name, u_donor.profile_pic AS donor_pic,
          u_ngo.full_name AS ngo_name, u_ngo.profile_pic AS ngo_pic,
          u_rider.full_name AS rider_name, u_rider.profile_pic AS rider_pic
          FROM requests r
          JOIN food_donations fd ON r.donation_id = fd.donation_id
          JOIN users u_donor ON fd.donor_id = u_donor.user_id
          JOIN users u_ngo ON r.receiver_id = u_ngo.user_id
          LEFT JOIN users u_rider ON r.rider_id = u_rider.user_id
          ORDER BY r.request_id DESC";
$result = mysqli_query($conn, $query);
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
.food-pill{display:inline-flex;align-items:center;gap:5px;background:var(--green-50);border:1px solid var(--green-100);color:var(--green-700);padding:4px 10px;border-radius:6px;font-size:12.5px;font-weight:600;}
.person-cell{display:flex;align-items:center;gap:7px;}
.person-av{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden;}
.person-av img{width:100%;height:100%;object-fit:cover;}
.dh-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;}
.dh-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;opacity:.7;}
.badge-delivered{background:#ede9fe;color:#6d28d9;}
.badge-transit{background:#dbeafe;color:#1d4ed8;}
.badge-pending{background:#fef3c7;color:#b45309;}
.unassigned-tag{font-size:12px;color:var(--gray-400);font-style:italic;}
.dh-empty{padding:3rem 1rem;text-align:center;color:var(--gray-400);}

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
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-map-marked-alt"></i></div>
        <div>
            <h5>Live Delivery Tracking</h5>
            <p>Full overview of all active and completed delivery chains</p>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="dh-table">
        <thead>
            <tr>
                <th style="width:6%;">ID</th>
                <th style="width:17%;">Food Item</th>
                <th style="width:18%;">Donor</th>
                <th style="width:18%;">NGO Receiver</th>
                <th style="width:18%;">Rider</th>
                <th style="width:14%;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if(mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $ds = $row['delivery_status'];
                $cls = $ds=='Delivered'?'badge-delivered':($ds=='Assigned'?'badge-transit':'badge-pending');
                $label = $ds=='Assigned'?'In Transit':$ds;
                $donor_pic = $row['donor_pic'] ?? '';
                $ngo_pic = $row['ngo_pic'] ?? '';
                $rider_pic = $row['rider_pic'] ?? '';
        ?>
        <tr>
            <td style="color:var(--gray-400);font-size:13px;">#<?= $row['request_id'] ?></td>
            <td><span class="food-pill"><i class="fas fa-utensils" style="font-size:11px;"></i><?= htmlspecialchars($row['food_item']) ?></span></td>
            <td>
                <div class="person-cell">
                    <div class="person-av donor">
                        <?php if (!empty($donor_pic) && file_exists('../' . $donor_pic)): ?>
                            <img src="../<?= htmlspecialchars($donor_pic) ?>" alt="Donor">
                        <?php else: ?>
                            <?= strtoupper(substr($row['donor_name'],0,1)) ?>
                        <?php endif; ?>
                    </div>
                    <span style="font-weight:500;"><?= htmlspecialchars($row['donor_name']) ?></span>
                </div>
            </div>
            <td>
                <div class="person-cell">
                    <div class="person-av ngo">
                        <?php if (!empty($ngo_pic) && file_exists('../' . $ngo_pic)): ?>
                            <img src="../<?= htmlspecialchars($ngo_pic) ?>" alt="NGO">
                        <?php else: ?>
                            <?= strtoupper(substr($row['ngo_name'],0,1)) ?>
                        <?php endif; ?>
                    </div>
                    <span style="font-weight:500;"><?= htmlspecialchars($row['ngo_name']) ?></span>
                </div>
            </div>
            <td>
                <?php if($row['rider_name']): ?>
                <div class="person-cell">
                    <div class="person-av rider">
                        <?php if (!empty($rider_pic) && file_exists('../' . $rider_pic)): ?>
                            <img src="../<?= htmlspecialchars($rider_pic) ?>" alt="Rider">
                        <?php else: ?>
                            <?= strtoupper(substr($row['rider_name'],0,1)) ?>
                        <?php endif; ?>
                    </div>
                    <span style="font-weight:500;"><?= htmlspecialchars($row['rider_name']) ?></span>
                </div>
                <?php else: ?>
                <span class="unassigned-tag"><i class="fas fa-clock" style="margin-right:4px;"></i>Unassigned</span>
                <?php endif; ?>
            </div>
            <td><span class="dh-badge <?= $cls ?>"><?= $label ?></span></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-map" style="font-size:36px;color:var(--gray-300);display:block;margin-bottom:.5rem;"></i><p style="margin:0;font-size:13px;">No deliveries on record.</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'footer.php'; ?>