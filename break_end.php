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

// Find active break
$stmt = $conn->prepare("SELECT id, break_start FROM breaks WHERE user_id = ? AND break_end IS NULL LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Não existe nenhuma pausa ativa para terminar.";
    exit();
}

$break = $result->fetch_assoc();
$break_id = $break['id'];
$break_start = $break['break_start'];
$now = date('Y-m-d H:i:s');

$start_time = new DateTime($break_start);
$end_time = new DateTime($now);
$diff = $start_time->diff($end_time);

// Calculate duration in minutes (for storing in DB)
$hours = $diff->h + ($diff->d * 24); // Account for multi-day breaks
$minutes = $diff->i;
$duration_minutes = ($hours * 60) + $minutes;

// Generate human-readable duration text
if ($hours > 0 && $minutes > 0) {
    $duration_text = "$hours hora" . ($hours > 1 ? "s" : "") . " e $minutes minuto" . ($minutes > 1 ? "s" : "");
} elseif ($hours > 0) {
    $duration_text = "$hours hora" . ($hours > 1 ? "s" : "");
} else {
    $duration_text = "$minutes minuto" . ($minutes > 1 ? "s" : "");
}

$stmt->close();

// Update break record
$stmt = $conn->prepare("UPDATE breaks SET break_end = ?, duration = ? WHERE id = ?");
$stmt->bind_param("sii", $now, $duration_minutes, $break_id);

if ($stmt->execute()) {
    echo "Intervalo terminado às " . date('H:i', strtotime($now)) . ". Duração: $duration_text.";
} else {
    error_log("Erro ao terminar intervalo: " . $stmt->error);
    echo "Erro ao terminar a intervalo. Tente novamente.";
}

$stmt->close();
?>
