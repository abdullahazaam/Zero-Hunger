<?php
include 'db.php';
include 'header.php';

// Calculate general operational insights
$total_completed = 0;
$completed_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM deliveries WHERE status='Completed'");
if($completed_res) $total_completed = mysqli_fetch_assoc($completed_res)['cnt'] ?? 0;

$total_available = 0;
$available_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM food_donations WHERE status='Available'");
if($available_res) $total_available = mysqli_fetch_assoc($available_res)['cnt'] ?? 0;

$total_expired = 0;
$expired_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM food_donations WHERE status='Expired'");
if($expired_res) $total_expired = mysqli_fetch_assoc($expired_res)['cnt'] ?? 0;
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--green-600:#226e42;--green-700:#174d2e;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-md:12px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-card-header-left{display:flex;align-items:center;gap:12px;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-card-header h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-card-header p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}
.print-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;font-size:12px;font-weight:600;color:var(--gray-700);background:#fff;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);cursor:pointer;transition:all .12s;text-decoration:none;}
.print-btn:hover{background:var(--gray-50);border-color:var(--gray-400);}

/* Export Buttons Group - Fixed Spacing */
.export-group {
    display: flex;
    gap: 12px;
    margin: 1.25rem 1.5rem;
    flex-wrap: wrap;
}
.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 600;
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}
.export-btn.excel { background: #28a745; color: white; border: none; }
.export-btn.excel:hover { background: #1e7e34; transform: translateY(-2px); }
.export-btn.users { background: #17a2b8; color: white; border: none; }
.export-btn.users:hover { background: #117a8b; transform: translateY(-2px); }
.export-btn.pdf { background: #dc3545; color: white; border: none; }
.export-btn.pdf:hover { background: #b02a37; transform: translateY(-2px); }

.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;padding:1.25rem 1.5rem;}
.stat-card{background:#fff;border-radius:var(--radius-md);padding:1.25rem;display:flex;align-items:center;justify-content:space-between;border:1px solid var(--gray-200);box-shadow:var(--shadow-card);transition:transform .1s;}
.stat-card:hover{transform:translateY(-2px);}
.stat-card.green{border-left:3px solid var(--green-500);}
.stat-card.blue{border-left:3px solid #3b82f6;}
.stat-card.red{border-left:3px solid #ef4444;}
.stat-info .stat-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-400);margin-bottom:4px;display:block;}
.stat-info .stat-value{font-size:28px;font-weight:800;color:var(--gray-900);line-height:1.2;}
.stat-info .stat-unit{font-size:12px;font-weight:500;color:var(--gray-400);margin-left:2px;}
.stat-icon{width:44px;height:44px;border-radius:40px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.stat-icon.green{background:var(--green-50);color:var(--green-500);}
.stat-icon.blue{background:#dbeafe;color:#2563eb;}
.stat-icon.red{background:#fee2e2;color:#dc2626;}
.reports-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:1.25rem;padding:0 1.5rem 1.5rem 1.5rem;}
.report-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);overflow:hidden;}
.report-card-header{display:flex;align-items:center;gap:8px;padding:1rem 1.25rem;border-bottom:1px solid var(--gray-100);background:var(--gray-50);}
.report-card-header i{font-size:16px;}
.report-card-header h6{margin:0;font-size:14px;font-weight:600;color:var(--gray-900);}
.dh-table{width:100%;border-collapse:collapse;font-size:13px;}
.dh-table thead th{background:var(--gray-50);padding:10px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);border-bottom:1px solid var(--gray-200);text-align:left;}
.dh-table tbody tr{border-bottom:1px solid var(--gray-100);}
.dh-table tbody tr:last-child{border-bottom:none;}
.dh-table td{padding:10px 12px;color:var(--gray-700);}
.dh-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.dh-badge.completed{background:#ede9fe;color:#6d28d9;}
.dh-badge.pending{background:#fef3c7;color:#b45309;}
.dh-empty{padding:2rem;text-align:center;color:var(--gray-400);font-size:13px;}
.dh-empty i{font-size:28px;margin-bottom:8px;display:block;}
@media print{.print-btn,.dh-card-header .dh-header-icon,.export-group{display: none;}.stats-grid,.reports-grid{gap:0.5rem;padding:0;}.stat-card{border:1px solid #ddd;box-shadow:none;}}

/* Dark mode overrides */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .dh-card,
body.dark-mode .stat-card,
body.dark-mode .report-card {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .stat-info .stat-value,
body.dark-mode .dh-table td {
    color: #fff;
}
body.dark-mode .report-card-header {
    background: #1e1e2e;
}
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-card-header-left">
            <div class="dh-header-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <h5>Operational Insights & Reports</h5>
                <p>Realtime distribution analytics summary</p>
            </div>
        </div>
        <button onclick="window.print()" class="print-btn"><i class="fas fa-print"></i> Print Report</button>
    </div>

    <!-- Export Buttons - Fixed spacing with margin -->
    <div class="export-group">
        <a href="export_reports.php?type=donations" class="export-btn excel">
            <i class="fas fa-file-excel"></i> Export Donations
        </a>
        <a href="export_reports.php?type=users" class="export-btn users">
            <i class="fas fa-file-excel"></i> Export Users
        </a>
        <a href="export_reports.php?type=deliveries" class="export-btn pdf">
            <i class="fas fa-file-excel"></i> Export Deliveries
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-info">
                <span class="stat-label">Success Dispatches</span>
                <span class="stat-value"><?= $total_completed ?></span>
                <span class="stat-unit">Batches</span>
            </div>
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-info">
                <span class="stat-label">Onboard Stock</span>
                <span class="stat-value"><?= $total_available ?></span>
                <span class="stat-unit">Available</span>
            </div>
            <div class="stat-icon blue"><i class="fas fa-boxes"></i></div>
        </div>
        <div class="stat-card red">
            <div class="stat-info">
                <span class="stat-label">Wasted / Expired</span>
                <span class="stat-value"><?= $total_expired ?></span>
                <span class="stat-unit">Items</span>
            </div>
            <div class="stat-icon red"><i class="fas fa-trash-alt"></i></div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="reports-grid">
        <!-- Most Active Donors -->
        <div class="report-card">
            <div class="report-card-header">
                <i class="fas fa-medal" style="color:#f59e0b;"></i>
                <h6>Most Active Food Donors</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="dh-table">
                    <thead>
                        <tr><th>Donor Name</th><th>Email</th><th class="text-end">Listings</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $donor_rank_q = "SELECT u.full_name, u.email, COUNT(fd.donation_id) as total_posts 
                                         FROM users u 
                                         JOIN food_donations fd ON u.user_id = fd.donor_id 
                                         GROUP BY u.user_id 
                                         ORDER BY total_posts DESC LIMIT 5";
                        $donor_res = mysqli_query($conn, $donor_rank_q);
                        if($donor_res && mysqli_num_rows($donor_res) > 0) {
                            while($dr = mysqli_fetch_assoc($donor_res)) {
                                echo "<tr>
                                    <td><strong>".htmlspecialchars($dr['full_name'])."</strong></td>
                                    <td style='color:var(--gray-500);'>".htmlspecialchars($dr['email'])."</div>
                                    <td class='text-end fw-bold' style='color:var(--green-600);'>".$dr['total_posts']."</div>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='dh-empty'><i class='fas fa-chart-simple'></i>No donor logs captured yet.</div></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Latest Deliveries -->
        <div class="report-card">
            <div class="report-card-header">
                <i class="fas fa-history" style="color:#3b82f6;"></i>
                <h6>Latest Deliveries Track Log</h6>
            </div>
            <div style="overflow-x:auto;">
                <table class="dh-table">
                    <thead>
                        <tr><th>Delivery ID</th><th>Status</th><th class="text-end">Timestamp</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $del_log_q = "SELECT delivery_id, status, created_at FROM deliveries ORDER BY delivery_id DESC LIMIT 5";
                        $del_res = mysqli_query($conn, $del_log_q);
                        if($del_res && mysqli_num_rows($del_res) > 0) {
                            while($dl = mysqli_fetch_assoc($del_res)) {
                                $badge_class = ($dl['status'] == 'Completed') ? 'completed' : 'pending';
                                $badge_text = $dl['status'];
                                echo "<tr>
                                    <td style='color:var(--gray-400);'>#".$dl['delivery_id']."</div>
                                    <td><span class='dh-badge $badge_class'>$badge_text</span></div>
                                    <td class='text-end' style='color:var(--gray-500);'>".$dl['created_at']."</div>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='dh-empty'><i class='fas fa-truck-fast'></i>No tracked deliveries on record.</div><tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>