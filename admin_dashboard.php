<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styledashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>My Dashboard</h2>
        <ul>
            <li><a href="admin_dashboard.php" class="active">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="admin_users.php">Manage Users</a></li>
            <li><a href="admin_leaves.php">Manage Leave Requests</a></li>
            <li><a href="admin_breaks.php">Employees on Break</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
        </div>

        <div class="card-container">
            <a href="admin_users.php?action=create" class="card">➕ Create User</a>
            <a href="admin_users.php?action=update" class="card">✏️ Update User</a>
            <a href="admin_users.php?action=delete" class="card">🗑️ Delete User</a>
            <a href="admin_leaves.php" class="card">📋 View Leave Requests</a>
            <a href="admin_late_report.php" class="card">📊 View Late Report</a>
        </div>
    </div>
</body>
</html>
