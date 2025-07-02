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
    // Check if user is already on break
    $check_stmt = $conn->prepare("SELECT id FROM breaks WHERE user_id = ? AND break_end IS NULL");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already on a break!']);
        exit();
    }
    
    // Start new break
    $start_stmt = $conn->prepare("INSERT INTO breaks (user_id, break_start) VALUES (?, NOW())");
    $start_stmt->bind_param("i", $user_id);
    
    if ($start_stmt->execute()) {
        // Get username for notification
        $user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $username = $user_result->fetch_assoc()['username'] ?? 'Unknown';
        
        // Notify admin (assuming admin has user_id = 1, or role = 'admin')
        $admin_stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin'");
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        
        while ($admin = $admin_result->fetch_assoc()) {
            $admin_id = $admin['id'];
            $message = "Employee <strong>{$username}</strong> started a break at " . date('H:i:s');
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notif_stmt->bind_param("is", $admin_id, $message);
            $notif_stmt->execute();
        }
        
        echo json_encode(['success' => true, 'message' => '✅ Break started!', 'status' => 'on_break']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to start break']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
