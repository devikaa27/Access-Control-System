<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");

$error_msg = '';
if (!$notif_query) {
    $error_msg = "Error loading notifications: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="styledasboard2.css">
</head>
<body>

<div class="sidebar">
    <h2>My Dashboard</h2>
    <ul>
        <li><a href="employee_dashboard.php">Home</a></li>
        <li><a href="profile.html">Profile</a></li>
        <li><a href="settings.html">Settings</a></li>
        <li><a href="leave_form.php">Leave Form</a></li>
        <li><a href="all_notifications.php" class="active">Notifications</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main">
    <div class="topbar">
        <h1>Notifications</h1>
    </div>

    <div class="content">
        <?php if (!empty($error_msg)): ?>
            <p style="color:red;"><?php echo $error_msg; ?></p>
        <?php elseif (mysqli_num_rows($notif_query) > 0): ?>
            <ul>
                <?php while ($notif = mysqli_fetch_assoc($notif_query)) {
                    $icon = "ℹ️";
                    $color = "#333";
                    if (stripos($notif['message'], 'approved') !== false) {
                        $icon = "✅";
                        $color = "green";
                    } elseif (stripos($notif['message'], 'rejected') !== false) {
                        $icon = "❌";
                        $color = "red";
                    }
                ?>
                    <li>
                        <span style="color: <?php echo $color; ?>;"><?php echo $icon; ?></span>
                        <?php echo $notif['message']; ?>
                        <small><?php echo date('d M Y, H:i', strtotime($notif['created_at'])); ?></small>
                    </li>
                <?php } ?>
            </ul>
            <form method="POST" action="mark_notifications.php">
                <button type="submit">Mark all as read</button>
            </form>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
