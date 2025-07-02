<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Get today's breaks
$today = date('Y-m-d');
$breaks_query = "
    SELECT b.*, u.username, 
           DATE_FORMAT(b.break_start, '%H:%i:%s') as start_time,
           DATE_FORMAT(b.break_end, '%H:%i:%s') as end_time,
           CASE 
               WHEN b.break_end IS NULL THEN TIMESTAMPDIFF(MINUTE, b.break_start, NOW())
               ELSE b.duration 
           END as calculated_duration,
           CASE WHEN b.break_end IS NULL THEN 'On Break' ELSE 'Completed' END as status
    FROM breaks b 
    JOIN users u ON b.user_id = u.id 
    WHERE DATE(b.break_start) = '$today'
    ORDER BY b.break_start DESC
";
$breaks_result = mysqli_query($conn, $breaks_query);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_breaks,
        COUNT(CASE WHEN break_end IS NULL THEN 1 END) as active_breaks,
        AVG(CASE WHEN break_end IS NOT NULL THEN duration END) as avg_duration
    FROM breaks 
    WHERE DATE(break_start) = '$today'
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Break Management - Admin</title>
    <link rel="stylesheet" href="styledashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .break-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }
        .breaks-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .breaks-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .breaks-table th,
        .breaks-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .breaks-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-active {
            background: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .refresh-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .refresh-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
        <li><a href="admin_dashboard.php">🏠 Home</a></li>
        <li><a href="profile.php">👤 Profile</a></li>
        <li><a href="settings.php">⚙️ Settings</a></li>
        <li><a href="manage_users.php">👥 Manage Users</a></li>
        <li><a href="admin_leaves.php">📅 Manage Leave Requests</a></li>
        <li><a href="admin_breaks.php" style="background: #007bff;">🕒 Break Management</a></li>
        <li><a href="logout.php">🔌 Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main">
    <div class="topbar">
        <h1>Break Management</h1>
        <button class="refresh-btn" onclick="refreshData()">🔄 Refresh</button>
    </div>

    <div class="content">
        <!-- Statistics -->
        <div class="break-stats">
            <div class="stat-box">
                <h3><?php echo $stats['total_breaks'] ?? 0; ?></h3>
                <p>Total Breaks Today</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $stats['active_breaks'] ?? 0; ?></h3>
                <p>Currently on Break</p>
            </div>
            <div class="stat-box">
                <h3><?php echo round($stats['avg_duration'] ?? 0); ?> min</h3>
                <p>Average Break Duration</p>
            </div>
        </div>

        <!-- Breaks Table -->
        <div class="breaks-table">
            <table id="breaks-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration (minutes)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($breaks_result) > 0): ?>
                        <?php while ($break = mysqli_fetch_assoc($breaks_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($break['username']); ?></td>
                                <td><?php echo $break['start_time']; ?></td>
                                <td><?php echo $break['end_time'] ?: '--'; ?></td>
                                <td><?php echo round($break['calculated_duration'] ?? 0); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $break['status'] === 'On Break' ? 'status-active' : 'status-completed'; ?>">
                                        <?php echo $break['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #666;">No breaks recorded today</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

// Auto-refresh every 60 seconds
setInterval(refreshData, 60000);

// Show notification for active breaks
document.addEventListener('DOMContentLoaded', function() {
    const activeBreaks = <?php echo $stats['active_breaks'] ?? 0; ?>;
    if (activeBreaks > 0) {
        setTimeout(() => {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #ffc107;
                color: #212529;
                padding: 15px 20px;
                border-radius: 5px;
                font-weight: bold;
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = `⚠️ ${activeBreaks} employee(s) currently on break`;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.remove(), 5000);
        }, 1000);
    }
});
</script>

</body>
</html>