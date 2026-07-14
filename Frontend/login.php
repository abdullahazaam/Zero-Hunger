<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";
$remember_email = "";

// Check for remember me cookie
if (isset($_COOKIE['remember_email'])) {
    $remember_email = $_COOKIE['remember_email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // LOCALHOST FIX: Email verification check commented out
        // if ($user['email_verified'] == 0) {
        //     $error = "Please verify your email first. <a href='resend_verification.php?email=" . urlencode($email) . "'>Resend verification link?</a>";
        // }
        if ($user['is_active'] == 0) {
            $error = "Your account has been deactivated!";
        }
        elseif (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['user_role'] = $user['role_id'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            
            // Remember Me - set cookie for 30 days
            if ($remember_me) {
                setcookie('remember_email', $email, time() + (86400 * 30), "/");
            } else {
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }
            
            // Log activity
            if (function_exists('logActivity')) {
                logActivity($user['user_id'], $user['role_id'], "login", "User logged in");
            }
            
            // Redirect based on role
            if ($user['role_id'] == 2) {
                header('Location: donor_dashboard.php');
                exit();
            } 
            elseif ($user['role_id'] == 3) {
                header('Location: ngo_dashboard.php');
                exit();
            } 
            elseif ($user['role_id'] == 4) {
                $col_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'enrollment_completed'");
                $has_column = mysqli_num_rows($col_check) > 0;
                if ($has_column && isset($user['enrollment_completed']) && $user['enrollment_completed'] == 0) {
                    header('Location: rider_enrollment.php');
                } else {
                    header('Location: rider_dashboard.php');
                }
                exit();
            } 
            else {
                header('Location: index.php');
                exit();
            }
        } 
        else {
            $error = "Invalid password!";
        }
    } 
    else {
        $error = "No account found with this email!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Zero Hunger</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
        }
        .login-container { max-width: 450px; margin: 0 auto; padding: 2rem 1rem; }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-header h3 { margin: 0; font-size: 1.8rem; }
        .login-body { padding: 2rem; }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #2e7d32;
            box-shadow: 0 0 0 0.2rem rgba(46,125,50,0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            color: white;
            transition: transform 0.2s;
        }
        .btn-login:hover { background: linear-gradient(135deg, #1b5e20, #0d3b0f); transform: translateY(-2px); }
        .alert { border-radius: 10px; }
        .alert a { color: #2e7d32; font-weight: bold; }
        .remember-me { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
        .remember-me input { width: 18px; height: 18px; cursor: pointer; }
        .remember-me label { margin: 0; cursor: pointer; font-size: 13px; color: #6c757d; }
        .forgot-link { text-align: right; margin-top: 5px; }
        .forgot-link a { font-size: 12px; color: #2e7d32; text-decoration: none; }
        .forgot-link a:hover { text-decoration: underline; }
        .social-btn { display: flex; justify-content: center; gap: 15px; margin-top: 15px; }
        .btn-google { background: #db4437; color: white; border: none; border-radius: 40px; padding: 8px 20px; font-weight: 500; }
        .btn-google:hover { background: #c53929; color: white; }
        .btn-facebook { background: #4267B2; color: white; border: none; border-radius: 40px; padding: 8px 20px; font-weight: 500; }
        .btn-facebook:hover { background: #365899; color: white; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h3><i class="fas fa-utensils me-2"></i>Zero Hunger</h3>
                <p>Welcome back! Please login to your account</p>
            </div>
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($remember_email) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <div class="remember-me">
                        <input type="checkbox" name="remember_me" id="remember_me" <?= $remember_email ? 'checked' : '' ?>>
                        <label for="remember_me">Remember Me</label>
                    </div>
                    <div class="forgot-link">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-login mt-3" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i> Sign In
                    </button>
                </form>
                
                <hr>
                
                <!-- Social Login Buttons -->
                <div class="social-btn">
                    <a href="google_login.php" class="btn-google btn-sm">
                        <i class="fab fa-google me-1"></i> Google
                    </a>
                    <a href="facebook_login.php" class="btn-facebook btn-sm">
                        <i class="fab fa-facebook-f me-1"></i> Facebook
                    </a>
                </div>
                
                <hr>
                <p class="text-center mb-0">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
   
    <script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('loginBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Logging in...';
        btn.disabled = true;
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>