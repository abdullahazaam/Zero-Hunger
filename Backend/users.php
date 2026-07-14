<?php
ob_start();
include 'db.php';
include 'header.php';

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Build query with search
$where = "";
if (!empty($search)) {
    $where = "WHERE u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%'";
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users u JOIN roles r ON u.role_id = r.role_id $where";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Get users with pagination - ADDED profile_pic column
$res = mysqli_query($conn, "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id $where ORDER BY u.user_id DESC LIMIT $offset, $limit");

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
    echo "<script>window.location.href='users.php';</script>"; exit();
}

if (isset($_POST['updateUser'])) {
    $id = intval($_POST['edit_id']);
    $full_name = trim($_POST['edit_full_name']);
    $email = trim($_POST['edit_email']);
    $role_id = intval($_POST['edit_role_id']);
    $phone = trim($_POST['edit_phone']);
    $address = trim($_POST['edit_address']);
    $is_active = intval($_POST['edit_is_active']);
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role_id=?, phone=?, address=?, is_active=? WHERE user_id=?");
    $stmt->bind_param("ssissii", $full_name, $email, $role_id, $phone, $address, $is_active, $id);
    if($stmt->execute()) echo "<script>Swal.fire({icon:'success',title:'Updated!',text:'User updated successfully',timer:1500,showConfirmButton:false}).then(()=>{window.location.href='users.php';});</script>";
    $stmt->close();
}
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--green-600:#226e42;--green-700:#174d2e;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-card-header-left{display:flex;align-items:center;gap:12px;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-header-title h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-header-title p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}
.add-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;font-size:13px;font-weight:600;color:#fff;background:var(--green-500);border:none;border-radius:var(--radius-sm);cursor:pointer;text-decoration:none;transition:all 0.2s;}
.add-btn:hover{background:var(--green-600);transform:translateY(-2px);}
.search-filter{display:flex;gap:12px;margin:1.25rem 1.5rem;}
.search-filter input{flex:1;padding:10px 14px;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);font-size:13px;outline:none;}
.search-filter input:focus{border-color:var(--green-400);box-shadow:0 0 0 3px rgba(46,148,88,.18);}
.search-filter button{padding:10px 24px;background:var(--green-500);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:600;cursor:pointer;}
.search-filter button:hover{background:var(--green-600);}
.search-filter .clear-btn{background:#6c757d;}
.search-filter .clear-btn:hover{background:#5a6268;}
.dh-table{width:100%;border-collapse:collapse;font-size:13.5px;}
.dh-table thead th{background:var(--gray-50);padding:14px 16px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-500);border-bottom:1.5px solid var(--gray-200);text-align:left;white-space:nowrap;}
.dh-table tbody tr{border-bottom:1px solid var(--gray-100);transition:background .1s;}
.dh-table tbody tr:hover{background:var(--gray-50);}
.dh-table tbody tr:last-child{border-bottom:none;}
.dh-table td{padding:14px 16px;color:var(--gray-700);vertical-align:middle;}

/* User cell with profile picture */
.user-cell{display:flex;align-items:center;gap:12px;}
.user-av{width:40px;height:40px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;overflow:hidden;}
.user-av img{width:100%;height:100%;object-fit:cover;}
.user-av.admin{background:#f3e8ff;color:#7c3aed;}
.user-av.donor{background:var(--green-100);color:var(--green-700);}
.user-av.ngo{background:#fef3c7;color:#b45309;}
.user-av.rider{background:#ffedd5;color:#ea580c;}
.user-name{font-weight:700;color:var(--gray-900);font-size:14px;}
.dh-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;font-size:12px;font-weight:600;white-space:nowrap;}
.role-admin{background:#f3e8ff;color:#7c3aed;}
.role-donor{background:var(--green-100);color:var(--green-700);}
.role-ngo{background:#fef3c7;color:#b45309;}
.role-rider{background:#ffedd5;color:#ea580c;}
.status-active{background:var(--green-50);color:var(--green-600);display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;white-space:nowrap;}
.status-active::before{content:'';width:8px;height:8px;border-radius:50%;background:var(--green-400);}
.status-inactive{background:#fee2e2;color:#b91c1c;display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:30px;white-space:nowrap;}
.status-inactive::before{content:'';width:8px;height:8px;border-radius:50%;background:#ef4444;}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: nowrap;
    white-space: nowrap;
}
.edit-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 14px;
    font-size:12px;
    font-weight:600;
    color:#2563eb;
    background:#eff6ff;
    border:1px solid #dbeafe;
    border-radius:6px;
    cursor:pointer;
    transition:all 0.2s;
    text-decoration:none;
    white-space: nowrap;
}
.edit-btn:hover{
    background:#dbeafe;
    transform:translateY(-1px);
}
.del-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 14px;
    font-size:12px;
    font-weight:600;
    color:#c0392b;
    background:#fff0f0;
    border:1px solid #ffc9c9;
    border-radius:6px;
    cursor:pointer;
    text-decoration:none;
    transition:all 0.2s;
    white-space: nowrap;
}
.del-btn:hover{
    background:#ffe0e0;
    transform:translateY(-1px);
}
.pagination{display:flex;justify-content:flex-end;gap:6px;margin-top:1.25rem;padding:1rem 1.5rem;border-top:1px solid var(--gray-200);}
.pagination a, .pagination span{padding:7px 14px;border-radius:6px;text-decoration:none;background:var(--gray-50);border:1px solid var(--gray-300);color:var(--gray-700);font-size:13px;transition:all 0.2s;}
.pagination a:hover{background:var(--green-500);color:#fff;border-color:var(--green-500);}
.pagination .active{background:var(--green-500);color:#fff;border-color:var(--green-500);}
.zh-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:3000;align-items:center;justify-content:center;}
.zh-modal-backdrop.open{display:flex;}
.zh-modal{background:#fff;border-radius:var(--radius-lg);max-width:500px;width:100%;overflow:hidden;}
.zh-modal-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.5rem;border-bottom:1px solid var(--gray-200);background:var(--gray-50);}
.zh-modal-header h5{margin:0;font-size:16px;font-weight:700;}
.zh-modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:var(--gray-400);}
.zh-modal-body{padding:1.5rem;}
.dh-label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--gray-700);}
.dh-input,.dh-select,.dh-textarea{width:100%;padding:10px 12px;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);margin-bottom:1rem;font-size:13px;}
.dh-input:focus,.dh-select:focus{border-color:var(--green-400);outline:none;}
.dh-btn-submit{width:100%;padding:12px;background:var(--green-500);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:600;cursor:pointer;transition:background 0.2s;}
.dh-btn-submit:hover{background:var(--green-600);}
.row-form{display:grid;grid-template-columns:1fr 1fr;gap:15px;}

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
body.dark-mode .user-name {
    color: #fff;
}
body.dark-mode .search-filter input {
    background: #2a2a3a;
    border-color: #3a3a4a;
    color: #fff;
}
</style>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-card-header-left">
            <div class="dh-header-icon"><i class="fas fa-users"></i></div>
            <div class="dh-header-title">
                <h5>Platform Users</h5>
                <p>Manage all registered accounts</p>
            </div>
        </div>
        <button class="add-btn" onclick="document.getElementById('addUserBackdrop').classList.add('open')">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>
    
    <!-- Search Filter -->
    <div class="search-filter">
        <form method="GET" style="display:flex; gap:12px; width:100%;">
            <input type="text" name="search" placeholder="🔍 Search by name, email or phone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if($search): ?>
                <a href="users.php" class="clear-btn" style="padding:10px 24px; background:#6c757d; color:#fff; border-radius:8px; text-decoration:none;"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div style="overflow-x:auto;">
        <table class="dh-table">
            <thead>
                <tr>
                    <th style="width:6%;">ID</th>
                    <th style="width:25%;">User</th>
                    <th style="width:20%;">Email</th>
                    <th style="width:12%;">Phone</th>
                    <th style="width:10%;">Role</th>
                    <th style="width:9%;">Status</th>
                    <th style="width:18%;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)):
                $rid = $row['role_id'];
                $avCls = $rid==1?'admin':($rid==2?'donor':($rid==3?'ngo':'rider'));
                $roleCls = 'role-'.$avCls;
                $initials = strtoupper(substr($row['full_name'],0,1));
                $profile_pic = $row['profile_pic'] ?? '';
            ?>
                <tr>
                    <td style="color:var(--gray-400);font-weight:600;"><?= $row['user_id'] ?></td>
                    <td>
                        <div class="user-cell">
                            <div class="user-av <?= $avCls ?>">
                                <?php if (!empty($profile_pic) && file_exists('../' . $profile_pic)): ?>
                                    <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile" style="width:40px;height:40px;object-fit:cover;">
                                <?php else: ?>
                                    <?= $initials ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-name"><?= htmlspecialchars($row['full_name']) ?></div>
                        </div>
                    </div>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><span class="dh-badge <?= $roleCls ?>"><?= htmlspecialchars($row['role_name']) ?></span></td>
                    <td>
                        <?php if($row['is_active']): ?>
                            <span class="status-active">Active</span>
                        <?php else: ?>
                            <span class="status-inactive">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <td class="action-buttons">
                        <button class="edit-btn editBtn"
                            data-id="<?= $row['user_id'] ?>" 
                            data-name="<?= htmlspecialchars($row['full_name'],ENT_QUOTES) ?>"
                            data-email="<?= htmlspecialchars($row['email'],ENT_QUOTES) ?>" 
                            data-phone="<?= htmlspecialchars($row['phone'],ENT_QUOTES) ?>"
                            data-address="<?= htmlspecialchars($row['address']??'',ENT_QUOTES) ?>" 
                            data-role="<?= $row['role_id'] ?>"
                            data-active="<?= $row['is_active'] ?>">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                        <a href="users.php?delete=<?= $row['user_id'] ?>" class="del-btn" onclick="return confirm('Delete this user?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </div>
                </tr>
            <?php endwhile; ?>
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

<!-- Edit User Modal -->
<div class="zh-modal-backdrop" id="editUserBackdrop">
    <div class="zh-modal">
        <div class="zh-modal-header">
            <h5>Edit User <span id="editModalId" style="color:var(--gray-400);font-size:13px;"></span></h5>
            <button class="zh-modal-close" onclick="document.getElementById('editUserBackdrop').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <div class="zh-modal-body">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="row-form">
                    <div>
                        <label class="dh-label">Full Name</label>
                        <input type="text" id="edit_full_name" name="edit_full_name" class="dh-input" required>
                    </div>
                    <div>
                        <label class="dh-label">Email</label>
                        <input type="email" id="edit_email" name="edit_email" class="dh-input" required>
                    </div>
                </div>
                <div class="row-form">
                    <div>
                        <label class="dh-label">Phone</label>
                        <input type="text" id="edit_phone" name="edit_phone" class="dh-input">
                    </div>
                    <div>
                        <label class="dh-label">Role</label>
                        <select id="edit_role_id" name="edit_role_id" class="dh-select">
                            <option value="1">Admin</option>
                            <option value="2">Donor</option>
                            <option value="3">NGO</option>
                            <option value="4">Rider</option>
                        </select>
                    </div>
                </div>
                <label class="dh-label">Address</label>
                <textarea id="edit_address" name="edit_address" class="dh-textarea" rows="2"></textarea>
                <label class="dh-label">Status</label>
                <select id="edit_is_active" name="edit_is_active" class="dh-select">
                    <option value="1">Active</option>
                    <option value="0">Deactivated</option>
                </select>
            </div>
            <div class="zh-modal-footer" style="padding:1rem 1.5rem; border-top:1px solid var(--gray-200);">
                <button type="submit" name="updateUser" class="dh-btn-submit"><i class="fas fa-check-circle"></i> Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Add User Modal -->
<div class="zh-modal-backdrop" id="addUserBackdrop">
    <div class="zh-modal">
        <div class="zh-modal-header">
            <h5>Add New User</h5>
            <button class="zh-modal-close" onclick="document.getElementById('addUserBackdrop').classList.remove('open')">&times;</button>
        </div>
        <form method="POST" action="add_user.php">
            <div class="zh-modal-body">
                <div class="row-form">
                    <div>
                        <label class="dh-label">Full Name *</label>
                        <input type="text" name="full_name" class="dh-input" required>
                    </div>
                    <div>
                        <label class="dh-label">Email *</label>
                        <input type="email" name="email" class="dh-input" required>
                    </div>
                </div>
                <div class="row-form">
                    <div>
                        <label class="dh-label">Password *</label>
                        <input type="password" name="password" class="dh-input" required>
                    </div>
                    <div>
                        <label class="dh-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="dh-input" required>
                    </div>
                </div>
                <div class="row-form">
                    <div>
                        <label class="dh-label">Phone</label>
                        <input type="text" name="phone" class="dh-input">
                    </div>
                    <div>
                        <label class="dh-label">Role *</label>
                        <select name="role_id" class="dh-select" required>
                            <option value="2">Donor</option>
                            <option value="3">NGO</option>
                            <option value="4">Rider</option>
                        </select>
                    </div>
                </div>
                <label class="dh-label">Address</label>
                <textarea name="address" class="dh-textarea" rows="2"></textarea>
            </div>
            <div class="zh-modal-footer" style="padding:1rem 1.5rem; border-top:1px solid var(--gray-200);">
                <button type="submit" name="add_user" class="dh-btn-submit"><i class="fas fa-user-plus"></i> Add User</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editModalId').textContent = '#' + this.dataset.id;
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_full_name').value = this.dataset.name;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_phone').value = this.dataset.phone;
        document.getElementById('edit_address').value = this.dataset.address;
        document.getElementById('edit_role_id').value = this.dataset.role;
        document.getElementById('edit_is_active').value = this.dataset.active;
        document.getElementById('editUserBackdrop').classList.add('open');
    });
});

document.getElementById('editUserBackdrop').addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
document.getElementById('addUserBackdrop')?.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open'); });
</script>

<?php include 'footer.php'; ob_end_flush(); ?>