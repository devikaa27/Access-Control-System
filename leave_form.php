<?php
session_start();

// Make sure the user is logged in and has a user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$showModal = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include 'db.php';

    $user_id = $_SESSION['user_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $reason = $_POST['reason'];

    // Insert the leave request with user_id
    $stmt = $conn->prepare("INSERT INTO employee_leaves (user_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $start, $end, $reason);
        if ($stmt->execute()) {
            $showModal = true;
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Error in SQL: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leave Request Form</title>
  <link rel="stylesheet" href="style_form.css">
</head>
<body>
  <div class="sidebar">
    <ul>
      <li><a href="employee_dashboard.php">Home</a></li>
      <li><a href="profile.html">Profile</a></li>
      <li><a href="settings.html">Settings</a></li>
      <li><a href="leave_form.php">Leave Form</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <h2>Leave Request Form</h2>
    <form method="POST" action="leave_form.php">
      <label for="start_date">Start Date:</label>
      <input type="date" id="start_date" name="start_date" required>

      <label for="end_date">End Date:</label>
      <input type="date" id="end_date" name="end_date" required>

      <label for="reason">Reason:</label>
      <textarea id="reason" name="reason" required></textarea>

      <button type="submit">Request Leave</button>
    </form>
  </div>

  <?php if ($showModal): ?>
  <div class="modal">
    <div class="modal-content">
      <div class="checkmark-circle">
        <div class="checkmark"></div>
      </div>
      <h3>Submitted Successfully</h3>
      <p>Your leave request has been sent.</p>
      <a href="leave_form.php"><button>Done</button></a>
    </div>
  </div>
  <?php endif; ?>
</body>
</html>
