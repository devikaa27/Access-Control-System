<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$user_id = $_SESSION['user_id'];

// Fetch notifications
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");

$error_msg = '';
if (!$notif_query) {
    $error_msg = "Erro ao carregar notificações: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Notificações</title>
    <link rel="stylesheet" href="styledasboard2.css">
</head>
<body>

<div class="sidebar">
    <h2>O Meu Painel</h2>
    <ul>
        <li><a href="employee_dashboard.php">Início</a></li>
        <li><a href="profile.html">Perfil</a></li>
        <li><a href="settings.html">Configurações</a></li>
        <li><a href="leave_form.php">Pedido de Ausência</a></li>
        <li><a href="all_notifications.php" class="active">Notificações</a></li>
        <li><a href="logout.php">Sair</a></li>
    </ul>
</div>

<div class="main">
    <div class="topbar">
        <h1>🔔 Notificações</h1>
    </div>

    <div class="content">
        <?php if (!empty($error_msg)): ?>
            <p style="color:red;"><?php echo $error_msg; ?></p>
        <?php elseif (mysqli_num_rows($notif_query) > 0): ?>
            <ul>
                <?php 
                // Portuguese date formatter: "29 de julho de 2025 às 15:49"
                $formatter = new IntlDateFormatter(
                    'pt_PT',
                    IntlDateFormatter::LONG,
                    IntlDateFormatter::SHORT,
                    'Europe/Lisbon',
                    IntlDateFormatter::GREGORIAN,
                    "d 'de' MMMM 'de' y 'às' HH:mm"
                );

                while ($notif = mysqli_fetch_assoc($notif_query)) {
                    $icon = "ℹ️";
                    $color = "#333";

                    // Handle message type (Aprovado / Rejeitado) in Portuguese
                    if (stripos($notif['message'], 'aprovado') !== false || stripos($notif['message'], 'approved') !== false) {
                        $icon = "✅";
                        $color = "green";
                    } elseif (stripos($notif['message'], 'rejeitado') !== false || stripos($notif['message'], 'rejected') !== false) {
                        $icon = "❌";
                        $color = "red";
                    }
                ?>
                    <li style="margin-bottom: 15px;">
                        <span style="color: <?php echo $color; ?>; font-size: 1.2em;"><?php echo $icon; ?></span>
                        <?php echo htmlspecialchars($notif['message']); ?><br>
                        <small style="color: #666;">
                            <?php
                                $date = new DateTime($notif['created_at']);
                                echo $formatter->format($date); // Ex: "29 de julho de 2025 às 15:49"
                            ?>
                        </small>
                    </li>
                <?php } ?>
            </ul>

            <form method="POST" action="mark_notifications.php">
                <button type="submit">Marcar todas como lidas</button>
            </form>
        <?php else: ?>
            <p>Sem novas notificações.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
