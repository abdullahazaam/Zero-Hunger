<?php
include 'db.php';
include 'header.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    echo "<script>
    if(confirm('Are you sure you want to delete this feedback?')) {
        window.location.href = 'feedback.php?confirm_delete=$id';
    } else { window.location.href = 'feedback.php'; }
    </script>"; exit();
}
if (isset($_GET['confirm_delete'])) {
    $id = intval($_GET['confirm_delete']);
    mysqli_query($conn, "DELETE FROM feedback WHERE feedback_id=$id");
    header("Location: feedback.php?deleted=1"); exit();
}
?>

<style>
:root{--green-50:#f0faf4;--green-100:#d6f0e0;--green-500:#2e9458;--green-600:#226e42;--green-700:#174d2e;--gray-50:#f8f9fa;--gray-100:#f1f3f5;--gray-200:#e9ecef;--gray-300:#dee2e6;--gray-400:#adb5bd;--gray-500:#6c757d;--gray-700:#343a40;--gray-900:#212529;--radius-sm:8px;--radius-md:12px;--radius-lg:16px;--shadow-card:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.06);--shadow-input-focus:0 0 0 3px rgba(46,148,88,.18);}
.dh-card{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:var(--shadow-card);overflow:hidden;}
.dh-card-header{display:flex;align-items:center;gap:12px;padding:1rem 1.5rem;background:linear-gradient(135deg, #2e7d32, #1b5e20);color:white;border-bottom:none;}
.dh-header-icon{width:38px;height:38px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;background:rgba(255,255,255,0.2);color:white;}
.dh-card-header h5{margin:0;font-size:16px;font-weight:700;color:white;}
.dh-card-header p{margin:0;font-size:12.5px;color:rgba(255,255,255,0.85);}
.dh-alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
.dh-alert.success{background:var(--green-50);border:1px solid var(--green-100);color:var(--green-600);}
.dh-table{width:100%;border-collapse:collapse;font-size:13.5px;}
.dh-table thead th{background:var(--gray-50);padding:10px 14px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray-500);border-bottom:1.5px solid var(--gray-200);text-align:left;white-space:nowrap;}
.dh-table tbody tr{border-bottom:1px solid var(--gray-100);transition:background .1s;}
.dh-table tbody tr:hover{background:var(--gray-50);}
.dh-table tbody tr:last-child{border-bottom:none;}
.dh-table td{padding:12px 14px;color:var(--gray-700);vertical-align:middle;}
.user-cell{display:flex;align-items:center;gap:9px;}
.user-av{width:32px;height:32px;border-radius:50%;background:var(--green-100);color:var(--green-700);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;overflow:hidden;}
.user-av img{width:100%;height:100%;object-fit:cover;}
.user-name{font-weight:600;color:var(--gray-900);font-size:13.5px;}
.user-email{font-size:11.5px;color:var(--gray-400);}
.star-filled{color:#f59e0b;}
.star-empty{color:var(--gray-300);}
.view-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;font-size:12px;font-weight:600;color:#2563eb;background:#eff6ff;border:1px solid #dbeafe;border-radius:6px;cursor:pointer;transition:background .12s;}
.view-btn:hover{background:#dbeafe;}
.del-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;font-size:12px;font-weight:600;color:#c0392b;background:#fff0f0;border:1px solid #ffc9c9;border-radius:6px;text-decoration:none;transition:background .12s;margin-left:5px;}
.del-btn:hover{background:#ffe0e0;color:#c0392b;}
.dh-empty{padding:3rem 1rem;text-align:center;color:var(--gray-400);}
.dh-empty i{font-size:36px;display:block;margin-bottom:.5rem;}

/* Modal */
.zh-modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:3000;align-items:center;justify-content:center;}
.zh-modal-backdrop.open{display:flex;}
.zh-modal{background:#fff;border-radius:var(--radius-lg);border:1px solid var(--gray-200);box-shadow:0 20px 60px rgba(0,0,0,.15);width:100%;max-width:480px;overflow:hidden;}
.zh-modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--gray-200);background:var(--gray-50);}
.zh-modal-header h5{margin:0;font-size:15px;font-weight:600;color:var(--gray-900);}
.zh-modal-close{background:none;border:none;font-size:18px;color:var(--gray-400);cursor:pointer;padding:0;line-height:1;}
.zh-modal-close:hover{color:var(--gray-700);}
.zh-modal-body{padding:1.25rem;}
.zh-modal-field{margin-bottom:.9rem;}
.zh-modal-field label{display:block;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:4px;}
.zh-modal-field p{margin:0;font-size:13.5px;color:var(--gray-700);}
.zh-modal-message{background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:.75rem 1rem;font-size:13.5px;color:var(--gray-700);white-space:pre-wrap;line-height:1.6;}
.zh-modal-footer{padding:.9rem 1.25rem;border-top:1px solid var(--gray-200);background:var(--gray-50);display:flex;justify-content:flex-end;gap:8px;}
.zh-btn-secondary{padding:8px 16px;font-size:13px;font-weight:600;background:#fff;border:1.5px solid var(--gray-300);border-radius:var(--radius-sm);cursor:pointer;color:var(--gray-700);}
.zh-btn-danger{padding:8px 16px;font-size:13px;font-weight:600;background:#c0392b;color:#fff;border:none;border-radius:var(--radius-sm);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.zh-btn-danger:hover{background:#a93226;color:#fff;}

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
body.dark-mode .user-name {
    color: #fff;
}
body.dark-mode .zh-modal {
    background: #2a2a3a;
    border-color: #3a3a4a;
}
</style>

<?php if (isset($_GET['deleted'])): ?>
<div class="dh-alert success mb-3"><i class="fas fa-check-circle"></i> Feedback deleted successfully.</div>
<?php endif; ?>

<div class="dh-card">
    <div class="dh-card-header">
        <div class="dh-header-icon"><i class="fas fa-comment-dots"></i></div>
        <div>
            <h5>User Appraisals & Feedback</h5>
            <p>All feedback submissions from platform users</p>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="dh-table">
        <thead>
            <tr>
                <th style="width:5%;">ID</th>
                <th style="width:25%;">User</th>
                <th style="width:22%;">Email</th>
                <th style="width:14%;">Rating</th>
                <th style="width:18%;">Date</th>
                <th style="width:16%;">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT f.*, u.full_name, u.email, u.profile_pic FROM feedback f JOIN users u ON f.user_id = u.user_id ORDER BY f.feedback_id DESC";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                $initials = strtoupper(substr($row['full_name'], 0, 1));
                $profile_pic = $row['profile_pic'] ?? '';
                $stars = '';
                for ($i = 1; $i <= 5; $i++)
                    $stars .= $i <= $row['rating'] ? '<i class="fas fa-star star-filled" style="font-size:13px;"></i>' : '<i class="far fa-star star-empty" style="font-size:13px;"></i>';
        ?>
        <tr>
            <td style="color:var(--gray-400);font-size:13px;">#<?= $row['feedback_id'] ?></td>
            <td>
                <div class="user-cell">
                    <div class="user-av">
                        <?php if (!empty($profile_pic) && file_exists('../' . $profile_pic)): ?>
                            <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile">
                        <?php else: ?>
                            <?= $initials ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="user-name"><?= htmlspecialchars($row['full_name']) ?></div>
                    </div>
                </div>
            </td>
            <td style="font-size:13px;color:var(--gray-500);"><?= htmlspecialchars($row['email']) ?></td>
            <td>
                <?= $stars ?>
                <span style="font-size:11.5px;color:var(--gray-400);margin-left:3px;">(<?= $row['rating'] ?>)</span>
            </td>
            <td style="font-size:12.5px;color:var(--gray-500);"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
            <td>
                <button class="view-btn viewFeedbackBtn"
                    data-id="<?= $row['feedback_id'] ?>"
                    data-name="<?= htmlspecialchars($row['full_name']) ?>"
                    data-email="<?= htmlspecialchars($row['email']) ?>"
                    data-message="<?= htmlspecialchars($row['message']) ?>"
                    data-rating="<?= $row['rating'] ?>"
                    data-date="<?= $row['created_at'] ?>">
                    <i class="fas fa-eye"></i> View
                </button>
                <a href="feedback.php?delete=<?= $row['feedback_id'] ?>" class="del-btn">
                    <i class="fas fa-trash-alt"></i>
                </a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6"><div class="dh-empty"><i class="fas fa-inbox"></i><p>No feedback submissions recorded yet.</p></div></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Custom Modal -->
<div class="zh-modal-backdrop" id="feedbackModal">
    <div class="zh-modal">
        <div class="zh-modal-header">
            <h5>Feedback Review <span id="modalId" style="font-size:12px;color:var(--gray-400);font-weight:400;"></span></h5>
            <button class="zh-modal-close" id="modalClose">&times;</button>
        </div>
        <div class="zh-modal-body">
            <div class="row g-3 mb-1">
                <div class="col-6">
                    <div class="zh-modal-field"><label>Sender</label><p id="modalName"></p></div>
                </div>
                <div class="col-6">
                    <div class="zh-modal-field"><label>Date</label><p id="modalDate" style="color:var(--gray-400);"></p></div>
                </div>
            </div>
            <div class="zh-modal-field"><label>Email</label><p id="modalEmail"></p></div>
            <div class="zh-modal-field"><label>Rating</label><div id="modalRating"></div></div>
            <div class="zh-modal-field"><label>Message</label><div class="zh-modal-message" id="modalMessage"></div></div>
        </div>
        <div class="zh-modal-footer">
            <button class="zh-btn-secondary" id="modalClose2">Close</button>
            <a id="modalDeleteBtn" href="#" class="zh-btn-danger"><i class="fas fa-trash-alt"></i> Delete</a>
        </div>
    </div>
</div>

<script>
const modal = document.getElementById('feedbackModal');
document.querySelectorAll('.viewFeedbackBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        let stars = '';
        for (let i = 1; i <= 5; i++)
            stars += i <= rating ? '<i class="fas fa-star" style="color:#f59e0b;font-size:15px;"></i>' : '<i class="far fa-star" style="color:#dee2e6;font-size:15px;"></i>';
        stars += `<span style="font-size:12.5px;color:var(--gray-400);margin-left:6px;">${rating}/5</span>`;

        document.getElementById('modalId').textContent = '#' + this.dataset.id;
        document.getElementById('modalName').textContent = this.dataset.name;
        document.getElementById('modalEmail').innerHTML = `<a href="mailto:${this.dataset.email}" style="color:var(--green-500);">${this.dataset.email}</a>`;
        document.getElementById('modalDate').textContent = this.dataset.date;
        document.getElementById('modalRating').innerHTML = stars;
        document.getElementById('modalMessage').textContent = this.dataset.message;
        document.getElementById('modalDeleteBtn').href = `feedback.php?confirm_delete=${this.dataset.id}`;
        modal.classList.add('open');
    });
});
document.getElementById('modalClose').addEventListener('click', () => modal.classList.remove('open'));
document.getElementById('modalClose2').addEventListener('click', () => modal.classList.remove('open'));
modal.addEventListener('click', (e) => { if(e.target === modal) modal.classList.remove('open'); });
</script>

<?php include 'footer.php'; ?>