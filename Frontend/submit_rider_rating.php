<?php
include '../Backend/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 3) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$request_id = intval($_POST['request_id'] ?? 0);
$rider_id = intval($_POST['rider_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$feedback = mysqli_real_escape_string($conn, trim($_POST['feedback'] ?? ''));
$ngo_id = $_SESSION['user_id'];

if ($request_id == 0 || $rider_id == 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$check = mysqli_query($conn, "SELECT rating_id FROM rider_ratings WHERE request_id = $request_id");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'message' => 'Rating already submitted']);
    exit();
}

$insert = mysqli_query($conn, "INSERT INTO rider_ratings (request_id, rider_id, ngo_id, rating, feedback) VALUES ($request_id, $rider_id, $ngo_id, $rating, '$feedback')");

if ($insert) {
    $avg_query = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM rider_ratings WHERE rider_id = $rider_id");
    $avg_data = mysqli_fetch_assoc($avg_query);
    $new_avg = round($avg_data['avg_rating'], 2);
    $total = $avg_data['total'];
    mysqli_query($conn, "UPDATE users SET avg_rating = $new_avg, total_ratings = $total WHERE user_id = $rider_id");
    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>