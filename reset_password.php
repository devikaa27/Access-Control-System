<?php
include 'db.php';

$token = $_GET['token'] ?? '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPass = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($newPass !== $confirm) {
        $msg = "❌ As palavras-passe não coincidem!";
    } else {
        // Check token validity
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $email = $row['email'];

            // Update user's password
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $newPass, $email);
            $update->execute();

            // Remove token
            $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $del->bind_param("s", $token);
            $del->execute();

            $msg = "✅ Palavra-passe redefinida com sucesso! Pode agora <a href='login.php'>iniciar sessão</a>.";
        } else {
            $msg = "❌ Token inválido ou expirado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Palavra-passe</title>
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

        input[type="password"] {
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
        <h2>Redefinir Palavra-passe</h2>
        <?php if ($msg): ?><p class="error"><?= $msg ?></p><?php endif; ?>
        <?php if ($token): ?>
            <form method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="password" name="new_password" placeholder="Nova palavra-passe" required>
                <input type="password" name="confirm_password" placeholder="Confirmar palavra-passe" required>
                <button type="submit">Atualizar Palavra-passe</button>
            </form>
        <?php else: ?>
            <p>Token de redefinição em falta ou inválido.</p>
        <?php endif; ?>
    </div>
</body>
</html>
