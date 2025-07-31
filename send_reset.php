<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer from local folder
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';


// Database connection
$host = "127.0.0.1";
$port = 3307;
$user = "root";
$password = "";
$dbname = "access_control";

$conn = new mysqli($host, $user, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Falha na ligação: " . $conn->connect_error);
}

// Get user email
$email = $_POST['email'] ?? '';

// Stop if email is empty
if (empty($email)) {
    die("O email é obrigatório.");
}

// Generate token and expiry
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save token to DB
$sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $token, $expires);
$stmt->execute();

// Create reset link
$reset_link = "http://localhost/reset_password.php?token=$token";

// Send email via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'shreyasoni07pt@gmail.com';         // Your Gmail
    $mail->Password   = 'zscqyzclcnbjbaqb';                 // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('shreyasoni07pt@gmail.com', 'Sistema de Controlo de Acesso');
    $mail->addAddress($email);

    $mail->isHTML(false);
    $mail->Subject = 'Recuperar a sua palavra-passe - Sistema de Controlo de Acesso';
    $mail->Body    = "Olá,\n\nClique no link abaixo para recuperar a sua palavra-passe:\n\n$reset_link\n\nEste link expirará dentro de 1 hora.";

    $mail->send();

    echo "Link para recuperar a palavra-passe enviado para o seu email.";
} catch (Exception $e) {
    echo "O email não pôde ser enviado. Erro: {$mail->ErrorInfo}";
}
?>
