<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    echo "Não tem sessão iniciada. Por favor, inicie sessão primeiro.";
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInRole = $_SESSION['role'];
$viewingUserId = $loggedInUserId;

// Os administradores podem ver outros perfis via ?id=
if ($loggedInRole === 'boss' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viewingUserId = (int) $_GET['id'];
} elseif ($loggedInRole !== 'boss' && isset($_GET['id']) && (int)$_GET['id'] !== $loggedInUserId) {
    echo "Acesso negado.";
    exit();
}

// Obter utilizador
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $viewingUserId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Utilizador não encontrado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Perfil</title>
  <link rel="stylesheet" href="styleprofile.css" />
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />
</head>
<body>

<!-- Botão de Voltar -->
<div class="back-button-container">
    <a href="<?php echo ($loggedInRole === 'boss') ? 'admin_dashboard.php' : 'employee_dashboard.php'; ?>" class="back-button">🏠︎</a>
</div>

<div class="profile-container">

  <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <p class="success-message">Perfil atualizado com sucesso!</p>
  <?php endif; ?>

  <?php
  if (isset($_SESSION['edit_errors']) && !empty($_SESSION['edit_errors'])) {
      echo '<div style="color: red; font-weight: bold; margin-bottom: 20px;">';
      foreach ($_SESSION['edit_errors'] as $error) {
          echo htmlspecialchars($error) . '<br>';
      }
      echo '</div>';
      unset($_SESSION['edit_errors']);
  }
  ?>

  <h1 class="profile-header">
    Perfil de 
    <?php 
      echo htmlspecialchars(trim(
        (!empty($user['Firstname']) || !empty($user['Lastname'])) 
          ? $user['Firstname'] . ' ' . $user['Lastname'] 
          : $user['username']
      ));
    ?>
  </h1>

  <div class="profile-details">
    <label>Nome de utilizador:</label>
    <div class="value"><?php echo htmlspecialchars($user['username'] ?? 'N/D'); ?></div>

    <label>Email:</label>
    <div class="value"><?php echo htmlspecialchars($user['email'] ?? 'N/D'); ?></div>

    <label>Nome próprio:</label>
    <div class="value"><?php echo htmlspecialchars($user['Firstname'] ?? 'N/D'); ?></div>

    <label>Apelido:</label>
    <div class="value"><?php echo htmlspecialchars($user['Lastname'] ?? 'N/D'); ?></div>

    <label>Cidade:</label>
    <div class="value"><?php echo htmlspecialchars($user['city'] ?? 'N/D'); ?></div>

    <label>País:</label>
    <div class="value"><?php echo htmlspecialchars($user['country'] ?? 'N/D'); ?></div>

    <label>Função:</label>
    <div class="value"><?php echo htmlspecialchars(ucfirst($user['role'] ?? 'N/D')); ?></div>

    <label>Membro desde:</label>
    <div class="value">
      <?php 
        if (!empty($user['created_at'])) {
            $formatter = new IntlDateFormatter(
                'pt_PT',
                IntlDateFormatter::LONG,
                IntlDateFormatter::NONE,
                'Europe/Lisbon',
                IntlDateFormatter::GREGORIAN,
                "d 'de' MMMM 'de' y"
            );
            echo $formatter->format(new DateTime($user['created_at']));
        } else {
            echo "N/D";
        }
      ?>
    </div>
  </div>

  <!-- Mostrar formulário de edição se for boss ou o próprio -->
  <?php if ($loggedInRole === 'boss' || $loggedInUserId == $user['id']): ?>
    <button id="editProfileBtn" class="edit-btn" aria-expanded="false">Editar perfil</button>

    <form id="editProfileForm" class="edit-profile-form" method="post" action="edit_profile.php" style="display:none;" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>" />

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />

      <label for="Firstname">Nome próprio:</label>
      <input type="text" id="Firstname" name="Firstname" value="<?php echo htmlspecialchars($user['Firstname']); ?>" />

      <label for="Lastname">Apelido:</label>
      <input type="text" id="Lastname" name="Lastname" value="<?php echo htmlspecialchars($user['Lastname']); ?>" />

      <label for="city">Cidade:</label>
      <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" />

      <label for="country">País:</label>
      <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($user['country']); ?>" />

      <div class="form-buttons">
        <button type="submit">Guardar alterações</button>
        <button type="button" id="cancelEdit" class="cancel-btn">Cancelar</button>
      </div>
    </form>
  <?php endif; ?>

  <!-- Link do boss para ver todos os funcionários -->
  <?php if ($loggedInRole === 'boss'): ?>
    <a href="employees.php" class="view-employees-btn">Ver todos os funcionários</a>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const editBtn = document.getElementById('editProfileBtn');
  const editForm = document.getElementById('editProfileForm');
  const cancelBtn = document.getElementById('cancelEdit');

  if (editBtn && editForm && cancelBtn) {
    editBtn.addEventListener('click', () => {
      editForm.style.display = 'flex';
      editBtn.disabled = true;
      editBtn.setAttribute('aria-expanded', 'true');
      editForm.scrollIntoView({ behavior: 'smooth' });
    });

    cancelBtn.addEventListener('click', () => {
      editForm.style.display = 'none';
      editBtn.disabled = false;
      editBtn.setAttribute('aria-expanded', 'false');
    });
  }

  setTimeout(() => {
    const msg = document.querySelector('.success-message');
    if (msg) msg.style.display = 'none';
  }, 3000);
});
</script>

</body>
</html>
