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
    $check_stmt = $conn->prepare("SELECT id, break_start FROM breaks WHERE user_id = ? AND break_end IS NULL ORDER BY break_start DESC LIMIT 1");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not currently on a break!']);
        exit();
    }
    
    $break_data = $result->fetch_assoc();
    $break_start_time = $break_data['break_start'];
    
    // End the break
    $end_stmt = $conn->prepare("
        UPDATE breaks 
        SET break_end = NOW(), 
            duration = TIMESTAMPDIFF(MINUTE, break_start, NOW()) 
        WHERE user_id = ? AND break_end IS NULL 
        ORDER BY break_start DESC LIMIT 1
    ");
    $end_stmt->bind_param("i", $user_id);
    
    if ($end_stmt->execute()) {
        // Calculate duration for notification
        $duration_stmt = $conn->prepare("
            SELECT duration, TIMESTAMPDIFF(MINUTE, break_start, break_end) as actual_duration
            FROM breaks 
            WHERE user_id = ? AND break_start = ? 
            LIMIT 1
        ");
        $duration_stmt->bind_param("is", $user_id, $break_start_time);
        $duration_stmt->execute();
        $duration_result = $duration_stmt->get_result();
        $duration_data = $duration_result->fetch_assoc();
        $duration_minutes = $duration_data['actual_duration'] ?? 0;
        
        // Get username for notification
        $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $username = $user_result->fetch_assoc()['username'] ?? 'Unknown';
        
        // Notify admin
        $admin_stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        
        while ($admin = $admin_result->fetch_assoc()) {
            $admin_id = $admin['id'];
            $message = "Employee <strong>{$username}</strong> ended break at " . date('H:i:s') . " (Duration: {$duration_minutes} minutes)";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->bind_param("is", $admin_id, $message);
            $notif_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => "✅ Break ended! Duration: {$duration_minutes} minutes", 'status' => 'available']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to end break']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
