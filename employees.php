<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'boss') {
    echo "Acesso negado.";
    exit;
}

$sql = "SELECT id, username, Firstname, Lastname, email FROM users ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8" />
  <title>Todos os Funcionários</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styleprofile.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cinzel&display=swap" rel="stylesheet">
</head>
<body>

<div class="back-button-container">
    <a href="admin_dashboard.php" class="back-button">🏠︎</a>
</div>

<div class="employees-container">
  <h1>Todos os Funcionários</h1>

  <?php if ($result && $result->num_rows > 0): ?>
    <table class="employees-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome de Utilizador</th>
          <th>Nome</th>
          <th>Apelido</th>
          <th>Email</th>
          <th>Ação</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['Firstname']); ?></td>
            <td><?php echo htmlspecialchars($row['Lastname']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td>
              <a href="profile.php?id=<?php echo urlencode($row['id']); ?>" class="edit-btn">Editar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="no-data">Nenhum funcionário encontrado.</p>
  <?php endif; ?>

  <a href="profile.php" class="back-btn">← Voltar ao Meu Perfil</a>
</div>

</body>
</html>
