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
    if ($conn->connect_error) die("Erro de ligação: " . $conn->connect_error);

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
            $mail->Username = 'cruzvermelhaevoro@gmail.com';
            $mail->Password = 'fonz wyrr qmgw rgnv';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('shreyasoni07pt@gmail.com', 'Cruz Vermelha - Controlo de Acesso');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Pedido de redefinicao de palavra-passe';
            $mail->Body = "Clique para redefinir a sua palavra-passe: <a href='$link'>$link</a>";

            $mail->send();
            $msg = "Link de redefinição enviado para o seu e-mail.";
        } catch (Exception $e) {
            $msg = "Falha ao enviar o e-mail: " . $mail->ErrorInfo;
        }
    } else {
        $msg = "E-mail não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Palavra-Passe</title>
    <style>
        body {
            background: #ffffff;
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
        }

        .box {
            width: 350px;
            margin: 100px auto;
            padding: 25px;
            background: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            border-radius: 6px;
            text-align: center;
            border: 1px solid #cc0000;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 22px;
            color: #cc0000;
        }

        p {
            margin-bottom: 20px;
            color: #800000;
            font-size: 14px;
        }

        input[type="email"] {
            display: block;
            margin: 0 auto 15px auto;
            width: 80%;
            padding: 10px;
            border: 2px solid #cc0000;
            border-radius: 4px;
            font-size: 14px;
            color: #000;
            background: #fff;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #cc0000;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            background: #a00000;
        }

        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Esqueceu-se da Palavra-Passe</h2>
        <p>Introduza o seu e-mail para receber um link de redefinição.</p>
        <?php if ($msg): ?><p class="error"><?= $msg ?></p><?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="O seu e-mail" required>
            <button type="submit">Enviar Link de Redefinição</button>
        </form>
    </div>
</body>
</html>
