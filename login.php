<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php'; // Reuse the database connection

// Handle form submission
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $sql = "SELECT * FROM users WHERE username = ?";
  $stmt = $conn->prepare($sql);
  if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
      if ($password == $row['password']) {
        // ✅ Set necessary session values
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $username;

        if ($username == 'admin') {
          $_SESSION['role'] = 'admin';
          header("Location: admin_dashboard.php");
        } else {
          $_SESSION['role'] = 'employee';
          header("Location: employee_dashboard.php");
        }
        exit();
      } else {
        $error = "Invalid password!";
      }
    } else {
      $error = "User not found!";
    }
  } else {
    $error = "Database error: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="stylelogin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="login-container">
  <form method="post">
    <h2>Login</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <div class="input-box">
      <i class="fa fa-user"></i>
      <input type="text" name="username" placeholder="Username" required>
    </div>

    <div class="input-box">
      <i class="fa fa-lock"></i>
      <input type="password" name="password" placeholder="Password" required>
    </div>

    <div class="options">
      <label><input type="checkbox"> Remember me</label>
      <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <button type="submit">LOGIN</button>
  </form>
</div>

</body>
</html>
