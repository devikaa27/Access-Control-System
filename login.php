<?php
session_start();
require_once 'db.php';  // Your mysqli connection $conn

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        die("Erro na preparação: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Plain text password check; replace with password_verify if you hash passwords
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] === 'boss') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: employee_dashboard.php");
                exit;
            }
        } else {
            $error = "Palavra-passe inválida!!";
        }
    } else {
        $error = "Utilizador não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="PT">
<head>
    <meta charset="UTF-8" />
    <title>Cruz Vermelha Login</title>
    <link rel="stylesheet" href="login.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
</head>
<body>

<div class="logo">
    <img src="images/img.png" alt="Cruz Vermelha Portuguesa" />
</div>

<div class="Login">
    <form method="post" action="">
        <h1>Iniciar Sessão</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red; margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="input-box">
            <input type="text" name="username" placeholder="Nome de utilizador" required />
            <i class='bx bxs-user'></i>
        </div>

        <div class="input-box">
            <input type="password" name="password" placeholder="Palavra-passe" required />
            <i class='bx bxs-lock-alt'></i>
        </div>

        <div class="remember-forgot">
            <label><input type="checkbox" />Lembrar-me</label>
            <a href="forgot_password.php">Esqueceu-se da palavra-passe?</a>
        </div>

        <button type="submit" class="btn">Entrar</button>

    </form>
</div>

</body>
</html>
