<?php
include 'db.php';
$pageTitle = 'Activity Logs - Admin';
include 'header.php';

// Only admin can access
if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? 0) != 1)) {
    header('Location: admin_login.php');
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM activity_logs");
$total = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total / $limit);

$logs = mysqli_query($conn, "SELECT l.*, u.full_name FROM activity_logs l LEFT JOIN users u ON l.user_id = u.user_id ORDER BY l.created_at DESC LIMIT $offset, $limit");
?>

<style>
.logs-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
.logs-header { background: linear-gradient(135deg, #2e9458, #1b5e20); color: white; padding: 1.5rem; }
.logs-table { width: 100%; border-collapse: collapse; }
.logs-table th, .logs-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e0e0e0; }
.logs-table th { background: #f8f9fa; font-weight: 600; }
.logs-table tr:hover { background: #f8f9fa; }
.pagination { display: flex; justify-content: flex-end; gap: 8px; padding: 1rem; }
.pagination a, .pagination span { padding: 6px 12px; border-radius: 6px; text-decoration: none; background: #f0faf4; color: #2e9458; border: 1px solid #d6f0e0; }
.pagination .active { background: #2e9458; color: white; border-color: #2e9458; }
.badge-action { background: #e9ecef; color: #495057; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
body.dark-mode .logs-card { background: #2a2a3a; }
body.dark-mode .logs-table th { background: #1e1e2e; color: #fff; }
body.dark-mode .logs-table td { color: #c0c0d0; border-color: #3a3a4a; }
body.dark-mode .badge-action { background: #3a3a4a; color: #c0c0d0; }
</style>

<div class="container mt-4">
    <div class="logs-card">
        <div class="logs-header">
            <h3><i class="fas fa-history me-2"></i>Activity Logs</h3>
            <p>Track all user activities on the platform</p>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="logs-table">
                <thead>
                    <tr>
                        <th style="width:15%">Time</th>
                        <th style="width:15%">User</th>
                        <th style="width:20%">Action</th>
                        <th style="width:35%">Details</th>
                        <th style="width:15%">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($logs) > 0): ?>
                        <?php while($log = mysqli_fetch_assoc($logs)): ?>
                        <tr>
                            <td><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['full_name'] ?? 'Guest') ?></td>
                            <td><span class="badge-action"><?= htmlspecialchars($log['action']) ?></span></td>
                            <td><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                            <td><code><?= $log['ip_address'] ?></code></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:40px;">No activity logs found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i> Prev</a>
            <?php endif; ?>
            
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>