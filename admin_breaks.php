<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';  // your DB connection file

// Query breaks where break_end is NULL, exclude admin user by username
$sql = "
    SELECT b.break_start, u.username
    FROM breaks b
    JOIN users u ON b.user_id = u.id
    WHERE b.break_end IS NULL
    AND u.username != 'admin'
    ORDER BY b.break_start DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees on Break</title>
    <link rel="stylesheet" href="styledashboard.css">
</head>
<body>

<div class="sidebar">
    <h2>My Dashboard</h2>
    <ul>
        <li><a href="admin_dashboard.php">🏠 Home</a></li>
        <li><a href="profile.php">👤 Profile</a></li>
        <li><a href="settings.php">⚙️ Settings</a></li>
        <li><a href="manage_users.php">👥 Manage Users</a></li>
        <li><a href="admin_leaves.php">📅 Manage Leave Requests</a></li>
        <li><a href="admin_breaks.php" class="active">⏳ Employees on Break</a></li>
        <li><a href="logout.php">🔌 Logout</a></li>
    </ul>
</div>

<div class="main">
    <div class="topbar">
        <h1>Employees Currently on Break</h1>
    </div>

    <div class="content">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Employee Username</th>
                        <th>Break Start Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($row['break_start'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No employees are currently on break.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
