<?php
include 'db.php';
session_start();

// Admin access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit;
}

$shift_time = "09:00:00";
$sql = "SELECT users.username, attendance.check_in 
        FROM attendance 
        JOIN users ON users.id = attendance.user_id 
        WHERE DATE(attendance.check_in) = CURDATE()";

$result = mysqli_query($conn, $sql);

$rows = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $check_in = date("H:i:s", strtotime($row['check_in']));
        $status = ($check_in > $shift_time) ? "Late" : "On Time";

        if ($status === "Late") {
            $diff = strtotime($check_in) - strtotime($shift_time);
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $late_time = "{$hours}h {$minutes}m";
        } else {
            $late_time = "-";
        }

        $rows[] = [
            'name' => $row['username'],
            'check_in' => $check_in,
            'status' => $status,
            'late_time' => $late_time
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Late Attendance Report</title>
    <link rel="stylesheet" href="stylereport.css">
</head>
<body>

<h2>📋 Late Attendance Report - <?= date("Y-m-d"); ?></h2>

<div class="table-container">
    <?php if (empty($rows)) : ?>
        <p>No check-ins today.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Check-In</th>
                    <th>Status</th>
                    <th>Late Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $r['name'] ?></td>
                        <td><?= $r['check_in'] ?></td>
                        <td>
                            <span class="badge <?= $r['status'] === 'Late' ? 'badge-late' : 'badge-on' ?>">
                                <?= $r['status'] ?>
                            </span>
                        </td>
                        <td><?= $r['late_time'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
