<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

// Find active break
$check = mysqli_query($conn, "SELECT * FROM breaks WHERE user_id = $user_id AND break_end IS NULL LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    echo "No active break to end.";
    exit();
}

$break = mysqli_fetch_assoc($check);
$break_id = $break['id'];
$break_start = $break['break_start'];
$now = date('Y-m-d H:i:s');

$start_time = new DateTime($break_start);
$end_time = new DateTime($now);
$duration = $end_time->getTimestamp() - $start_time->getTimestamp(); // in seconds
$duration_min = round($duration / 60);

// Update break_end and duration
$sql = "UPDATE breaks SET break_end = '$now', duration = $duration_min WHERE id = $break_id";
if (mysqli_query($conn, $sql)) {
    echo "Break ended at " . date('h:i A', strtotime($now)) . ". Duration: $duration_min minutes.";
} else {
    echo "Failed to end break.";
}
?>
