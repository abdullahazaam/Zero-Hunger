<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "zero_hunger"; 

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Karachi');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// EMAIL FUNCTION (FREE - Using mail() or SMTP)
// ============================================
function sendEmail($to, $subject, $body, $isHTML = true) {
    global $conn;
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Zero Hunger <noreply@zerohunger.com>" . "\r\n";
    
    $result = @mail($to, $subject, $body, $headers);
    
    // Log email
    $user_id = $_SESSION['user_id'] ?? null;
    $status = $result ? 'sent' : 'failed';
    mysqli_query($conn, "INSERT INTO email_logs (user_id, email, subject, status) VALUES ($user_id, '$to', '$subject', '$status')");
    
    return $result;
}

// ============================================
// SEND VERIFICATION EMAIL
// ============================================
function sendVerificationEmail($user_id, $email, $name) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    global $conn;
    mysqli_query($conn, "INSERT INTO email_verifications (user_id, token, expires_at) VALUES ($user_id, '$token', '$expires')");
    
    $verify_link = SITE_URL . "verify_email.php?token=" . $token;
    
    $subject = "Verify Your Email - Zero Hunger";
    $body = "
    <html>
    <head><style>
        body{font-family:Arial;color:#333;}
        .container{max-width:500px;margin:auto;padding:20px;border:1px solid #ddd;border-radius:10px;}
        .header{background:#2e9458;color:white;padding:15px;text-align:center;border-radius:10px 10px 0 0;}
        .btn{background:#2e9458;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;}
        .footer{font-size:12px;color:#888;text-align:center;margin-top:20px;}
    </style></head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Zero Hunger</h2>
            </div>
            <div style='padding:20px;text-align:center;'>
                <h3>Welcome, $name!</h3>
                <p>Thank you for joining Zero Hunger. Please verify your email address to get started.</p>
                <p><a href='$verify_link' class='btn'>Verify Email Address</a></p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't create an account, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>Zero Hunger Network - Connecting surplus food with those in need</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($email, $subject, $body);
}

// ============================================
// SEND DELIVERY NOTIFICATION
// ============================================
function sendDeliveryNotification($user_id, $user_email, $user_name, $food_item, $status, $rider_name = null) {
    if ($status == 'assigned') {
        $subject = "New Delivery Assigned - Zero Hunger";
        $body = "
        <html>
        <body>
            <h3>Hello $user_name!</h3>
            <p>A new delivery has been assigned to you.</p>
            <p><strong>Food Item:</strong> $food_item</p>
            <p><strong>Status:</strong> Ready for pickup</p>
            <p>Please check your dashboard for more details.</p>
            <p><a href='" . SITE_URL . "rider_dashboard.php'>Go to Dashboard</a></p>
        </body>
        </html>";
    } elseif ($status == 'completed') {
        $subject = "Delivery Completed - Zero Hunger";
        $body = "
        <html>
        <body>
            <h3>Hello $user_name!</h3>
            <p>Your delivery has been successfully completed!</p>
            <p><strong>Food Item:</strong> $food_item</p>
            <p><strong>Delivered by:</strong> $rider_name</p>
            <p>Please rate your rider experience.</p>
            <p><a href='" . SITE_URL . "ngo_dashboard.php'>Rate Your Rider</a></p>
        </body>
        </html>";
    }
    
    return sendEmail($user_email, $subject, $body);
}

// ============================================
// SEND MESSAGE NOTIFICATION
// ============================================
function sendMessageNotification($to_email, $to_name, $from_name) {
    $subject = "New Message - Zero Hunger";
    $body = "
    <html>
    <body>
        <h3>Hello $to_name!</h3>
        <p>You have received a new message from <strong>$from_name</strong>.</p>
        <p>Click below to read and reply.</p>
        <p><a href='" . SITE_URL . "messages.php'>Go to Messages</a></p>
    </body>
    </html>";
    
    return sendEmail($to_email, $subject, $body);
}

// ============================================
// LOG ACTIVITY
// ============================================
function logActivity($user_id, $user_role, $action, $details = null) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_role, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $user_id, $user_role, $action, $details, $ip, $agent);
    $stmt->execute();
    $stmt->close();
}

// ============================================
// HELPER FUNCTIONS
// ============================================
function getPagination($page, $limit, $total) {
    $total_pages = ceil($total / $limit);
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $limit;
    return ['page' => $page, 'limit' => $limit, 'offset' => $offset, 'total_pages' => $total_pages, 'total' => $total];
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}
?>