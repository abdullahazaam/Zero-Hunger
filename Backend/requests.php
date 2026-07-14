<?php
ob_start();
include 'db.php';
include 'header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE u1.full_name LIKE '%$search%' OR fd.food_item LIKE '%$search%'";
}

$count_query = "SELECT COUNT(*) as total FROM requests r LEFT JOIN users u1 ON r.receiver_id = u1.user_id LEFT JOIN food_donations fd ON r.donation_id = fd.donation_id $where";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT r.*, u1.full_name AS ngo_name, u1.profile_pic AS ngo_pic, fd.food_item FROM requests r LEFT JOIN users u1 ON r.receiver_id = u1.user_id LEFT JOIN food_donations fd ON r.donation_id = fd.donation_id $where ORDER BY r.request_id DESC LIMIT $offset, $limit";
$res = mysqli_query($conn, $sql);
$requests = [];
while($row = mysqli_fetch_assoc($res)) { $requests[] = $row; }

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM requests WHERE request_id = $id");
    echo "<script>window.location.href='requests.php';</script>"; exit();
}
if (isset($_POST['updateRequest'])) {
    $id = intval($_POST['edit_id']);
    $status = mysqli_real_escape_string($conn, $_POST['edit_status']);
    mysqli_query($conn, "UPDATE requests SET status = '$status' WHERE request_id = $id");
    echo "<script>Swal.fire({icon:'success',title:'Updated!',text:'Request status updated',timer:1500,showConfirmButton:false}).then(()=>{window.location.href='requests.php';});</script>"; exit();
}
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;gap:12px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-card-header h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-card-header p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}

/* Search Filter - Fixed Spacing */
.search-filter{margin:1.25rem 1.5rem;display:flex;gap:12px;align-items:center;}
.search-filter form{display:flex;gap:12px;flex:1;}
.search-filter input{flex:1;padding:10px 14px;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);font-size:13px;outline:none;transition:all 0.2s;}
.search-filter input:focus{border-color:var(--green-400);box-shadow:0 0 0 3px rgba(46,148,88,.18);}
.search-filter button{padding:10px 24px;background:var(--green-500);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:600;cursor:pointer;transition:background 0.2s;}
.search-filter button:hover{background:var(--green-600);}
.search-filter .clear-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;background:#6c757d;color:#fff;border:none;border-radius:var(--radius-sm);text-decoration:none;font-weight:600;}
.search-filter .clear-btn:hover{background:#5a6268;}

.dh-table{width:100%;border-collapse:collapse;font-size:13.5px;}
.dh-table thead th{background:var(--gray-50);padding:10px 14px;font-size:11.5px;font-weight:700;text-transform:uppercase;color:var(--gray-500);border-bottom:1.5px solid var(--gray-200);text-align:left;}
.dh-table tbody tr{border-bottom:1px solid var(--gray-100);}
.dh-table tbody tr:hover{background:var(--gray-50);}
.dh-table td{padding:11px 14px;color:var(--gray-700);vertical-align:middle;}
.dh-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;}
.badge-approved{background:var(--green-100);color:var(--green-700);}
.badge-delivered{background:#ede9fe;color:#6d28d9;}
.badge-assigned{background:#dbeafe;color:#1d4ed8;}
.badge-rejected{background:#fee2e2;color:#b91c1c;}
.badge-pending{background:#fef3c7;color:#b45309;}
.ngo-cell{display:flex;align-items:center;gap:10px;}
.ngo-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;overflow:hidden;flex-shrink:0;background:#dbeafe;color:#1d4ed8;}
.ngo-av img{width:100%;height:100%;object-fit:cover;}
.ngo-name{font-weight:600;color:var(--gray-900);}
.edit-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;font-size:12px;font-weight:600;color:#2563eb;background:#eff6ff;border:1px solid #dbeafe;border-radius:6px;cursor:pointer;}
.del-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;font-size:12px;font-weight:600;color:#c0392b;background:#fff0f0;border:1px solid #ffc9c9;border-radius:6px;text-decoration:none;margin-left:5px;}
.pagination{display:flex;justify-content:flex-end;gap:5px;margin-top:1rem;padding:1rem 1.5rem;border-top:1px solid var(--gray-200);}
.pagination a,.pagination span{padding:6px 12px;border-radius:6px;text-decoration:none;background:var(--gray-50);border:1px solid var(--gray-300);color:var(--gray-700);}
.pagination a:hover{background:var(--green-500);color:#fff;}
.pagination .active{background:var(--green-500);color:#fff;}
.dh-empty{padding:3rem 1rem;text-align:center;color:var(--gray-400);}
.zh-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:3000;align-items:center;justify-content:center;}
.zh-modal-backdrop.open{display:flex;}
.zh-modal{background:#fff;border-radius:var(--radius-lg);max-width:440px;width:100%;overflow:hidden;}
.zh-modal-header{display:flex;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);background:var(--gray-50);}
.zh-modal-body{padding:1.25rem;}
.dh-label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px;}
.dh-input,.dh-select{width:100%;padding:9px 12px;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);margin-bottom:1rem;}
.dh-btn-submit{width:100%;padding:10px;background:var(--green-500);color:#fff;border:none;border-radius:var(--radius-sm);cursor:pointer;}
.note-text{font-size:11.5px;color:var(--gray-400);margin-top:-6px;}

/* Dark mode overrides */
body.dark-mode .dh-card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
body.dark-mode .dh-card,
body.dark-mode .zh-modal {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
body.dark-mode .dh-table td,
body.dark-mode .dh-table th,
body.dark-mode .ngo-name {
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
        <div class="dh-header-icon"><i class="fas fa-bullhorn"></i></div>
        <div><h5>Food Requests Management</h5><p>Review and update NGO food request statuses</p></div>
    </div>
    
    <div class="search-filter">
        <form method="GET">
            <input type="text" name="search" placeholder="🔍 Search by NGO or food item..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if($search): ?>
                <a href="requests.php" class="clear-btn"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead>
                <tr>
                    <th style="width:8%;">ID</th>
                    <th style="width:25%;">NGO</th>
                    <th style="width:20%;">Food Item</th>
                    <th style="width:15%;">Status</th>
                    <th style="width:12%;">Date</th>
                    <th style="width:20%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(count($requests)>0): foreach($requests as $row):
                $ds = ($row['delivery_status']=='Delivered') ? 'Delivered' : (($row['delivery_status']=='Assigned') ? 'Assigned' : $row['status']);
                $cls='badge-pending';
                if($ds=='Approved') $cls='badge-approved';
                elseif($ds=='Delivered') $cls='badge-delivered';
                elseif($ds=='Assigned') $cls='badge-assigned';
                elseif($ds=='Rejected') $cls='badge-rejected';
                $ngo = $row['ngo_name'] ?? 'Direct';
                $ngo_pic = $row['ngo_pic'] ?? '';
                $initials = strtoupper(substr($ngo, 0, 1));
            ?>
            <tr>
                <td style="color:var(--gray-400);">#<?= $row['request_id'] ?></td>
                <td>
                    <div class="ngo-cell">
                        <div class="ngo-av">
                            <?php if (!empty($ngo_pic) && file_exists('../' . $ngo_pic)): ?>
                                <img src="../<?= htmlspecialchars($ngo_pic) ?>" alt="NGO">
                            <?php else: ?>
                                <?= $initials ?>
                            <?php endif; ?>
                        </div>
                        <div class="ngo-name"><?= htmlspecialchars($ngo) ?></div>
                    </div>
                </div>
                <td><?= htmlspecialchars($row['food_item'] ?? '—') ?></td>
                <td><span class="dh-badge <?= $cls ?>"><?= $ds ?></span></td>
                <td style="font-size:12px;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                <td>
                    <button class="edit-btn" onclick="openReqModal(<?= $row['request_id'] ?>,'<?= htmlspecialchars($row['food_item']??'',ENT_QUOTES) ?>','<?= htmlspecialchars($ngo,ENT_QUOTES) ?>','<?= $row['status'] ?>')"><i class="fas fa-pen"></i> Edit</button>
                    <a href="requests.php?delete=<?= $row['request_id'] ?>" class="del-btn" onclick="return confirm('Delete this request?')"><i class="fas fa-trash-alt"></i></a>
                </div>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-inbox" style="font-size:36px;margin-bottom:10px;display:block;"></i>No requests found.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if($total_pages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">« Prev</a><?php endif; ?>
        <?php for($i=1; $i<=$total_pages; $i++): ?>
            <?php if($i == $page): ?><span class="active"><?= $i ?></span><?php else: ?><a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if($page < $total_pages): ?><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next »</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="zh-modal-backdrop" id="reqModalBackdrop">
    <div class="zh-modal">
        <div class="zh-modal-header"><h5>Edit Request <span id="reqModalId"></span></h5><button class="zh-modal-close" onclick="document.getElementById('reqModalBackdrop').classList.remove('open')">&times;</button></div>
        <form method="POST">
            <div class="zh-modal-body">
                <input type="hidden" name="edit_id" id="rm_id">
                <label class="dh-label">Food Item</label><input type="text" id="rm_food" class="dh-input" readonly>
                <label class="dh-label">NGO Name</label><input type="text" id="rm_ngo" class="dh-input" readonly>
                <label class="dh-label">Request Status</label>
                <select name="edit_status" id="rm_status" class="dh-select">
                    <option value="Pending">Pending</option><option value="Approved">Approved</option><option value="Rejected">Rejected</option>
                </select>
                <p class="note-text"><i class="fas fa-info-circle"></i> Delivery status (Assigned/Delivered) Rider Dashboard se control hota hai.</p>
            </div>
            <div class="zh-modal-footer" style="padding:1rem; border-top:1px solid var(--gray-200);"><button type="submit" name="updateRequest" class="dh-btn-submit"><i class="fas fa-check-circle"></i> Update Request</button></div>
        </form>
    </div>
</div>

<script>
function openReqModal(id, food, ngo, status) {
    document.getElementById('reqModalId').textContent = '#' + id;
    document.getElementById('rm_id').value = id;
    document.getElementById('rm_food').value = food;
    document.getElementById('rm_ngo').value = ngo;
    document.getElementById('rm_status').value = status;
    document.getElementById('reqModalBackdrop').classList.add('open');
}
document.getElementById('reqModalBackdrop').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
</script>

<?php include 'footer.php'; ob_end_flush(); ?>