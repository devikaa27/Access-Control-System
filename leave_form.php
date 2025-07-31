<?php
session_start();

// Make sure the user is logged in and has a user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$showModal = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include 'db.php';

    $user_id = $_SESSION['user_id'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $reason = $_POST['reason'];

    // Insert the leave request with user_id
    $stmt = $conn->prepare("INSERT INTO employee_leaves (user_id, start_date, end_date, reason) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $start, $end, $reason);
        if ($stmt->execute()) {
            $showModal = true;
        } else {
            echo "<p style='color:red;'>Erro: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Erro na SQL: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Formulário de Pedido de Ausência</title>
  <link rel="stylesheet" href="style_form.css">
</head>
<body>

<div class="back-button-container">
    <a href="employee_dashboard.php" class="back-button">🏠︎</a>
</div>

<div class="main-content">
  <h2>Pedido de Ausência</h2>

  <form method="POST" action="leave_form.php">
    <label for="start_date">Data de Início:</label>
    <input type="date" id="start_date" name="start_date" required>

    <label for="end_date">Data de Fim:</label>
    <input type="date" id="end_date" name="end_date" required>

    <label for="reason">Motivo:</label>
    <textarea id="reason" name="reason" required></textarea>

    <button type="submit">Pedir Ausência</button>
  </form>
</div>

<?php if ($showModal): ?>
<div class="modal">
  <div class="modal-content">
    <div class="checkmark-circle">
      <div class="checkmark"></div>
    </div>
    <h3>Pedido de Ausência Submetido com Sucesso</h3>
    <p>O seu pedido de ausência foi enviado.</p>
    <a href="leave_form.php"><button>Concluído</button></a>
  </div>
</div>

<script>
  setTimeout(() => {
    const modal = document.querySelector('.modal');
    if (modal) {
      modal.style.opacity = '0';
      modal.style.transition = 'opacity 0.5s ease-out';
      setTimeout(() => modal.style.display = 'none', 500);
    }
  }, 3000); // modal disappears after 3 seconds
</script>
<?php endif; ?>

</body>
</html>
