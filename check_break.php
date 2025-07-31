<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id FROM breaks WHERE user_id = ? AND break_end IS NULL LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "active";
} else {
    echo "inactive";
}
$stmt->close();
?>
