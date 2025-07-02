<?php
include 'db.php';

$id = $_GET['id'];

// Get the user_id directly from employee_leaves
$leaveQuery = mysqli_query($conn, "SELECT user_id FROM employee_leaves WHERE id=$id");
$leaveData = mysqli_fetch_assoc($leaveQuery);
$user_id = $leaveData['user_id'];

// Update leave status
mysqli_query($conn, "UPDATE employee_leaves SET status='Approved' WHERE id=$id");

// Add notification (if user_id was found)
if ($user_id) {
    $message = "Your leave request has been <strong style='color:green;'>approved</strong>.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin_leaves.php");
?>
