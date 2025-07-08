<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if an active break exists
$check = mysqli_query($conn, "SELECT * FROM breaks WHERE user_id = $user_id AND break_end IS NULL LIMIT 1");

if (mysqli_num_rows($check) > 0) {
    echo "Break already started. Please end your current break first.";
    exit();
}

// Insert break start
$now = date('Y-m-d H:i:s');
$sql = "INSERT INTO breaks (user_id, break_start) VALUES ($user_id, '$now')";
if (mysqli_query($conn, $sql)) {
    echo "Break started at " . date('h:i A', strtotime($now));
} else {
    echo "Failed to start break.";
}
?>
