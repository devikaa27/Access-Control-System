<?php
session_start();
date_default_timezone_set('Europe/Lisbon');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Employee';
$today = date('Y-m-d');

// Fetch notifications
$notif_query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id AND is_read = 0 ORDER BY created_at DESC");
$notif_count = mysqli_num_rows($notif_query);

// Fetch attendance
$att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
$att_data = mysqli_fetch_assoc($att_today);

// Auto Check-In
if (!$att_data || empty($att_data['check_in'])) {
    $now = date('Y-m-d H:i:s');
    if ($att_data) {
        mysqli_query($conn, "UPDATE attendance SET check_in = '$now' WHERE user_id = $user_id AND date = '$today'");
    } else {
        mysqli_query($conn, "INSERT INTO attendance (user_id, date, check_in) VALUES ($user_id, '$today', '$now')");
    }
    $att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
    $att_data = mysqli_fetch_assoc($att_today);
}

// Auto Check-Out if after 6 PM
if (empty($att_data['check_out']) && date('H:i') >= '18:00') {
    $now = date('Y-m-d H:i:s');
    mysqli_query($conn, "UPDATE attendance SET check_out = '$now' WHERE user_id = $user_id AND date = '$today'");
    $att_today = mysqli_query($conn, "SELECT * FROM attendance WHERE user_id = $user_id AND date = '$today'");
    $att_data = mysqli_fetch_assoc($att_today);
}

// Check if user currently has an active break
$break_check = mysqli_query($conn, "SELECT * FROM breaks WHERE user_id = $user_id AND break_end IS NULL ORDER BY break_start DESC LIMIT 1");
$active_break = mysqli_fetch_assoc($break_check);
$on_break = $active_break ? true : false;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Employee Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styledasboard2.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />    
</head>
<body>
<aside class="sidebar">
    <h2>Cruz Vermelha</h2>
    <nav>
        <ul>
            <li><a href="employee_dashboard.php" class="active"><i class='bx bx-home'></i> Início</a></li>
            <li><a href="profile.php"><i class='bx bx-user'></i> Perfil</a></li>
            <li><a href="leave_form.php"><i class='bx bx-calendar-check'></i> Formulário de Ausência</a></li>
            <li><a href="logout.php" id="logout-link"><i class='bx bx-log-out'></i> Sair</a></li>
        </ul>
    </nav>
</aside>

<main class="main">
    <div class="header-bg">
        <header class="topbar">
            <h1>Bem-vindo! <span class="user-name"><?php echo htmlspecialchars($username); ?></span></h1>
        </header>

        <!-- Stat Cards -->
        <section class="stats">
            <div class="stat-card">
                <div class="icon" style="background-color:#2dce89;">✔️</div>
                <div class="text">
                    <small>Entrada</small>
                    <strong><?php echo isset($att_data['check_in']) ? date('H:i', strtotime($att_data['check_in'])) : '--'; ?></strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background-color:#f5365c;">❌</div>
                <div class="text">
                    <small>Saída</small>
                    <strong><?php echo isset($att_data['check_out']) ? date('H:i', strtotime($att_data['check_out'])) : '--'; ?></strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="icon" style="background-color:#11cdef;">📅</div>
                <div class="text">
                    <small>Data</small>
                    <strong><?php echo $today; ?></strong>
                </div>
            </div>

            <div class="stat-card notification-trigger" style="cursor:pointer;">
                <div class="icon" style="background-color:#8965e0;">🔔</div>
                <div class="text">
                    <small>Notificações</small>
                    <strong><?php echo $notif_count; ?> Novas</strong>
                </div>
            </div>
        </section>
    </div>

    <!-- Break Section -->
    <section class="break-section" style="margin-top: 30px;"> 
        <h3>🕒 Intervalo</h3>

        <button id="start-break-btn" 
            style="padding:10px 20px; background:#2dce89; color:white; border:none; border-radius:5px;" 
            <?php echo $on_break ? 'disabled' : ''; ?>>
            Iniciar Intervalo
        </button>

        <button id="end-break-btn" 
            style="padding:10px 20px; background:#f5365c; color:white; border:none; border-radius:5px;" 
            <?php echo $on_break ? '' : 'disabled'; ?>>
            Terminar Intervalo
        </button>

        <div id="break-status" style="margin-top:10px; font-weight:bold;">
            <?php 
                echo $on_break 
                    ? 'Está em intervalo desde ' . date('H:i', strtotime($active_break['break_start'])) 
                    : 'Não está em intervalo neste momento';
            ?>
        </div>
    </section>

    <!-- Notification Panel -->
    <div class="card notification-panel" style="display: none;">
        <span class="notif-icon">🔔</span>
        <h4>Notificações</h4>
        <ul>
            <?php if ($notif_count > 0): ?>
                <?php
                $formatter = new IntlDateFormatter(
                    'pt_PT',
                    IntlDateFormatter::LONG,
                    IntlDateFormatter::SHORT,
                    'Europe/Lisbon',
                    IntlDateFormatter::GREGORIAN,
                    "d 'de' MMMM 'de' y 'às' HH:mm"
                );

                mysqli_data_seek($notif_query, 0);
                while ($notif = mysqli_fetch_assoc($notif_query)) { 
                    $date = new DateTime($notif['created_at']);
                ?>
                    <li>
                        <?php echo htmlspecialchars($notif['message']); ?><br>
                        <small><?php echo $formatter->format($date); ?></small>
                    </li>
                <?php } ?>
            <?php else: ?>
                <li>Sem novas notificações</li>
            <?php endif; ?>
        </ul>
        <form method="POST" action="mark_notifications.php">
            <button class="mark-read" type="submit">Marcar todas como lidas</button>
        </form>
    </div>
</main>

<script>
    document.querySelector('.notification-trigger').addEventListener('click', function () {
        const panel = document.querySelector('.notification-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });

    const startBtn = document.getElementById("start-break-btn");
    const endBtn = document.getElementById("end-break-btn");
    const statusDiv = document.getElementById("break-status");

    startBtn.addEventListener("click", function () {
        fetch("break_start.php", {
            method: "POST",
            credentials: "include"
        })
        .then(res => res.text())
        .then(text => {
            statusDiv.innerText = text;
            if (text.includes("Intervalo iniciada")) {
                startBtn.disabled = true;
                endBtn.disabled = false;
            }
        });
    });

    endBtn.addEventListener("click", function () {
        fetch("break_end.php", {
            method: "POST",
            credentials: "include"
        })
        .then(res => res.text())
        .then(text => {
            statusDiv.innerText = text;
            if (text.includes("Intervalo terminada")) {
                startBtn.disabled = false;
                endBtn.disabled = true;
            }
        });
    });

    // On page load: check if break is active, sync buttons & status
    fetch("check_break.php", {
        method: "POST",
        credentials: "include"
    })
    .then(res => res.text())
    .then(text => {
        if (text === "active") {
            startBtn.disabled = true;
            endBtn.disabled = false;
            statusDiv.innerText = 'Está em intervalo neste momento';
        } else {
            startBtn.disabled = false;
            endBtn.disabled = true;
            statusDiv.innerText = 'Não está em intervalo neste momento';
        }
    });

    document.getElementById('logout-link').addEventListener('click', function (e) {
        e.preventDefault();
        if (confirm("Tem a certeza que quer sair?")) {
            window.location.href = "logout.php";
        }
    });
</script>
</body>
</html>
