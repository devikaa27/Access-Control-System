<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'boss') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['Aprovado', 'Rejeitado'])) {
    header("Location: admin_leaves.php");
    exit();
}

$query = mysqli_query($conn, "SELECT user_id FROM employee_leaves WHERE id = $id");
$row = mysqli_fetch_assoc($query);
$user_id = $row['user_id'] ?? null;

if ($user_id) {
    $status = $action === 'Aprovado' ? 'Aprovado' : 'Rejeitado';
    $update = mysqli_query($conn, "UPDATE employee_leaves SET status = '$status' WHERE id = $id");

    if ($update) {
        $message = "O seu pedido de ausência foi $status.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: admin_leaves.php");
exit();
