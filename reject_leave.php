<?php
include 'db.php';

$id = $_GET['id'];

// Obter user_id do pedido de ausência
$leaveQuery = mysqli_query($conn, "SELECT user_id FROM employee_leaves WHERE id = $id");
$leaveData = mysqli_fetch_assoc($leaveQuery);
$user_id = $leaveData['user_id'];

// Atualizar estado da ausência para "Rejeitada"
mysqli_query($conn, "UPDATE employee_leaves SET status = 'Rejeitada' WHERE id = $id");

// Inserir notificação para o funcionário
if ($user_id) {
    $message = "O seu pedido de ausência foi <strong style='color:red;'>rejeitado</strong>.";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Redirecionar de volta para a página de ausências do admin
header("Location: admin_leaves.php");
exit();
?>
