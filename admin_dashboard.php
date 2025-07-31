<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'boss') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="styledashboard.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Cruz Vermelha</h2>
        <ul>
            <li><a href="admin_dashboard.php" class="active"><i class='bx bx-home'></i> Início</a></li>
            <li><a href="profile.php"><i class='bx bx-user'></i> Perfil</a></li>
            <li><a href="admin_users.php"><i class='bx bx-group'></i> Gerir Utilizadores</a></li>
            <li><a href="admin_leaves.php"><i class='bx bx-calendar-check'></i> Pedido de Ausência</a></li>
            <li><a href="admin_breaks.php"><i class='bx bx-coffee'></i> Funcionários em Intervalo</a></li>
            <li><a href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
        </ul>
    </div>


    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div class="top-text">
                <h1>Bem-vindo! <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
            </div>
        </div>

        <div class="card-container">
            <a href="admin_users.php?action=create" class="card">➕ Criar Utilizador</a>
            <a href="admin_users.php?action=update" class="card">✏️ Atualizar Utilizador</a>
            <a href="admin_users.php?action=delete" class="card">🗑️ Eliminar Utilizador</a>
            <a href="admin_leaves.php" class="card">📋 Pedido de Ausência</a>
            <a href="admin_late_report.php" class="card">📊 Ver Relatório de Atrasos</a>
            <a href="admin_full_report.php" class="card">📋 Relatório Completo de Presenças</a>
        </div>
    </div>
</body>
</html>
