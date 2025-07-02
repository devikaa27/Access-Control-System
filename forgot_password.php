<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Connect to DB
    $conn = new mysqli("127.0.0.1", "root", "", "access_control", 3307);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    // Check email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        // Save token
        $save = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $save->bind_param("sss", $email, $token, $expires);
        $save->execute();

        $link = "http://localhost/Access-Control-System/reset_password.php?token=$token";

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shreyasoni07pt@gmail.com';
            $mail->Password = 'zscqyzclcnbjbaqb';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('shreyasoni07pt@gmail.com', 'Cruz Vermelha - Access Control');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click to reset your password: <a href='$link'>$link</a>";

            $mail->send();
            $msg = "Reset link sent to your email.";
        } catch (Exception $e) {
            $msg = "Email sending failed: " . $mail->ErrorInfo;
        }
    } else {
        $msg = "Email not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="reset.css">
</head>
<body>
    <div class="box">
        <h2>Forgot Password</h2>
        <p>Enter your email to get a reset link.</p>
        <?php if ($msg): ?><p class="error"><?= $msg ?></p><?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>
</html>
