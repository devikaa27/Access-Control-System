<?php
session_start();
include("db.php");

// Processar ações do formulário (Criar, Atualizar, Eliminar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email    = $_POST['email'];

    if (isset($_POST['create'])) {
        $query = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['update'])) {
        $query = "UPDATE users SET password='$password', email='$email' WHERE username='$username'";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['delete'])) {
        $query = "DELETE FROM users WHERE username='$username'";
        mysqli_query($conn, $query);
    }
}

// Buscar utilizadores para exibição
$result = mysqli_query($conn, "SELECT username, email FROM users");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gerir Utilizadores</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />    
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(90deg, #d2042d, #f58cae);
            margin: 0;
            padding: 0;
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

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.25);
            text-align: center;
        }

        h1 {
            color: #d2042d;
            margin-bottom: 30px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 80%;
            padding: 10px;
            margin: 8px 0;
            border: 2px solid #f58cae;
            border-radius: 10px;
            font-size: 16px;
        }

        .btn-group {
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }

        button[name="create"] {
            background-color: #d2042d;
            color: white;
        }

        button[name="update"] {
            background-color: #f58cae;
            color: white;
        }

        button[name="delete"] {
            background-color: #222;
            color: white;
        }

        button:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
            font-size: 16px;
            border-radius: 15px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: center;
        }

        th {
            background: linear-gradient(90deg, #d2042d, #f58cae);
            color: white;
            font-size: 18px;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background-color: #fdf1f4;
        }

        tr:hover {
            background-color: #ffe6ef;
        }
    </style>
</head>
<body>

    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-button">🏠︎</a>
    </div>

    <div class="container">
        <h1>Gerir Utilizadores</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Nome de utilizador" required><br>
            <input type="password" name="password" placeholder="Palavra-passe (para criar/atualizar)"><br>
            <input type="email" name="email" placeholder="Email (para criar/atualizar)"><br>
            <div class="btn-group">
                <button type="submit" name="create">Criar Utilizador</button>
                <button type="submit" name="update">Atualizar Utilizador</button>
                <button type="submit" name="delete">Eliminar Utilizador</button>
            </div>
        </form>
    </div>

    <div class="container">
        <h1>Lista de Utilizadores</h1>
        <table>
            <tr>
                <th>Nome de Utilizador</th>
                <th>Email</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
