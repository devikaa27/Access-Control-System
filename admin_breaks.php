<?php
session_start();
require_once 'db.php';

// Set timezone to Portugal
date_default_timezone_set('Europe/Lisbon');

// IntlDateFormatter to show day/date in Portuguese
$fmt = new IntlDateFormatter('pt_PT', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Lisbon', IntlDateFormatter::GREGORIAN, "EEEE, d MMMM y");

// Fetch all breaks (intervalos) for all time, excluding boss
$sql = "
    SELECT b.break_start, b.break_end, u.username
    FROM breaks b
    JOIN users u ON b.user_id = u.id
    WHERE u.username != 'boss'
    ORDER BY b.break_start DESC
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Erro na consulta à base de dados: " . mysqli_error($conn));
}

// Group intervalos by date
$breaks_by_date = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = $fmt->format(strtotime($row['break_start']));
    $breaks_by_date[$date][] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Todos os Intervalos dos Funcionários</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />  
    <style>
        body {
            margin: 0;
            padding: 30px;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(90deg, #d2042d, #f58cae);
            color: #fff;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
            animation: fadeIn 1s ease-in-out;
        }

        .main {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease-in-out;
            background: white;
            color: #333;
        }

        .date-title {
            font-size: 20px;
            color: #d2042d;
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        th {
            background: linear-gradient(to right, #d2042d, #f58cae);
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        td {
            padding: 12px;
            color: #333;
            background: #fff;
        }

        tr:hover td {
            background-color: #f1f3f6;
        }

        tr:not(:last-child) td {
            border-bottom: 1px solid #eee;
        }

        .on-break {
            color: red;
            font-weight: bold;
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        p {
            text-align: center;
            color: #333;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-button">🏠︎</a>
    </div>

    <div class="main">
        <h1>Funcionários em Intervalo</h1>

        <?php if (!empty($breaks_by_date)): ?>
            <?php foreach ($breaks_by_date as $date => $entries): ?>
                <div class="date-title"><?php echo ucfirst($date); ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>Nome de Utilizador</th>
                            <th>Início do Intervalo</th>
                            <th>Fim do Intervalo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('H:i', strtotime($row['break_start'])); ?></td>
                                <td>
                                    <?php
                                    if ($row['break_end']) {
                                        echo date('H:i', strtotime($row['break_end']));
                                    } else {
                                        echo '<span class="on-break">Em intervalo</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum intervalo registado.</p>
        <?php endif; ?>
    </div>

</body>
</html>
