<?php
include 'db.php';

$id = $_GET['id'];

// Get user_id from leave request
$leaveQuery = mysqli_query($conn, "SELECT user_id FROM employee_leaves WHERE id = $id");
$leaveData = mysqli_fetch_assoc($leaveQuery);
$user_id = $leaveData['user_id'];

// Update leave status to "Rejected"
mysqli_query($conn, "UPDATE employee_leaves SET status = 'Rejected' WHERE id = $id");

// Insert notification for employee
if ($user_id) {
    $message = "Your leave request has been <strong style='color:red;'>rejected</strong>.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to admin leaves page
header("Location: admin_leaves.php");
exit();
?>
