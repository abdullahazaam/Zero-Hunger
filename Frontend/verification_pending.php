<?php
session_start();

// Agar session mein email nahi hai toh register page pe bhej do
if(!isset($_SESSION['temp_registered_email']) && !isset($_GET['email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['temp_registered_email'] ?? $_GET['email'] ?? '';
$name = $_SESSION['temp_registered_name'] ?? 'User';

// Clear temp session after showing
// unset($_SESSION['temp_registered_email']);
// unset($_SESSION['temp_registered_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verify Email - Zero Hunger</title>
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
        .verification-card {
            max-width: 550px;
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
        .verification-header {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
            padding: 2rem;
        }
        .verification-header i {
            font-size: 64px;
            margin-bottom: 1rem;
        }
        .verification-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }
        .verification-body {
            padding: 2.5rem;
        }
        .email-display {
            background: #f0faf4;
            border-radius: 12px;
            padding: 12px 20px;
            margin: 20px 0;
            font-weight: 600;
            color: #2e7d32;
            word-break: break-all;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            border: none;
            border-radius: 40px;
            padding: 12px 28px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(46,125,50,0.3);
            color: white;
        }
        .btn-outline-custom {
            background: transparent;
            border: 2px solid #2e7d32;
            border-radius: 40px;
            padding: 12px 28px;
            font-weight: 600;
            color: #2e7d32;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        .btn-outline-custom:hover {
            background: #2e7d32;
            color: white;
            transform: translateY(-2px);
        }
        .note {
            font-size: 12px;
            color: #6c757d;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e9ecef;
        }
        .alert-custom {
            background: #f8d7da;
            color: #721c24;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="verification-header">
            <i class="fas fa-envelope-open-text"></i>
            <h2>Verify Your Email</h2>
            <p style="margin-top: 10px; opacity: 0.9;">Almost there!</p>
        </div>
        <div class="verification-body">
            <i class="fas fa-paper-plane" style="font-size: 48px; color: #2e7d32; margin-bottom: 16px; display: block;"></i>
            
            <h4 style="margin-bottom: 16px;">We sent you a verification link!</h4>
            
            <p>Please check your email inbox at:</p>
            <div class="email-display">
                <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($email) ?>
            </div>
            
            <p style="margin-bottom: 24px;">Click the verification link in the email to activate your account. You'll be able to login immediately after verification.</p>
            
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="login.php" class="btn-primary-custom">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <a href="resend_verification.php?email=<?= urlencode($email) ?>" class="btn-outline-custom">
                    <i class="fas fa-redo-alt"></i> Resend Email
                </a>
            </div>
            
            <div class="note">
                <i class="fas fa-hourglass-half me-1"></i> 
                Didn't receive the email? Check your spam folder or click "Resend Email".
                <br>
                <small>The verification link expires in 24 hours.</small>
            </div>
        </div>
    </div>
</body>
</html>