<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$sql = "SELECT el.*, u.username AS employee_username 
        FROM employee_leaves el 
        JOIN users u ON el.user_id = u.id 
        ORDER BY el.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Requests</title>
    <link rel="stylesheet" href="style_admin.css">
</head>
<body>

<div class="admin-container">
    <aside class="sidebar">
        <h2>My Dashboard</h2>
        <ul>
            <li><a href="admin_dashboard.php">🏠 Home</a></li>
            <li><a href="profile.php">👤 Profile</a></li>
            <li><a href="settings.php">⚙️ Settings</a></li>
            <li><a href="manage_users.php">👥 Manage Users</a></li>
            <li><a href="admin_leaves.php" class="active">🗕️ Leave Requests</a></li>
            <li><a href="logout.php">🔌 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Leave Requests</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        $reason = nl2br(htmlspecialchars($row['reason']));
                        $short = nl2br(htmlspecialchars(substr($row['reason'], 0, 100)));
                        $row_id = $row['id'];
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['employee_username']) ?></td>
                        <td><?= $row['start_date'] ?></td>
                        <td><?= $row['end_date'] ?></td>
                        <td class="reason">
                            <div class="readmore-container" id="reason-<?= $row_id ?>">
                                <div class="short-text"><?= $short ?>...</div>
                                <div class="full-text" style="display: none;"><?= $reason ?></div>
                                <button class="toggle-btn" onclick="toggleReason(<?= $row_id ?>)">See More</button>
                            </div>
                        </td>
                        <td><span class="status <?= strtolower($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        <td>
                            <?php if (strtolower($row['status']) === 'pending') { ?>
                                <a class="btn approve" href="approve_leave.php?id=<?= $row['id'] ?>">Approve</a>
                                <a class="btn reject" href="reject_leave.php?id=<?= $row['id'] ?>">Reject</a>
                            <?php } else {
                                echo "No actions";
                            } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
function toggleReason(id) {
    const container = document.getElementById('reason-' + id);
    const shortText = container.querySelector('.short-text');
    const fullText = container.querySelector('.full-text');
    const button = container.querySelector('.toggle-btn');

    const isExpanded = fullText.style.display === 'block';

    if (isExpanded) {
        fullText.style.display = 'none';
        shortText.style.display = 'block';
        button.textContent = 'See More';
    } else {
        fullText.style.display = 'block';
        shortText.style.display = 'none';
        button.textContent = 'See Less';
    }
}
</script>

</body>
</html>
