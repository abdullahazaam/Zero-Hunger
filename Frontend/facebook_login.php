<?php
session_start();
include '../Backend/db.php';
include '../Backend/functions.php';

// Note: Facebook Login works only on LIVE server with HTTPS
// For localhost, it will show error message

$error = "";

// Check if running on localhost
$is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');

if ($is_localhost) {
    $_SESSION['error'] = "Facebook Login only works on live server with HTTPS. Please use email/password login for local testing.";
    header("Location: login.php");
    exit();
}

/*
// ========== UNCOMMENT BELOW CODE FOR LIVE SERVER ==========

// Step 1: Install Facebook SDK via Composer
// composer require facebook/graph-sdk

require_once '../vendor/autoload.php';

$fb = new Facebook\Facebook([
    'app_id' => 'YOUR_FACEBOOK_APP_ID',           // Replace with your App ID
    'app_secret' => 'YOUR_FACEBOOK_APP_SECRET',   // Replace with your App Secret
    'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();

// Facebook Login Callback URL
$redirect_url = 'https://yourdomain.com/facebook_login.php';

if (!isset($_GET['code'])) {
    // Step 1: Redirect to Facebook for login
    $permissions = ['email', 'public_profile'];
    $login_url = $helper->getLoginUrl($redirect_url, $permissions);
    header('Location: ' . $login_url);
    exit();
} else {
    try {
        // Step 2: Get access token from Facebook
        $access_token = $helper->getAccessToken();
        
        if (!isset($access_token)) {
            throw new Exception('Access token not received');
        }
        
        // Step 3: Get user info from Facebook
        $response = $fb->get('/me?fields=id,name,email,picture', $access_token);
        $fb_user = $response->getGraphUser();
        
        $email = $fb_user->getEmail();
        $name = $fb_user->getName();
        $facebook_id = $fb_user->getId();
        
        if (empty($email)) {
            throw new Exception('Email not available from Facebook. Please use different login method.');
        }
        
        // Step 4: Check if user exists in database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User exists - login
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id'] = $user['role_id'];
            
            // Update last login
            $update = $conn->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE user_id = ?");
            $update->bind_param("si", $_SERVER['REMOTE_ADDR'], $user['user_id']);
            $update->execute();
            
            logActivity($user['user_id'], $user['role_id'], "facebook_login", "User logged in via Facebook");
            
            // Redirect based on role
            if ($user['role_id'] == 2) header("Location: donor_dashboard.php");
            elseif ($user['role_id'] == 3) header("Location: ngo_dashboard.php");
            else header("Location: rider_dashboard.php");
            exit();
            
        } else {
            // Step 5: User doesn't exist - create new account
            $random_password = bin2hex(random_bytes(16));
            $password_hash = password_hash($random_password, PASSWORD_BCRYPT);
            
            // Default role: Donor (2)
            $role_id = 2;
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role_id, email_verified, is_active) VALUES (?, ?, ?, ?, 1, 1)");
            $stmt->bind_param("sssi", $name, $email, $password_hash, $role_id);
            
            if ($stmt->execute()) {
                $new_user_id = $stmt->insert_id;
                
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['full_name'] = $name;
                $_SESSION['role_id'] = $role_id;
                
                // Save Facebook ID in social_logins table
                $social_stmt = $conn->prepare("INSERT INTO social_logins (user_id, provider, provider_user_id) VALUES (?, 'facebook', ?)");
                $social_stmt->bind_param("is", $new_user_id, $facebook_id);
                $social_stmt->execute();
                
                logActivity($new_user_id, $role_id, "facebook_register", "New user registered via Facebook");
                
                header("Location: donor_dashboard.php");
                exit();
            } else {
                throw new Exception("Failed to create account");
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Facebook Login Failed: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}

// ========== END OF LIVE SERVER CODE ==========
*/

?>