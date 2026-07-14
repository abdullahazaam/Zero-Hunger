<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$message_type = "";

if (isset($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, urldecode($_GET['email']));
    
    // Find user
    $stmt = $conn->prepare("SELECT user_id, full_name, email_verified, verification_token FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['email_verified'] == 1) {
            $message = "Your email is already verified! You can login directly.";
            $message_type = "info";
        } else {
            // Generate new token
            $new_token = bin2hex(random_bytes(32));
            
            // Update token
            $update_stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $new_token, $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Send new verification email
            $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_email.php?token=" . $new_token . "&email=" . urlencode($email);
            
            $email_subject = "Verify Your Email - Zero Hunger (Resend)";
            $email_message = "
            <html>
            <head>
                <style>
                    body{font-family:Arial,sans-serif;}
                    .container{max-width:600px;margin:0 auto;padding:20px;background:#f5f5f5;}
                    .header{background:#2e7d32;color:white;padding:20px;text-align:center;}
                    .content{background:white;padding:30px;border-radius:10px;}
                    .btn{display:inline-block;background:#2e7d32;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin:20px 0;}
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Zero Hunger Network</h2>
                    </div>
                    <div class='content'>
                        <h3>Welcome, {$user['full_name']}!</h3>
                        <p>Please verify your email address to activate your account.</p>
                        <a href='$verification_link' class='btn'>Verify Email Address</a>
                        <p>Or copy and paste this link:</p>
                        <p style='word-break:break-all;font-size:12px;color:#666;'>$verification_link</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Zero Hunger <noreply@zerohunger.com>" . "\r\n";
            
            $mail_sent = mail($email, $email_subject, $email_message, $headers);
            
            if ($mail_sent) {
                $message = "Verification email has been resent to $email. Please check your inbox.";
                $message_type = "success";
            } else {
                $message = "Failed to send email. Please try again later.";
                $message_type = "error";
            }
        }
    } else {
        $message = "No account found with this email address.";
        $message_type = "error";
    }
    $stmt->close();
} else {
    $message = "No email address provided.";
    $message_type = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Resend Verification - Zero Hunger</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-custom {
            max-width: 500px;
            margin: 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
            text-align: center;
            padding: 2rem;
        }
        .btn-custom {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            border: none;
            border-radius: 40px;
            padding: 12px 32px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-custom:hover { transform: translateY(-2px); color: white; }
        .alert-success { background: #d4edda; color: #155724; border-radius: 12px; padding: 16px; }
        .alert-error { background: #f8d7da; color: #721c24; border-radius: 12px; padding: 16px; }
        .alert-info { background: #d1ecf1; color: #0c5460; border-radius: 12px; padding: 16px; }
    </style>
</head>
<body>
    <div class="card-custom">
        <?php if($message_type == "success"): ?>
            <i class="fas fa-paper-plane" style="font-size: 64px; color: #2e7d32; margin-bottom: 20px;"></i>
            <div class="alert-success"><?= htmlspecialchars($message) ?></div>
            <a href="login.php" class="btn-custom"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
        <?php elseif($message_type == "error"): ?>
            <i class="fas fa-exclamation-triangle" style="font-size: 64px; color: #dc3545; margin-bottom: 20px;"></i>
            <div class="alert-error"><?= htmlspecialchars($message) ?></div>
            <a href="register.php" class="btn-custom"><i class="fas fa-user-plus"></i> Register</a>
        <?php else: ?>
            <i class="fas fa-info-circle" style="font-size: 64px; color: #17a2b8; margin-bottom: 20px;"></i>
            <div class="alert-info"><?= htmlspecialchars($message) ?></div>
            <a href="login.php" class="btn-custom"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>