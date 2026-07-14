<?php
include 'db.php';
include 'header.php';

// Role definitions and descriptions for the UI (Admin role removed)
$role_descriptions = [
    2 => "Food Donors (Restaurants, Hotels, Individuals) who list surplus food items.",
    3 => "Non-Governmental Organizations & Welfare bodies who request food packs.",
    4 => "Riders & Volunteers who pick up donations and safely transport them."
];

// Role-specific colors and icons for visual distinction
$role_styles = [
    2 => ['bg' => '#f0faf4', 'color' => '#2e9458', 'icon' => 'fa-hand-holding-heart', 'badge' => 'bg-success', 'name' => 'Donor'],
    3 => ['bg' => '#fff7ed', 'color' => '#ea580c', 'icon' => 'fa-building', 'badge' => 'bg-warning', 'name' => 'Receiver_NGO'],
    4 => ['bg' => '#ffedd5', 'color' => '#b45309', 'icon' => 'fa-motorcycle', 'badge' => 'bg-info', 'name' => 'Rider']
];
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--green-600:#226e42;--green-700:#174d2e;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-md:12px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;gap:12px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-card-header h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-card-header p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}
.role-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.5rem;padding:1.5rem;}
.role-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);transition:all .2s ease;position:relative;overflow:hidden;}
.role-card:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(0,0,0,.08);}
.role-card-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--gray-100);}
.role-icon{width:48px;height:48px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:22px;}
.role-badge{font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;color:#fff;}
.role-card-body{padding:1.25rem;}
.role-name{font-size:18px;font-weight:700;color:var(--gray-900);margin:0 0 8px 0;}
.role-desc{font-size:13px;color:var(--gray-500);line-height:1.5;margin-bottom:1rem;}
.role-stats{display:flex;align-items:center;justify-content:space-between;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--gray-100);}
.stat-label{font-size:11.5px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);}
.stat-label i{margin-right:4px;}
.stat-count{font-size:22px;font-weight:700;color:var(--gray-900);}
.info-notice{display:flex;align-items:flex-start;gap:12px;margin:0 1.5rem 1.5rem 1.5rem;padding:1rem;background:var(--gray-50);border-radius:var(--radius-md);border-left:3px solid var(--green-500);}
.info-notice i{color:var(--green-500);font-size:16px;margin-top:2px;}
.info-notice p{margin:0;font-size:12.5px;color:var(--gray-600);line-height:1.5;}
.info-notice strong{color:var(--gray-900);}

/* Dark mode overrides */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .dh-card,
body.dark-mode .role-card {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .role-name,
body.dark-mode .stat-count {
    color: #fff;
}
body.dark-mode .role-desc,
body.dark-mode .info-notice p {
    color: #aaa;
}
body.dark-mode .info-notice {
    background: #1e1e2e;
    border-left-color: #3b82f6;
}
body.dark-mode .role-card-header {
    border-bottom-color: #3a3a4a;
}
body.dark-mode .role-stats {
    border-top-color: #3a3a4a;
}
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-tags"></i></div>
        <div>
            <h5>Platform Roles Definition</h5>
            <p>Zero Hunger system registration tier mappings & permissions</p>
        </div>
    </div>

    <div class="role-grid">
        <?php
        // Fetch total active users count grouped by role_id (excluding admin role_id=1)
        $query = "SELECT r.role_id, r.role_name, COUNT(u.user_id) AS total_users 
                  FROM roles r 
                  LEFT JOIN users u ON r.role_id = u.role_id 
                  WHERE r.role_id IN (2,3,4)
                  GROUP BY r.role_id, r.role_name
                  ORDER BY FIELD(r.role_id, 2,3,4)";
        $res = mysqli_query($conn, $query);

        if (!$res || mysqli_num_rows($res) == 0) {
            // Fallback template if roles table is empty
            foreach($role_descriptions as $id => $desc) {
                $style = $role_styles[$id];
                echo '
                <div class="role-card">
                    <div class="role-card-header">
                        <div class="role-icon" style="background:'.$style['bg'].';color:'.$style['color'].'">
                            <i class="fas '.$style['icon'].'"></i>
                        </div>
                        <span class="role-badge" style="background:'.$style['color'].'">ID: '.$id.'</span>
                    </div>
                    <div class="role-card-body">
                        <h4 class="role-name">'.$style['name'].'</h4>
                        <p class="role-desc">'.$desc.'</p>
                        <div class="role-stats">
                            <span class="stat-label"><i class="fas fa-users"></i> Registered Users</span>
                            <span class="stat-count">0</span>
                        </div>
                    </div>
                </div>';
            }
        } else {
            while ($row = mysqli_fetch_assoc($res)) {
                $id = $row['role_id'];
                $name = htmlspecialchars($row['role_name']);
                $count = $row['total_users'];
                $desc = $role_descriptions[$id] ?? "Custom system designated permission group.";
                $style = $role_styles[$id];
                ?>
                <div class="role-card">
                    <div class="role-card-header">
                        <div class="role-icon" style="background:<?= $style['bg'] ?>;color:<?= $style['color'] ?>">
                            <i class="fas <?= $style['icon'] ?>"></i>
                        </div>
                        <span class="role-badge" style="background:<?= $style['color'] ?>">ID: <?= $id ?></span>
                    </div>
                    <div class="role-card-body">
                        <h4 class="role-name"><?= $name ?></h4>
                        <p class="role-desc"><?= htmlspecialchars($desc) ?></p>
                        <div class="role-stats">
                            <span class="stat-label"><i class="fas fa-users"></i> Registered Users</span>
                            <span class="stat-count"><?= $count ?></span>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <div class="info-notice">
        <i class="fas fa-shield-alt"></i>
        <p>
            <strong>System Security Notice</strong><br>
            Roles key assignments directly govern login operations in authentication steps (<code>role_id</code> validation logic). 
            Modifying system parameters manually inside the source database can alter volunteer routing and donor submission security tokens.
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>