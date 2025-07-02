<?php
include 'db.php';

// Get POST data
$employee_username = $_POST['employee_username'];
$start = $_POST['start_date'];
$end = $_POST['end_date'];
$reason = $_POST['reason'];

// Prepare & execute SQL
$stmt = $conn->prepare("INSERT INTO employee_leave (employee_username, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $employee_username, $start, $end, $reason);

if ($stmt->execute()) {
    echo "Leave request submitted!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
