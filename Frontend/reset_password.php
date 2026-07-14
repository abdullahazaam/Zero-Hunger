<?php
include '../Backend/db.php';
$pageTitle = 'Reset Password';
include 'header.php';

$token = $_GET['token'] ?? '';
$error = "";
$success = "";

if (empty($token)) {
    header('Location: login.php');
    exit();
}

// Verify token
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $error = "Invalid or expired reset link.";
} else {
    $reset = $result->fetch_assoc();
    $user_id = $reset['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        
        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters!";
        } elseif ($password !== $confirm) {
            $error = "Passwords do not match!";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            mysqli_query($conn, "UPDATE users SET password_hash = '$hash' WHERE user_id = $user_id");
            mysqli_query($conn, "UPDATE password_resets SET used = 1 WHERE token = '$token'");
            $success = "Password reset successfully! You can now login.";
            logActivity($user_id, null, "password_reset", "Password changed");
        }
    }
}
?>

<style>
.reset-container { max-width: 500px; margin: 2rem auto; }
.reset-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
.reset-header { background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; padding: 2rem; text-align: center; }
.reset-body { padding: 2rem; }
.btn-reset { background: linear-gradient(135deg, #2e9458, #226e42); border: none; border-radius: 10px; padding: 0.75rem; font-weight: 600; width: 100%; color: white; }
</style>

<div class="reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <h3><i class="fas fa-lock me-2"></i>Reset Password</h3>
            <p>Create a new password</p>
        </div>
        <div class="reset-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <a href="login.php" class="btn-reset text-center d-block"><i class="fas fa-sign-in-alt me-2"></i> Go to Login</a>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                    </div>
                    <button type="submit" class="btn-reset"><i class="fas fa-save me-2"></i> Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>