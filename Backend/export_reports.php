<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin can access
if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? 0) != 1)) {
    header('Location: admin_login.php');
    exit();
}

$type = $_GET['type'] ?? 'donations';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $type . '_report_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

if ($type == 'donations') {
    fputcsv($output, ['ID', 'Donor', 'Food Item', 'Quantity', 'Price', 'Status', 'Created At']);
    $query = mysqli_query($conn, "SELECT fd.*, u.full_name as donor FROM food_donations fd JOIN users u ON fd.donor_id = u.user_id ORDER BY fd.donation_id DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, [$row['donation_id'], $row['donor'], $row['food_item'], $row['quantity'], $row['price'], $row['status'], $row['created_at']]);
    }
} elseif ($type == 'users') {
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Registered']);
    $query = mysqli_query($conn, "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id ORDER BY u.user_id DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, [$row['user_id'], $row['full_name'], $row['email'], $row['phone'], $row['role_name'], $row['is_active'] ? 'Active' : 'Inactive', $row['created_at']]);
    }
} elseif ($type == 'deliveries') {
    fputcsv($output, ['Request ID', 'Food Item', 'NGO', 'Rider', 'Status', 'Base Fare', 'Created At']);
    $query = mysqli_query($conn, "SELECT r.*, fd.food_item, u_ngo.full_name as ngo, u_rider.full_name as rider FROM requests r JOIN food_donations fd ON r.donation_id = fd.donation_id JOIN users u_ngo ON r.receiver_id = u_ngo.user_id LEFT JOIN users u_rider ON r.rider_id = u_rider.user_id ORDER BY r.request_id DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, [$row['request_id'], $row['food_item'], $row['ngo'], $row['rider'] ?? 'Not Assigned', $row['delivery_status'], $row['base_fare'], $row['created_at']]);
    }
}

fclose($output);
exit();
?>