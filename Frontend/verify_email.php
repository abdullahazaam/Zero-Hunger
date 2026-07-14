<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$message_type = ""; // success or error

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $email = mysqli_real_escape_string($conn, urldecode($_GET['email']));
    
    // Find user with this token and email
    $stmt = $conn->prepare("SELECT user_id, email_verified FROM users WHERE email = ? AND verification_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['email_verified'] == 1) {
            $message = "Your email is already verified! You can now login.";
            $message_type = "info";
        } else {
            // Update email_verified to 1
            $update_stmt = $conn->prepare("UPDATE users SET email_verified = 1, email_verified_at = NOW(), verification_token = NULL WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            
            if ($update_stmt->execute()) {
                $message = "Email verified successfully! Your account is now active. You can now login.";
                $message_type = "success";
                
                // Log activity
                if (function_exists('logActivity')) {
                    logActivity($user['user_id'], 0, "email_verify", "Email verified successfully");
                }
            } else {
                $message = "Failed to verify email. Please try again or contact support.";
                $message_type = "error";
            }
            $update_stmt->close();
        }
    } else {
        $message = "Invalid verification link. The link may have expired or been used already.";
        $message_type = "error";
    }
    $stmt->close();
} else {
    $message = "Invalid verification request. Missing token or email.";
    $message_type = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Email Verification - Zero Hunger</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verify-card {
            max-width: 500px;
            margin: 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .verify-header {
            padding: 2rem;
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
        }
        .verify-header i {
            font-size: 64px;
            margin-bottom: 1rem;
        }
        .verify-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .verify-body {
            padding: 2.5rem;
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
            transition: all 0.3s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(46,125,50,0.3);
            color: white;
        }
        .alert-success-custom {
            background: #d4edda;
            color: #155724;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .alert-error-custom {
            background: #f8d7da;
            color: #721c24;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .alert-info-custom {
            background: #d1ecf1;
            color: #0c5460;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="verify-header">
            <?php if($message_type == "success"): ?>
                <i class="fas fa-check-circle"></i>
                <h2>Verification Successful!</h2>
            <?php elseif($message_type == "error"): ?>
                <i class="fas fa-times-circle"></i>
                <h2>Verification Failed</h2>
            <?php else: ?>
                <i class="fas fa-info-circle"></i>
                <h2>Information</h2>
            <?php endif; ?>
        </div>
        <div class="verify-body">
            <?php if($message_type == "success"): ?>
                <div class="alert-success-custom">
                    <i class="fas fa-envelope-open-text me-2"></i> <?= htmlspecialchars($message) ?>
                </div>
                <a href="login.php" class="btn-custom">
                    <i class="fas fa-sign-in-alt"></i> Login Now
                </a>
            <?php elseif($message_type == "error"): ?>
                <div class="alert-error-custom">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($message) ?>
                </div>
                <a href="register.php" class="btn-custom">
                    <i class="fas fa-user-plus"></i> Register New Account
                </a>
            <?php else: ?>
                <div class="alert-info-custom">
                    <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($message) ?>
                </div>
                <a href="login.php" class="btn-custom">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>