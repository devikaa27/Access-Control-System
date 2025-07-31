<?php
include 'db.php';
session_start();
date_default_timezone_set('Europe/Lisbon');

// boss access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'boss') {
    echo "Acesso negado."; // Changed from "Access denied."
    exit;
}

$shift_time = "09:00:00";
$sql = "SELECT users.username, attendance.check_in, attendance.check_out 
        FROM attendance 
        JOIN users ON users.id = attendance.user_id 
        WHERE DATE(attendance.check_in) = CURDATE()";

$result = mysqli_query($conn, $sql);

$rows = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $check_in = date("H:i:s", strtotime($row['check_in']));
        $check_out = $row['check_out'] ? date("H:i:s", strtotime($row['check_out'])) : "-";

        $status = ($check_in > $shift_time) ? "Atrasado" : "Pontual"; // Changed from "Late" / "On Time"

        if ($status === "Atrasado") { // Updated string match accordingly
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
            'check_out' => $check_out,
            'status' => $status,
            'late_time' => $late_time
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt"> <!-- Changed lang attribute to Portuguese -->
<head>
    <meta charset="UTF-8">
    <title>Relatório de Atrasos</title> <!-- Changed from "Late Attendance Report" -->
    <link rel="stylesheet" href="style_report.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />   
</head>
<body>

    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-button">🏠︎</a>
    </div>

<h2>Relatório de Atrasos - <?= date("Y-m-d"); ?></h2> <!-- Changed from "Late Attendance Report" -->

<div class="table-container">
    <?php if (empty($rows)) : ?>
        <p>Sem registos de entrada hoje.</p> <!-- Changed from "No check-ins today." -->
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Funcionário</th> <!-- Changed from "Employee" -->
                    <th>Entrada</th>     <!-- Changed from "Check-In" -->
                    <th>Saída</th>       <!-- Changed from "Check-Out" -->
                    <th>Estado</th>      <!-- Changed from "Status" -->
                    <th>Duração do Atraso</th> <!-- Changed from "Late Duration" -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= $r['check_in'] ?></td>
                        <td><?= $r['check_out'] ?></td>
                        <td>
                            <span class="badge <?= $r['status'] === 'Atrasado' ? 'badge-late' : 'badge-on' ?>">
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
