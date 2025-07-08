<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php'; // Your database connection

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
            if ($password == $row['password']) {  // Plain text check - consider hashing in future
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
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cruz Vermelha</title>
    <link rel="stylesheet" href="login.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
</head>
<body>

    <!-- Logo in top-left corner -->
    <div class="logo">
        <img src="images/img.png" alt="Cruz Vermelha Portuguesa" />
    </div>

    <!-- Login Form -->
    <div class="Login">
        <form method="post" action="">
            <h1>Login</h1>

            <?php if (!empty($error)) {
                echo "<p style='color:red; margin-bottom:15px;'>$error</p>";
            } ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required />
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required />
                <i class='bx bxs-lock-alt'></i>
            </div>

            <div class="remember-forgot">
                <label><input type="checkbox" />Remember me</label>
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" class="btn">Login</button>

            <div class="register-link">
                <p>Don't have an account? <a href="create.php">Create new account</a></p>
            </div>
        </form>
    </div>

</body>
</html>
