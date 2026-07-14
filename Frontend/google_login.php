<?php
session_start();
include '../Backend/db.php';
include '../Backend/functions.php';

$is_localhost = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1');

if ($is_localhost) {
    $_SESSION['error'] = "Google Login only works on live server with HTTPS. Please use email/password login for local testing.";
    header("Location: login.php");
    exit();
}

/*
// ========== UNCOMMENT FOR LIVE SERVER ==========

require_once '../vendor/autoload.php';

$client = new Google\Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('https://yourdomain.com/google_login.php');
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
} else {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    
    $oauth = new Google\Service\Oauth2($client);
    $userinfo = $oauth->userinfo->get();
    
    $email = $userinfo->email;
    $name = $userinfo->name;
    $google_id = $userinfo->id;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role_id'] = $user['role_id'];
        
        if ($user['role_id'] == 2) header("Location: donor_dashboard.php");
        elseif ($user['role_id'] == 3) header("Location: ngo_dashboard.php");
        else header("Location: rider_dashboard.php");
        exit();
    } else {
        $random_password = bin2hex(random_bytes(16));
        $password_hash = password_hash($random_password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role_id, email_verified, is_active) VALUES (?, ?, ?, 2, 1, 1)");
        $stmt->bind_param("sss", $name, $email, $password_hash);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        
        $_SESSION['user_id'] = $new_id;
        $_SESSION['full_name'] = $name;
        $_SESSION['role_id'] = 2;
        
        header("Location: donor_dashboard.php");
        exit();
    }
}

// ========== END OF LIVE SERVER CODE ==========
*/
?>