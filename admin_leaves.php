<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'boss') {
    header("Location: login.php");
    exit();
}
include 'db.php';

$sql = "SELECT el.*, u.username AS employee_username 
        FROM employee_leaves el 
        JOIN users u ON el.user_id = u.id 
        ORDER BY el.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Pedidos de Férias</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(90deg, #d2042d, #f58cae);
            margin: 0;
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

        .admin-container {
            padding: 20px;
        }

        .card {
            width: 95%;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(210, 4, 45, 0.2);
            overflow-x: auto;
        }

        h2 {
            color: white;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(90deg, #d2042d, #f58cae);
            color: white;
            padding: 16px;
            text-align: left;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: bold;
        }

        thead tr:first-child th:first-child {
            border-top-left-radius: 10px;
        }

        thead tr:first-child th:last-child {
            border-top-right-radius: 10px;
        }

        td {
            padding: 14px;
            font-size: 15px;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background-color: #fff6f6;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tr:hover {
            background-color: #fff0f3;
        }

        .status.approved {
            color: green;
            font-weight: bold;
        }

        .status.rejected {
            color: red;
            font-weight: bold;
        }

        .status.pending {
            color: orange;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 6px 14px;
            margin: 2px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: white;
            transition: background 0.3s ease;
        }

        .btn.approve {
            background-color: #28a745;
        }

        .btn.reject {
            background-color: #dc3545;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .toggle-btn {
            background-color: #d2042d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            margin-top: 6px;
        }

        .toggle-btn:hover {
            background-color: #b00227;
        }

        .readmore-container {
            max-width: 300px;
            position: relative;
        }

        .short-text,
        .full-text {
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .short-text {
            background: #fef2f2;
            display: block;
        }

        .full-text {
            background: #fff4f2;
            display: none;
            position: relative;
            z-index: 1;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-button">🏠︎</a>
    </div>

    <div class="admin-container">
        <h2>Pedidos de Ausência</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Funcionário</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        $reason = nl2br(htmlspecialchars($row['reason']));
                        $short = nl2br(htmlspecialchars(substr($row['reason'], 0, 100)));
                        $row_id = $row['id'];

                        // Translate status to Portuguese
                        $status_raw = $row['status']; // e.g., "Aprovado"
                        $status_map = [
                        'Pendente' => ['class' => 'pending', 'text' => 'Pendente'],
                        'Aprovado' => ['class' => 'approved', 'text' => 'Aprovado'],
                        'Rejeitado' => ['class' => 'rejected', 'text' => 'Rejeitado']
                        ];

                        $status_class = $status_map[$status_raw]['class'] ?? 'pending';
                        $status_text = $status_map[$status_raw]['text'] ?? $status_raw;

                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['employee_username']) ?></td>
                        <td><?= $row['start_date'] ?></td>
                        <td><?= $row['end_date'] ?></td>
                        <td class="reason">
                            <div class="readmore-container" id="reason-<?= $row_id ?>">
                                <div class="short-text"><?= $short ?>...</div>
                                <div class="full-text"><?= $reason ?></div>
                                <button class="toggle-btn" onclick="toggleReason(<?= $row_id ?>)">Ver mais</button>
                            </div>
                        </td>
                        <td><span class="status <?= $status_class ?>"><?= $status_text ?></span></td>
                        <td>
                            <?php if (in_array($status_raw, ['Pending', 'Pendente'])) { ?>
                                <a class="btn approve" href="approve_leave.php?action=Aprovado&id=<?= $row['id'] ?>">Aprovar</a>
                                <a class="btn reject" href="approve_leave.php?action=Rejeitado&id=<?= $row['id'] ?>">Rejeitar</a>
                            <?php } else {
                                echo "Sem ações";
                            } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
function toggleReason(id) {
    const container = document.getElementById('reason-' + id);
    const shortText = container.querySelector('.short-text');
    const fullText = container.querySelector('.full-text');
    const button = container.querySelector('.toggle-btn');

    if (fullText.style.display === 'block') {
        fullText.style.display = 'none';
        shortText.style.display = 'block';
        button.textContent = 'Ver mais';
    } else {
        fullText.style.display = 'block';
        shortText.style.display = 'none';
        button.textContent = 'Ver menos';
    }
}
</script>

</body>
</html>
