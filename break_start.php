<?php
session_start();
include 'db.php';

date_default_timezone_set('Europe/Lisbon');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Não autorizado";
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if there's already an active break
$stmt = $conn->prepare("SELECT id FROM breaks WHERE user_id = ? AND break_end IS NULL LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Intervalo já iniciada. Por favor termine a intervalo atual primeiro.";
    $stmt->close();
    exit();
}
$stmt->close();

// Start new break
$now = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO breaks (user_id, break_start) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $now);

if ($stmt->execute()) {
    echo "Intervalo iniciada às " . date('H:i', strtotime($now));
} else {
    error_log("Erro ao iniciar intervalo: " . $stmt->error);
    echo "Erro ao iniciar a intervalo. Tente novamente.";
}
$stmt->close();
?>
