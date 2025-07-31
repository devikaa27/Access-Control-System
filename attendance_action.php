<?php
session_start();
date_default_timezone_set('Europe/Lisbon');

include 'db.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// Check In
if (isset($_POST['check_in'])) {
    $check = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO attendance (user_id, date, check_in) VALUES ($user_id, '$today', '$now')");
    }
    header("Location: employee_dashboard.php");
    exit();
}

// Start Break
if (isset($_POST['start_break'])) {
    $existing = mysqli_query($conn, "SELECT * FROM breaks WHERE user_id = $user_id AND date = '$today' AND break_end IS NULL");
    if (mysqli_num_rows($existing) == 0) {
        mysqli_query($conn, "INSERT INTO breaks (user_id, date, break_start) VALUES ($user_id, '$today', '$now')");
    }
    header("Location: employee_dashboard.php");
    exit();
}

// End Break
if (isset($_POST['end_break'])) {
    $ongoing = mysqli_query($conn, "SELECT * FROM breaks WHERE user_id = $user_id AND date = '$today' AND break_end IS NULL");
    if ($ongoing_row = mysqli_fetch_assoc($ongoing)) {
        $break_id = $ongoing_row['id'];
        mysqli_query($conn, "UPDATE breaks SET break_end = '$now' WHERE id = $break_id");
    }
    header("Location: employee_dashboard.php");
    exit();
}

// Check Out
if (isset($_POST['check_out'])) {
    mysqli_query($conn, "UPDATE attendance SET check_out = '$now' WHERE user_id = $user_id AND date = '$today'");
    header("Location: employee_dashboard.php");
    exit();
}
?>
