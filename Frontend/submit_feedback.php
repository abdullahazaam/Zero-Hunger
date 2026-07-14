<?php
include '../backend/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? '';
    $user_email = $_SESSION['email'] ?? '';

    // Check database columns
    $res = mysqli_query($conn, "DESCRIBE feedback");
    $db_columns = [];
    while($row = mysqli_fetch_assoc($res)) {
        $db_columns[] = strtolower($row['Field']);
    }

    // Build query dynamically
    $columns_to_insert = [];
    $values_to_insert = [];
    $types = "";
    $params = [];

    if (in_array('user_id', $db_columns)) {
        $columns_to_insert[] = 'user_id';
        $values_to_insert[] = '?';
        $types .= 'i';
        $params[] = $user_id;
    }
    
    $email_col = in_array('email', $db_columns) ? 'email' : (in_array('user_email', $db_columns) ? 'user_email' : null);
    if ($email_col) {
        $columns_to_insert[] = $email_col;
        $values_to_insert[] = '?';
        $types .= 's';
        $params[] = $user_email;
    }

    $msg_col = in_array('message', $db_columns) ? 'message' : (in_array('feedback', $db_columns) ? 'feedback' : 'message');
    $columns_to_insert[] = $msg_col;
    $values_to_insert[] = '?';
    $types .= 's';
    $params[] = $message;

    if (in_array('rating', $db_columns)) {
        $columns_to_insert[] = 'rating';
        $values_to_insert[] = '?';
        $types .= 'i';
        $params[] = $rating;
    }

    $sql = "INSERT INTO feedback (" . implode(", ", $columns_to_insert) . ") VALUES (" . implode(", ", $values_to_insert) . ")";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            // Need to include header for __() function
            include 'header.php';
            echo "<script>alert('" . __('feedback_sent_success') . "'); window.location.href = 'index.php';</script>";
        } else {
            echo "Execution Error: " . $stmt->error;
        }
    } else {
        echo "Query Error: " . $conn->error;
    }
}
?>