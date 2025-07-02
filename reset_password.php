<?php
/* 1.  Basic setup */
$done = false;   // <-- give them defaults so PHP doesn’t warn
$msg  = '';
$token = $_GET['token'] ?? '';

/* 2.  Handle the form */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token    = $_POST['token'] ?? '';
    $newPass  = $_POST['password'] ?? '';

    /* -----  DB connection  ----- */
    $conn = new mysqli("127.0.0.1", "root", "", "access_control", 3307);
    if ($conn->connect_error) die("DB error");

    $q = "SELECT email FROM password_resets
          WHERE token='$token' AND expires_at > NOW()";
    $res = $conn->query($q);

    if ($res && $res->num_rows) {
        $email  = $res->fetch_assoc()['email'];
        $hash   = password_hash($newPass, PASSWORD_DEFAULT);

        $conn->query("UPDATE users SET password='$hash' WHERE email='$email'");
        $conn->query("DELETE FROM password_resets WHERE token='$token'");
        $done = true;
    } else {
        $msg = "Link expired or invalid.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="reset.css">
</head>
<body>
<div class="box">
<?php if ($done): ?>
    <h2>Password Changed</h2>
    <p>You can now log in with your new password.</p>
<?php else: ?>
    <h2>Reset Password</h2>
    <p>Enter your new password below.</p>
    <?php if ($msg): ?><p class="error"><?= $msg ?></p><?php endif; ?>

    <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="password" name="password" placeholder="New Password" required>
        <button type="submit">Reset Password</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
