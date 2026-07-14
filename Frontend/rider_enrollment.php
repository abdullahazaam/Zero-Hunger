<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a rider
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 4) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Check if already enrolled
$check = mysqli_query($conn, "SELECT enrollment_completed FROM users WHERE user_id = $user_id");
$user_data = mysqli_fetch_assoc($check);
if ($user_data && $user_data['enrollment_completed'] == 1) {
    header('Location: rider_dashboard.php');
    exit();
}

// Handle enrollment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $bike_number = mysqli_real_escape_string($conn, trim($_POST['bike_number']));
    $cnic_number = mysqli_real_escape_string($conn, trim($_POST['cnic_number']));
    
    // File upload handling
    $profile_pic = '';
    $cnic_pic = '';
    
    $upload_dir = '../uploads/rider_documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Upload Profile Picture
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $profile_pic = $upload_dir . 'profile_' . $user_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
    }
    
    // Upload CNIC Picture
    if (isset($_FILES['cnic_pic']) && $_FILES['cnic_pic']['error'] == 0) {
        $ext = pathinfo($_FILES['cnic_pic']['name'], PATHINFO_EXTENSION);
        $cnic_pic = $upload_dir . 'cnic_' . $user_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['cnic_pic']['tmp_name'], $cnic_pic);
    }
    
    // Update user information
    $stmt = $conn->prepare("UPDATE users SET phone = ?, address = ?, bike_number = ?, cnic = ?, profile_pic = ?, cnic_pic = ?, enrollment_completed = 1, enrollment_submitted_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $phone, $address, $bike_number, $cnic_number, $profile_pic, $cnic_pic, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['enrollment_completed'] = 1;
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Enrollment Submitted!',
                text: 'Your enrollment request has been submitted. Admin will verify your details.',
                timer: 3000,
                showConfirmButton: true
            }).then(() => {
                window.location.href = 'rider_dashboard.php';
            });
        </script>";
        exit();
    } else {
        $error = "Failed to submit enrollment. Please try again.";
    }
    $stmt->close();
}

$pageTitle = 'Rider Enrollment - Zero Hunger';
include 'header.php';
?>

<style>
:root {
    --green-50: #f0faf4;
    --green-100: #d6f0e0;
    --green-500: #2e9458;
    --green-600: #226e42;
    --gray-50: #f8f9fa;
    --gray-100: #f1f3f5;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #adb5bd;
    --gray-500: #6c757d;
    --gray-700: #343a40;
    --gray-900: #212529;
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --shadow-card: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06);
}

.dh-page { background: var(--gray-100); min-height: 100vh; padding: 2rem 0 3rem; }
.dh-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    max-width: 700px;
    margin: 0 auto;
}
.dh-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    background: var(--gray-50);
}
.dh-header-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    background: var(--green-100);
    color: var(--green-600);
}
.dh-card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--gray-900);
}
.dh-card-header p {
    margin: 0;
    font-size: 12px;
    color: var(--gray-500);
}
.dh-card-body {
    padding: 1.5rem;
}
.dh-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 5px;
}
.dh-label .req {
    color: #dc3545;
    margin-left: 2px;
}
.dh-input {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    color: var(--gray-900);
    background: #fff;
    border: 1.5px solid var(--gray-300);
    border-radius: var(--radius-sm);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
}
.dh-input:focus {
    border-color: var(--green-400);
    box-shadow: 0 0 0 3px rgba(46,148,88,.18);
}
.dh-textarea {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    color: var(--gray-900);
    background: #fff;
    border: 1.5px solid var(--gray-300);
    border-radius: var(--radius-sm);
    outline: none;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}
.dh-textarea:focus {
    border-color: var(--green-400);
    box-shadow: 0 0 0 3px rgba(46,148,88,.18);
}
.file-upload {
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-sm);
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--gray-50);
}
.file-upload:hover {
    border-color: var(--green-500);
    background: var(--green-50);
}
.file-upload i {
    font-size: 2rem;
    color: var(--gray-400);
    margin-bottom: 0.5rem;
}
.file-name {
    font-size: 12px;
    color: var(--gray-500);
    margin-top: 0.5rem;
}
.dh-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    color: #fff;
    background: var(--green-500);
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: background .15s;
}
.dh-btn:hover {
    background: var(--green-600);
}
.dh-alert {
    padding: 12px 15px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dh-alert.danger {
    background: #fff0f0;
    border: 1px solid #ffc9c9;
    color: #c0392b;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}
@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}
</style>

<div class="dh-page">
<div class="container">
    <div class="dh-card">
        <div class="dh-card-header">
            <div class="dh-header-icon">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div>
                <h5>Complete Your Rider Profile</h5>
                <p>Please provide your details to start delivering</p>
            </div>
        </div>
        <div class="dh-card-body">
            
            <?php if (!empty($error)): ?>
            <div class="dh-alert danger">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div>
                        <label class="dh-label">Contact Number <span class="req">*</span></label>
                        <input type="tel" name="phone" class="dh-input" placeholder="03XXXXXXXXX" required>
                    </div>
                    <div>
                        <label class="dh-label">Bike Number <span class="req">*</span></label>
                        <input type="text" name="bike_number" class="dh-input" placeholder="e.g., ABC-123" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="dh-label">CNIC Number <span class="req">*</span></label>
                        <input type="text" name="cnic_number" class="dh-input" placeholder="42101-1234567-8" required>
                    </div>
                    <div>
                        <label class="dh-label">Profile Picture <span class="req">*</span></label>
                        <div class="file-upload" onclick="document.getElementById('profile_pic').click()">
                            <i class="fas fa-camera"></i>
                            <div>Click to upload your photo</div>
                            <div class="file-name" id="profile_pic_name">No file chosen</div>
                        </div>
                        <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label class="dh-label">CNIC Picture <span class="req">*</span></label>
                        <div class="file-upload" onclick="document.getElementById('cnic_pic').click()">
                            <i class="fas fa-id-card"></i>
                            <div>Upload CNIC (Front & Back combined or separate)</div>
                            <div class="file-name" id="cnic_pic_name">No file chosen</div>
                        </div>
                        <input type="file" name="cnic_pic" id="cnic_pic" class="d-none" accept="image/*" required>
                    </div>
                    <div>
                        <label class="dh-label">Full Address <span class="req">*</span></label>
                        <textarea name="address" class="dh-textarea" placeholder="Your complete address for delivery assignments" required></textarea>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="dh-btn">
                        <i class="fas fa-paper-plane"></i> Submit Enrollment
                    </button>
                    <p class="text-muted small text-center mt-3">
                        <i class="fas fa-shield-alt me-1"></i> Your information will be verified by admin within 24-48 hours.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
document.getElementById('profile_pic').addEventListener('change', function(e) {
    document.getElementById('profile_pic_name').textContent = e.target.files[0]?.name || 'No file chosen';
});
document.getElementById('cnic_pic').addEventListener('change', function(e) {
    document.getElementById('cnic_pic_name').textContent = e.target.files[0]?.name || 'No file chosen';
});
</script>

<?php include 'footer.php'; ?>