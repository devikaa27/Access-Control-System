<?php 
include 'db.php';
session_start();
date_default_timezone_set('Europe/Lisbon');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'boss') {
    echo "Acesso negado.";
    exit;
}

$view_type = $_GET['view_type'] ?? 'date';
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_user = $_GET['user_id'] ?? 'all';
$shift_time = "09:00:00";

require_once __DIR__ . '/vendor/autoload.php';

// Obter lista de utilizadores
$users_result = mysqli_query($conn, "SELECT id, username FROM users ORDER BY username ASC");
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[$row['id']] = $row['username'];
}

// Query principal
$where_user = ($selected_user !== 'all') ? "AND u.id = '$selected_user'" : "";

if ($view_type === 'month') {
    $sql = "
    SELECT 
        u.username, 
        a.date,
        a.check_in,
        a.check_out,
        b.break_start,
        b.break_end
    FROM 
        users u
    LEFT JOIN 
        attendance a ON u.id = a.user_id AND DATE_FORMAT(a.date, '%Y-%m') = '$selected_month'
    LEFT JOIN 
        breaks b ON u.id = b.user_id AND DATE_FORMAT(b.break_start, '%Y-%m') = '$selected_month'
    WHERE 1=1 $where_user
    ORDER BY 
        u.username ASC, a.date ASC, b.break_start ASC
    ";
} else {
    $sql = "
    SELECT 
        u.username, 
        a.date,
        a.check_in,
        a.check_out,
        b.break_start,
        b.break_end
    FROM 
        users u
    LEFT JOIN 
        attendance a ON u.id = a.user_id AND a.date = '$selected_date'
    LEFT JOIN 
        breaks b ON u.id = b.user_id AND DATE(b.break_start) = '$selected_date'
    WHERE 1=1 $where_user
    ORDER BY 
        u.username ASC, b.break_start ASC
    ";
}

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro SQL: " . mysqli_error($conn));
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $check_in = $row['check_in'] ? date("H:i:s", strtotime($row['check_in'])) : '-';
    $check_out = $row['check_out'] ? date("H:i:s", strtotime($row['check_out'])) : '-';

    $status = '-';
    $late_duration = '-';
    if ($row['check_in']) {
        $status = (strtotime($check_in) > strtotime($shift_time)) ? "Atrasado" : "Pontual";
        if ($status === "Atrasado") {
            $diff = strtotime($check_in) - strtotime($shift_time);
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            $late_duration = "{$hours}h {$minutes}m";
        }
    }

    $worked_duration = '-';
    if ($row['check_in'] && $row['check_out']) {
        $diff = strtotime($row['check_out']) - strtotime($row['check_in']);
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $worked_duration = "{$hours}h {$minutes}m";
    }

    $break_start = $row['break_start'] ? date("H:i:s", strtotime($row['break_start'])) : '-';
    $break_end = $row['break_end'] ? date("H:i:s", strtotime($row['break_end'])) : '-';

    $data[] = [
        'username' => $row['username'],
        'date' => $row['date'],
        'check_in' => $check_in,
        'check_out' => $check_out,
        'status' => $status,
        'late_duration' => $late_duration,
        'worked_duration' => $worked_duration,
        'break_start' => $break_start,
        'break_end' => $break_end
    ];
}

// Exportar PDF
if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    require_once __DIR__ . '/vendor/autoload.php';

    $mpdf = new \Mpdf\Mpdf();

    $mpdf->SetHTMLFooter('
        <div style="text-align: center; font-size: 10px; color: #999;">
            Gerado em ' . date('d/m/Y H:i') . ' | Cruz Vermelha Portuguesa - Sistema de Presenças
        </div>
    ');

$logoPath = 'images/img.png';
$reportTitle = ($view_type === 'month') ? $selected_month : $selected_date;

$html = '
<style>
    
    * {
        font-family: "DejaVu Sans", sans-serif;
    }

    .header-container {
        position: relative;
        margin-bottom: 5px;
        padding-top: 10px;
    }

    .title {
        text-align: center;
        font-size: 26px;
        font-weight: bold;
        color: #333;
        margin: 0;
        padding-bottom: 5px;
    }


    .logo {
        position: absolute;
        top: 0;
        left: 0;
        margin: 0;
        padding: 0;
    }

    .logo img {
        width: 50px !important;
        height: auto !important;
        margin: 0;
        padding: 0;
        display: block;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 12px;
    }

    th, td {
        border: 1px solid #999;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #d2042d;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f8d7da;
    }
</style>

<div class="header-container">
        <div class="logo">
            <img src="' . $logoPath . '" alt="Cruz Vermelha" style="width: 50px; height: auto; display: block; margin: 0; padding: 0;">
        </div>

    <div class="title">
        Relatório de Presenças - ' . htmlspecialchars($reportTitle) . '
    </div>
</div>
';

    $html .= "<table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Data</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Estado</th>
                <th>Duração Atraso</th>
                <th>Duração Trabalho</th>
                <th>Início Pausa</th>
                <th>Fim Pausa</th>
            </tr>
        </thead>
        <tbody>";

    if (empty($data)) {
        $html .= "<tr><td colspan='9' style='text-align:center;'>Nenhum dado encontrado.</td></tr>";
    } else {
        foreach ($data as $row) {
            $html .= "<tr>
                <td>" . htmlspecialchars($row['username']) . "</td>
                <td>" . ($row['date'] ?? '-') . "</td>
                <td>" . $row['check_in'] . "</td>
                <td>" . $row['check_out'] . "</td>
                <td>" . $row['status'] . "</td>
                <td>" . $row['late_duration'] . "</td>
                <td>" . $row['worked_duration'] . "</td>
                <td>" . $row['break_start'] . "</td>
                <td>" . $row['break_end'] . "</td>
            </tr>";
        }
    }

    $html .= "</tbody></table>";
    $mpdf->WriteHTML($html);
    $filename = "Relatorio_Presencas_" . $reportTitle . ".pdf";
    $mpdf->Output($filename, 'D');
    exit;
}

?>

<!-- Main page HTML remains unchanged -->
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Admin - Relatório Completo de Presenças</title>
    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(90deg, #d2042d, #f58cae);
            color: #fff;
            padding: 40px;
        }
        h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        form {
            text-align: center;
            margin-bottom: 30px;
        }
        input[type="date"], input[type="month"], select {
            padding: 8px;
            border-radius: 6px;
            border: none;
            margin-left: 5px;
        }
        button {
            background-color: #fff;
            color: #d2042d;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        button:hover {
            background-color: #f58cae;
            color: white;
        }
        .back-button-container {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .back-button {
            display: inline-block;
            background-color: white;
            color: #d2042d;
            font-size: 24px;
            border-radius: 50%;
            text-decoration: none;
            width: 50px;
            height: 50px;
            text-align: center;
            line-height: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background-color: #f58cae;
            color: white;
        }
        .table-container {
            overflow-x: auto;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #333;
            animation: fadeIn 0.5s ease-in-out;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #d2042d;
            color: white;
        }
        tr:hover {
            background-color: #ffe2ea;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }
        p {
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-button">🏠︎</a>
    </div>
    <h2>Relatório de Presenças</h2>
    <form method="get">
        <label>Ver por: 
            <select name="view_type" onchange="this.form.submit()">
                <option value="date" <?= ($view_type === 'date') ? 'selected' : '' ?>>Data</option>
                <option value="month" <?= ($view_type === 'month') ? 'selected' : '' ?>>Mês</option>
            </select>
        </label>
        <span id="date_input" style="display: <?= ($view_type === 'date') ? 'inline-block' : 'none' ?>;">
            <label>Selecionar Data:
                <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>">
            </label>
        </span>
        <span id="month_input" style="display: <?= ($view_type === 'month') ? 'inline-block' : 'none' ?>;">
            <label>Selecionar Mês:
                <input type="month" name="month" value="<?= htmlspecialchars($selected_month) ?>">
            </label>
        </span>
        <label>Funcionário:
            <select name="user_id">
                <option value="all">Todos</option>
                <?php foreach ($users as $id => $name): ?>
                    <option value="<?= $id ?>" <?= ($selected_user == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Ver</button>
        <button type="submit" name="download" value="pdf">Descarregar PDF</button>
    </form>
    <div class="table-container">
        <?php if (empty($data)) : ?>
            <p>Nenhum dado encontrado para o <?= htmlspecialchars($view_type) ?> selecionado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Estado</th>
                        <th>Duração Atraso</th>
                        <th>Duração Trabalho</th>
                        <th>Início Pausa</th>
                        <th>Fim Pausa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= $row['date'] ?? '-' ?></td>
                            <td><?= $row['check_in'] ?></td>
                            <td><?= $row['check_out'] ?></td>
                            <td><?= $row['status'] ?></td>
                            <td><?= $row['late_duration'] ?></td>
                            <td><?= $row['worked_duration'] ?></td>
                            <td><?= $row['break_start'] ?></td>
                            <td><?= $row['break_end'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script>
        document.querySelector('select[name="view_type"]').addEventListener('change', function(){
            var val = this.value;
            document.getElementById('date_input').style.display = (val === 'date') ? 'inline-block' : 'none';
            document.getElementById('month_input').style.display = (val === 'month') ? 'inline-block' : 'none';
        });
    </script>
</body>
</html>
