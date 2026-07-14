<?php
include '../Backend/db.php';
include '../Backend/config.php';
$pageTitle = 'Forgot Password';
include 'header.php';

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT user_id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt2 = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $user['user_id'], $token, $expires);
        $stmt2->execute();
        
        $reset_link = SITE_URL . "reset_password.php?token=" . $token;
        $subject = "Password Reset Request - Zero Hunger";
        $body = "
        <html>
        <head><style>
            body{font-family:Arial;color:#333;}
            .container{max-width:500px;margin:auto;padding:20px;border:1px solid #ddd;border-radius:10px;}
            .btn{background:#2e9458;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;}
        </style></head>
        <body>
            <div class='container'>
                <h2 style='color:#2e9458;'>Reset Your Password</h2>
                <p>Hello {$user['full_name']},</p>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                <p><a href='{$reset_link}' class='btn'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <hr>
                <p style='font-size:12px;color:#888;'>Zero Hunger Network</p>
            </div>
        </body>
        </html>";
        
        if (sendEmail($email, $subject, $body)) {
            $message = "Password reset link has been sent to your email.";
            logActivity($user['user_id'], null, "forgot_password", "Password reset requested");
        } else {
            $error = "Failed to send email. Please try again.";
        }
    } else {
        $message = "If your email exists in our system, you will receive a reset link.";
    }
}
?>

<style>
.forgot-container { max-width: 500px; margin: 2rem auto; }
.forgot-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
.forgot-header { background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; padding: 2rem; text-align: center; }
.forgot-body { padding: 2rem; }
.btn-reset { background: linear-gradient(135deg, #2e9458, #226e42); border: none; border-radius: 10px; padding: 0.75rem; font-weight: 600; width: 100%; color: white; }
</style>

<div class="forgot-container">
    <div class="forgot-card">
        <div class="forgot-header">
            <h3><i class="fas fa-key me-2"></i>Forgot Password</h3>
            <p>Enter your email to reset password</p>
        </div>
        <div class="forgot-body">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn-reset"><i class="fas fa-paper-plane me-2"></i> Send Reset Link</button>
            </form>
            <hr>
            <p class="text-center mb-0"><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>