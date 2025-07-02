<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styledashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>My Dashboard</h2>
    <ul>
        <li><a href="admin_dashboard.php">🏠 Home</a></li>
        <li><a href="profile.php">👤 Profile</a></li>
        <li><a href="settings.php">⚙️ Settings</a></li>
        <li><a href="manage_users.php">👥 Manage Users</a></li>
        <li><a href="admin_leaves.php">📅 Manage Leave Requests</a></li>
        <li><a href="logout.php">🔌 Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <h1>Welcome, Admin!</h1>
    </div>

 <div class="content">
    <h3>Admin Actions</h3>
    <div class="admin-actions">
        <a href="admin_users.php?action=create">➕ Create User</a>
        <a href="admin_users.php?action=update">✏️ Update User</a>
        <a href="admin_users.php?action=delete">🗑️ Delete User</a>
        <a href="admin_leaves.php">📋 View Leave Requests</a>
        <a href="admin_late_report.php">View Late Report</a>
    </div>
</div>
</div>

</body>
</html>
