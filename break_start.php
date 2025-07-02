<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Unauthorized");

mysqli_query($conn, "INSERT INTO breaks (user_id, break_start) VALUES ($user_id, NOW())");
echo "✅ Break started!";
?>
