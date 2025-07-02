<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Unauthorized");

mysqli_query($conn, "
    UPDATE breaks 
    SET break_end = NOW(), 
        duration = TIMESTAMPDIFF(MINUTE, break_start, NOW()) 
    WHERE user_id = $user_id AND break_end IS NULL 
    ORDER BY break_start DESC LIMIT 1
");
echo "✅ Break ended!";
?>
