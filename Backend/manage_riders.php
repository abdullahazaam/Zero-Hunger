<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'db.php';
$pageTitle = 'Manage Riders - Admin Panel';
include 'header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

$count_query = "SELECT COUNT(*) as total FROM users WHERE role_id = 4 $where";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Toggle rider active status - FIXED: Use JavaScript redirect instead of header()
if (isset($_GET['toggle_status'])) {
    $rider_id = intval($_GET['toggle_status']);
    mysqli_query($conn, "UPDATE users SET is_active = 1 - is_active WHERE user_id = $rider_id AND role_id = 4");
    echo "<script>window.location.href='manage_riders.php';</script>";
    exit();
}

// Approve/Verify rider enrollment
if (isset($_GET['approve_enrollment'])) {
    $rider_id = intval($_GET['approve_enrollment']);
    mysqli_query($conn, "UPDATE users SET enrollment_completed = 1, enrollment_status = 'Verified' WHERE user_id = $rider_id");
    echo "<script>Swal.fire({icon:'success',title:'Approved!',text:'Rider enrollment approved',timer:1500,showConfirmButton:false}).then(()=>{window.location.href='manage_riders.php';});</script>";
    exit();
}

// Reject rider enrollment
if (isset($_GET['reject_enrollment'])) {
    $rider_id = intval($_GET['reject_enrollment']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $rider_id AND role_id = 4");
    echo "<script>Swal.fire({icon:'success',title:'Rejected!',text:'Rider enrollment rejected',timer:1500,showConfirmButton:false}).then(()=>{window.location.href='manage_riders.php';});</script>";
    exit();
}

$riders_query = mysqli_query($conn, "SELECT user_id, full_name, email, phone, is_active, enrollment_completed, enrollment_status, bike_number, license_number, profile_pic, cnic_front, cnic_back, address, created_at FROM users WHERE role_id = 4 $where ORDER BY user_id DESC LIMIT $offset, $limit");
?>

<style>
:root{
    --green-50:#f0faf4;
    --green-100:#d6f0e0;
    --green-500:#2e9458;
    --green-600:#226e42;
    --green-700:#174d2e;
    --gray-50:#f8f9fa;
    --gray-100:#f1f3f5;
    --gray-200:#e9ecef;
    --gray-300:#dee2e6;
    --gray-400:#adb5bd;
    --gray-500:#6c757d;
    --gray-700:#343a40;
    --gray-900:#212529;
    --radius-sm:8px;
    --radius-md:12px;
    --radius-lg:16px;
    --shadow-card:0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
}
.dh-card{
    background:#fff;
    border-radius:var(--radius-lg);
    border:1px solid var(--gray-200);
    box-shadow:var(--shadow-card);
    overflow:hidden;
}
.dh-card-header{
    display:flex;
    align-items:center;
    gap:12px;
    padding:1rem 1.5rem;
    background:linear-gradient(135deg, #2e7d32, #1b5e20);
    color:white;
    border-bottom:none;
}
.dh-header-icon{
    width:38px;
    height:38px;
    border-radius:var(--radius-sm);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    flex-shrink:0;
    background:rgba(255,255,255,0.2);
    color:white;
}
.dh-header-title h5{
    margin:0;
    font-size:16px;
    font-weight:700;
    color:white;
}
.dh-header-title p{
    margin:0;
    font-size:12.5px;
    color:rgba(255,255,255,0.85);
}
.search-filter{
    display:flex;
    gap:12px;
    margin:1.25rem 1.5rem;
}
.search-filter form{
    display:flex;
    gap:12px;
    width:100%;
}
.search-filter input{
    flex:1;
    padding:10px 14px;
    border:1.5px solid var(--gray-300);
    border-radius:var(--radius-sm);
    font-size:13px;
    outline:none;
    transition:all 0.2s;
}
.search-filter input:focus{
    border-color:var(--green-400);
    box-shadow:0 0 0 3px rgba(46,148,88,.18);
}
.search-filter button{
    padding:10px 24px;
    background:var(--green-500);
    color:#fff;
    border:none;
    border-radius:var(--radius-sm);
    font-weight:600;
    cursor:pointer;
    transition:background 0.2s;
}
.search-filter button:hover{
    background:var(--green-600);
}
.search-filter .clear-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:10px 24px;
    background:#6c757d;
    color:#fff;
    border:none;
    border-radius:var(--radius-sm);
    text-decoration:none;
    font-weight:600;
}
.search-filter .clear-btn:hover{
    background:#5a6268;
}
.dh-table{
    width:100%;
    border-collapse:collapse;
    font-size:13.5px;
}
.dh-table thead th{
    background:var(--gray-50);
    padding:14px 16px;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:var(--gray-500);
    border-bottom:1.5px solid var(--gray-200);
    text-align:left;
}
.dh-table tbody tr{
    border-bottom:1px solid var(--gray-100);
    transition:background .1s;
}
.dh-table tbody tr:hover{
    background:var(--gray-50);
}
.dh-table tbody tr:last-child{
    border-bottom:none;
}
.dh-table td{
    padding:14px 16px;
    color:var(--gray-700);
    vertical-align:middle;
}
.rider-info{
    display:flex;
    align-items:center;
    gap:12px;
}
.rider-avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:16px;
    font-weight:700;
    flex-shrink:0;
    overflow:hidden;
    background:#ffedd5;
    color:#ea580c;
}
.rider-avatar img{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
}
.rider-name{
    font-weight:700;
    color:var(--gray-900);
    font-size:14px;
}
.rider-email{
    font-size:11px;
    color:var(--gray-400);
    margin-top:2px;
}
.contact-cell{
    font-size:13px;
    color:var(--gray-600);
}
.contact-cell i{
    color:var(--gray-400);
    margin-right:5px;
    width:14px;
}
.bike-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:var(--gray-100);
    color:var(--gray-600);
    padding:5px 10px;
    border-radius:20px;
    font-size:11px;
    margin-bottom:5px;
}
.status-active{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:var(--green-50);
    color:var(--green-600);
    padding:5px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}
.status-active::before{
    content:'';
    width:8px;
    height:8px;
    border-radius:50%;
    background:var(--green-400);
}
.status-suspended{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fee2e2;
    color:#b91c1c;
    padding:5px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}
.status-suspended::before{
    content:'';
    width:8px;
    height:8px;
    border-radius:50%;
    background:#ef4444;
}
.enroll-approved{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:var(--green-50);
    color:var(--green-600);
    padding:5px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}
.enroll-approved::before{
    content:'';
    width:8px;
    height:8px;
    border-radius:50%;
    background:var(--green-400);
}
.enroll-pending{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fef3c7;
    color:#b45309;
    padding:5px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}
.enroll-pending::before{
    content:'';
    width:8px;
    height:8px;
    border-radius:50%;
    background:#f59e0b;
}
.action-btns{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.approve-btn{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 12px;
    font-size:11px;
    font-weight:600;
    color:#fff;
    background:#2563eb;
    border:none;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
    transition:background 0.2s;
}
.approve-btn:hover{
    background:#1d4ed8;
}
.reject-btn{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 12px;
    font-size:11px;
    font-weight:600;
    color:#c0392b;
    background:#fff0f0;
    border:1px solid #ffc9c9;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
}
.reject-btn:hover{
    background:#ffe0e0;
}
.activate-btn{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 12px;
    font-size:11px;
    font-weight:600;
    color:#fff;
    background:var(--green-500);
    border:none;
    border-radius:6px;
    text-decoration:none;
}
.activate-btn:hover{
    background:var(--green-600);
}
.deactivate-btn{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 12px;
    font-size:11px;
    font-weight:600;
    color:#c0392b;
    background:#fff0f0;
    border:1px solid #ffc9c9;
    border-radius:6px;
    text-decoration:none;
}
.deactivate-btn:hover{
    background:#ffe0e0;
}
.view-docs-btn{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 12px;
    font-size:11px;
    font-weight:600;
    color:#6c757d;
    background:#e9ecef;
    border:1px solid #dee2e6;
    border-radius:6px;
    text-decoration:none;
    cursor:pointer;
}
.view-docs-btn:hover{
    background:#dee2e6;
}
.pagination{
    display:flex;
    justify-content:flex-end;
    gap:6px;
    margin-top:1.25rem;
    padding:1rem 1.5rem;
    border-top:1px solid var(--gray-200);
}
.pagination a, .pagination span{
    padding:7px 14px;
    border-radius:6px;
    text-decoration:none;
    background:var(--gray-50);
    border:1px solid var(--gray-300);
    color:var(--gray-700);
    font-size:13px;
    transition:all 0.2s;
}
.pagination a:hover{
    background:var(--green-500);
    color:#fff;
    border-color:var(--green-500);
}
.pagination .active{
    background:var(--green-500);
    color:#fff;
    border-color:var(--green-500);
}
.dh-empty{
    padding:3rem 1rem;
    text-align:center;
    color:var(--gray-400);
}
.dh-empty i{
    font-size:48px;
    display:block;
    margin-bottom:1rem;
}
.modal-docs{
    max-width:600px;
}
.document-img{
    max-width:100%;
    border-radius:8px;
    margin-bottom:10px;
    border:1px solid var(--gray-300);
}

/* Dark mode overrides */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .dh-card {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .dh-table td,
body.dark-mode .dh-table th,
body.dark-mode .rider-name {
    color: #fff;
}
body.dark-mode .search-filter input {
    background: #2a2a3a;
    border-color: #3a3a4a;
    color: #fff;
}
body.dark-mode .search-filter input::placeholder {
    color: #888;
}
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-motorcycle"></i></div>
        <div class="dh-header-title">
            <h5>Registered Delivery Riders</h5>
            <p>Activate or suspend rider accounts &amp; approve enrollment requests</p>
        </div>
    </div>
    
    <!-- Search Filter -->
    <div class="search-filter">
        <form method="GET">
            <input type="text" name="search" placeholder="🔍 Search by name, email or phone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if($search): ?>
                <a href="manage_riders.php" class="clear-btn"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead>
                <tr>
                    <th style="width:20%;">Rider Info</th>
                    <th style="width:12%;">Contact</th>
                    <th style="width:18%;">Vehicle & License</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:12%;">Enrollment</th>
                    <th style="width:28%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($riders_query && mysqli_num_rows($riders_query) > 0):
                while ($rider = mysqli_fetch_assoc($riders_query)):
                    $initials = strtoupper(substr($rider['full_name'], 0, 1));
                    $profile_pic = $rider['profile_pic'] ?? '';
                    $full_pic_path = '../' . $profile_pic;
            ?>
                <tr>
                    <td>
                        <div class="rider-info">
                            <div class="rider-avatar">
                                <?php if (!empty($profile_pic) && file_exists($full_pic_path)): ?>
                                    <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile">
                                <?php else: ?>
                                    <?= $initials ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="rider-name"><?= htmlspecialchars($rider['full_name']) ?></div>
                                <div class="rider-email"><?= htmlspecialchars($rider['email']) ?></div>
                            </div>
                        </div>
                    </div>
                    <td>
                        <div class="contact-cell">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($rider['phone'] ?? 'N/A') ?>
                        </div>
                    </div>
                    <td>
                        <?php if(!empty($rider['bike_number'])): ?>
                            <div class="bike-badge"><i class="fas fa-motorcycle"></i> <?= htmlspecialchars($rider['bike_number']) ?></div>
                        <?php else: ?>
                            <div class="bike-badge"><i class="fas fa-motorcycle"></i> Not provided</div>
                        <?php endif; ?>
                        <?php if(!empty($rider['license_number'])): ?>
                            <div class="bike-badge"><i class="fas fa-id-card"></i> <?= htmlspecialchars($rider['license_number']) ?></div>
                        <?php else: ?>
                            <div class="bike-badge"><i class="fas fa-id-card"></i> Not provided</div>
                        <?php endif; ?>
                    </div>
                    <td>
                        <?php if($rider['is_active'] == 1): ?>
                            <span class="status-active">Active</span>
                        <?php else: ?>
                            <span class="status-suspended">Suspended</span>
                        <?php endif; ?>
                    </div>
                    <td>
                        <?php if(isset($rider['enrollment_completed']) && $rider['enrollment_completed'] == 1): ?>
                            <span class="enroll-approved">Verified</span>
                        <?php else: ?>
                            <span class="enroll-pending">Pending</span>
                        <?php endif; ?>
                    </div>
                    <td>
                        <div class="action-btns">
                            <?php if(!isset($rider['enrollment_completed']) || $rider['enrollment_completed'] != 1): ?>
                                <a href="manage_riders.php?approve_enrollment=<?= $rider['user_id'] ?>" class="approve-btn" onclick="return confirm('Approve this rider?')"><i class="fas fa-check-circle"></i> Approve</a>
                                <a href="manage_riders.php?reject_enrollment=<?= $rider['user_id'] ?>" class="reject-btn" onclick="return confirm('Reject this rider?')"><i class="fas fa-times-circle"></i> Reject</a>
                            <?php endif; ?>
                            
                            <?php if($rider['is_active'] == 1): ?>
                                <a href="manage_riders.php?toggle_status=<?= $rider['user_id'] ?>" class="deactivate-btn" onclick="return confirm('Suspend this rider?')"><i class="fas fa-ban"></i> Suspend</a>
                            <?php else: ?>
                                <a href="manage_riders.php?toggle_status=<?= $rider['user_id'] ?>" class="activate-btn" onclick="return confirm('Activate this rider?')"><i class="fas fa-check-circle"></i> Activate</a>
                            <?php endif; ?>
                            
                            <?php if(!empty($rider['cnic_front']) || !empty($rider['cnic_back']) || !empty($rider['license_pic'])): ?>
                                <button class="view-docs-btn" onclick="viewDocuments(
                                    '<?= addslashes($rider['cnic_front'] ?? '') ?>',
                                    '<?= addslashes($rider['cnic_back'] ?? '') ?>',
                                    '<?= addslashes($rider['license_pic'] ?? '') ?>',
                                    '<?= addslashes($profile_pic) ?>'
                                )"><i class="fas fa-file-alt"></i> Docs</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-motorcycle"></i><p>No riders registered yet.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i> Prev</a>
        <?php endif; ?>
        
        <?php 
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for($i = $start; $i <= $end; $i++): 
        ?>
            <?php if($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next <i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="docsModal" tabindex="-1">
    <div class="modal-dialog modal-docs">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rider Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="docsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewDocuments(cnic_front, cnic_back, license_pic, profile_pic) {
    let html = '';
    
    if (profile_pic && profile_pic !== '') {
        let pic_path = profile_pic;
        if (!pic_path.startsWith('http') && !pic_path.startsWith('/')) {
            pic_path = '../' + pic_path;
        }
        html += `<div class="mb-3"><strong>Profile Picture:</strong><br><img src="${pic_path}" class="document-img" style="max-width:200px;"></div>`;
    }
    if (cnic_front && cnic_front !== '') {
        let front_path = cnic_front;
        if (!front_path.startsWith('http') && !front_path.startsWith('/')) {
            front_path = '../' + front_path;
        }
        html += `<div class="mb-3"><strong>CNIC Front:</strong><br><img src="${front_path}" class="document-img"></div>`;
    }
    if (cnic_back && cnic_back !== '') {
        let back_path = cnic_back;
        if (!back_path.startsWith('http') && !back_path.startsWith('/')) {
            back_path = '../' + back_path;
        }
        html += `<div class="mb-3"><strong>CNIC Back:</strong><br><img src="${back_path}" class="document-img"></div>`;
    }
    if (license_pic && license_pic !== '') {
        let lic_path = license_pic;
        if (!lic_path.startsWith('http') && !lic_path.startsWith('/')) {
            lic_path = '../' + lic_path;
        }
        html += `<div class="mb-3"><strong>Driving License:</strong><br><img src="${lic_path}" class="document-img"></div>`;
    }
    
    if (html === '') {
        html = '<p class="text-muted">No documents uploaded.</p>';
    }
    
    document.getElementById('docsModalBody').innerHTML = html;
    var myModal = new bootstrap.Modal(document.getElementById('docsModal'));
    myModal.show();
}
</script>

<?php include 'footer.php'; ?>