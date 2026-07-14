<?php
include '../Backend/db.php';
include '../Backend/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? mysqli_real_escape_string($conn, trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, trim($_POST['address'])) : '';
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, trim($_POST['phone'])) : '';
    $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0; 

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role_id)) {
        $error = "All required fields must be filled!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = mysqli_query($conn, "SELECT email, role_id FROM users WHERE email = '$email'");
        if ($check_email && mysqli_num_rows($check_email) > 0) {
            $existing = mysqli_fetch_assoc($check_email);
            $existing_role = $existing['role_id'];
            $role_names = [1=>'Admin', 2=>'Donor', 3=>'NGO', 4=>'Rider'];
            $error = "This email is already registered as a " . $role_names[$existing_role] . ".";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Generate verification token
            $verification_token = bin2hex(random_bytes(32));
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (role_id, full_name, email, password_hash, phone, address, email_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("issssss", $role_id, $name, $email, $password_hash, $phone, $address, $verification_token);
            
            if ($stmt->execute()) {
                $new_user_id = $stmt->insert_id;
                
                // Send verification email
                $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_email.php?token=" . $verification_token . "&email=" . urlencode($email);
                
                $email_subject = "Verify Your Email - Zero Hunger";
                $email_message = "
                <html>
                <head>
                    <style>
                        body{font-family:Arial,sans-serif;}
                        .container{max-width:600px;margin:0 auto;padding:20px;background:#f5f5f5;}
                        .header{background:#2e7d32;color:white;padding:20px;text-align:center;}
                        .content{background:white;padding:30px;border-radius:10px;}
                        .btn{display:inline-block;background:#2e7d32;color:white;padding:12px 24px;text-decoration:none;border-radius:5px;margin:20px 0;}
                        .footer{font-size:12px;color:#666;text-align:center;margin-top:20px;}
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Zero Hunger Network</h2>
                        </div>
                        <div class='content'>
                            <h3>Welcome, $name!</h3>
                            <p>Thank you for joining Zero Hunger. Please verify your email address to activate your account.</p>
                            <a href='$verification_link' class='btn'>Verify Email Address</a>
                            <p>Or copy and paste this link:</p>
                            <p style='word-break:break-all;font-size:12px;color:#666;'>$verification_link</p>
                            <p>This link will expire in 24 hours.</p>
                        </div>
                        <div class='footer'>
                            <p>&copy; 2026 Zero Hunger Network. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Zero Hunger <noreply@zerohunger.com>" . "\r\n";
                
                $mail_sent = mail($email, $email_subject, $email_message, $headers);
                
                // Log email attempt
                $status = $mail_sent ? 'sent' : 'failed';
                $log_stmt = $conn->prepare("INSERT INTO email_logs (user_id, email, subject, status) VALUES (?, ?, ?, ?)");
                $log_stmt->bind_param("isss", $new_user_id, $email, $email_subject, $status);
                $log_stmt->execute();
                $log_stmt->close();
                
                logActivity($new_user_id, $role_id, "register", "User registered, verification email sent (status: $status)");
                
                // Store email in session for pending page
                $_SESSION['temp_registered_email'] = $email;
                $_SESSION['temp_registered_name'] = $name;
                
                // Redirect to verification pending page
                header("Location: verification_pending.php");
                exit();
            } else {
                $error = "Registration failed! Please try again later.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Zero Hunger</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .register-container { max-width: 550px; margin: 0 auto; padding: 2rem 1rem; }
        .register-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .register-header { background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; padding: 2rem; text-align: center; }
        .register-header h3 { margin: 0; font-size: 1.8rem; }
        .register-body { padding: 2rem; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #2c3e50; margin-bottom: 0.3rem; }
        .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e0e0e0; padding: 0.7rem 1rem; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { border-color: #2e7d32; box-shadow: 0 0 0 0.2rem rgba(46,125,50,0.25); }
        .btn-register { background: linear-gradient(135deg, #2e7d32, #1b5e20); border: none; border-radius: 10px; padding: 0.75rem; font-weight: 600; font-size: 1rem; width: 100%; color: white; transition: transform 0.2s; }
        .btn-register:hover { transform: translateY(-2px); }
        .password-strength { margin-top: 5px; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden; }
        .password-strength-bar { height: 100%; width: 0%; transition: width 0.3s; }
        .strength-text { font-size: 11px; margin-top: 5px; }
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h3><i class="fas fa-utensils me-2"></i>Zero Hunger</h3>
                <p>Join the community and help reduce food waste</p>
            </div>
            <div class="register-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="mb-3">
                        <label class="form-label required">Account Identity Type</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">-- Choose Account Type --</option>
                            <option value="2">🍽️ Food Donor (Restaurant / Hotel / Individual)</option>
                            <option value="3">🏢 NGO / Welfare Organization (Receiver)</option>
                            <option value="4">🏍️ Delivery Rider / Driver</option>
                        </select>
                        <small class="text-muted" style="font-size: 11px; display: block; margin-top: 5px;">
                            <i class="fas fa-info-circle"></i> 
                            Donors and NGOs can start immediately after registration. Riders need enrollment.
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Full Name / Organization Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., John Doe / Save Life NGO" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                            <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="03xxxxxxxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Physical Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Complete physical location details for food dispatch/pickup"></textarea>
                    </div>

                    <button type="submit" class="btn-register" id="registerBtn">
                        <i class="fas fa-user-plus me-2"></i> Register Account
                    </button>
                </form>
                
                <div class="login-link text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Password Strength Meter
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthBar.className = 'password-strength-bar';
            strengthText.textContent = '';
        } else if (strength <= 1) {
            strengthBar.style.width = '25%';
            strengthBar.className = 'password-strength-bar strength-weak';
            strengthText.innerHTML = '<span style="color:#dc3545;">Weak password</span>';
        } else if (strength === 2) {
            strengthBar.style.width = '50%';
            strengthBar.className = 'password-strength-bar strength-fair';
            strengthText.innerHTML = '<span style="color:#ffc107;">Fair password</span>';
        } else if (strength === 3) {
            strengthBar.style.width = '75%';
            strengthBar.className = 'password-strength-bar strength-good';
            strengthText.innerHTML = '<span style="color:#17a2b8;">Good password</span>';
        } else {
            strengthBar.style.width = '100%';
            strengthBar.className = 'password-strength-bar strength-strong';
            strengthText.innerHTML = '<span style="color:#28a745;">Strong password</span>';
        }
    });
    
    document.getElementById('registerForm').addEventListener('submit', function() {
        const btn = document.getElementById('registerBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Registering...';
        btn.disabled = true;
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>