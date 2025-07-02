<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    switch ($action) {
        case 'create':
            $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $email);
            $message = "User created successfully.";
            break;
        case 'update':
            $stmt = $conn->prepare("UPDATE users SET password = ?, email = ? WHERE username = ?");
            $stmt->bind_param("sss", $password, $email, $username);
            $message = "User updated successfully.";
            break;
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $message = "User deleted successfully.";
            break;
    }

    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
    }
}

$result = $conn->query("SELECT username, email FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Users</title>
    <link rel="stylesheet" href="style_admin.css">
</head>
<body>
    <h2>Welcome, Admin!</h2>
    <?php if ($message) echo "<p class='success'>$message</p>"; ?>

    
<form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password (for create/update)">
    <input type="email" name="email" placeholder="Email (for create/update)">

    <div class="button-group">
        <button type="submit" name="action" value="create">Create User</button>
        <button type="submit" name="action" value="update">Update User</button>
        <button type="submit" name="action" value="delete">Delete User</button>
    </div>
</form>

    <h3>List of Users</h3>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['username']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
