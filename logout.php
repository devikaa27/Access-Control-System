 <?php
session_start();
date_default_timezone_set('Europe/Lisbon');


include 'db.php'; // Make sure you include your DB connection here

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Update checkout time for today's attendance where check_out is null
    $sql = "UPDATE attendance SET check_out = NOW() WHERE user_id = ? AND DATE(check_in) = CURDATE() AND check_out IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

// Destroy all session variables and session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
