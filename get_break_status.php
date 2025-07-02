<?php
include 'db.php';
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Check if user is currently on break
    $stmt = $conn->prepare("
        SELECT id, break_start, 
               TIMESTAMPDIFF(MINUTE, break_start, NOW()) as current_duration
        FROM breaks 
        WHERE user_id = ? AND break_end IS NULL 
        ORDER BY break_start DESC LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $break_data = $result->fetch_assoc();
        $start_time = date('H:i:s', strtotime($break_data['break_start']));
        $duration = $break_data['current_duration'];
        
        echo json_encode([
            'success' => true,
            'on_break' => true,
            'message' => "On break since {$start_time} ({$duration} minutes)",
            'start_time' => $start_time,
            'duration' => $duration
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'on_break' => false,
            'message' => "Available for work"
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>