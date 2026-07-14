<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_role = $_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0;

// Only NGO (role_id = 3) can claim food
if ($user_role != 3) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'Only NGOs can claim food donations.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>";
    exit();
}

$donation_id = isset($_GET['donation_id']) ? intval($_GET['donation_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($donation_id == 0) {
    header('Location: index.php');
    exit();
}

// Check if donation exists and is available
$check_donation = mysqli_query($conn, "SELECT * FROM food_donations WHERE donation_id = $donation_id AND status = 'Available'");
if (mysqli_num_rows($check_donation) == 0) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Not Available',
            text: 'This food item is no longer available!',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>";
    exit();
}

$donation = mysqli_fetch_assoc($check_donation);

// Insert into requests table
$insert = mysqli_query($conn, "INSERT INTO requests (donation_id, receiver_id, status, created_at) 
                                VALUES ($donation_id, $user_id, 'Pending', NOW())");

if ($insert) {
    $request_id = mysqli_insert_id($conn);
    // Update donation status to Claimed
    mysqli_query($conn, "UPDATE food_donations SET status = 'Claimed' WHERE donation_id = $donation_id");
    
    // Redirect to delivery setup
    header("Location: delivery_setup.php?donation_id=$donation_id&request_id=$request_id");
    exit();
} else {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to claim food. Please try again.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'index.php';
        });
    </script>";
}
?>