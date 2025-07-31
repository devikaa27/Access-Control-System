<?php
session_start();
include 'db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    echo "Access denied.";
    exit;
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInRole = $_SESSION['role'];

// Validate POSTed ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header("Location: profile.php");
    exit;
}

$user_id = (int) $_POST['id'];

// Allow editing if:
// - boss is editing anyone
// - OR employee is editing themselves
if ($loggedInRole !== 'boss' && $loggedInUserId !== $user_id) {
    echo "Access denied.";
    exit;
}

// Validate inputs
$errors = [];
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$Firstname = trim($_POST['Firstname'] ?? '');
$Lastname = trim($_POST['Lastname'] ?? '');
$city = trim($_POST['city'] ?? '');
$country = trim($_POST['country'] ?? '');

if (!$email) {
    $errors[] = "Invalid email address.";
}

if (!empty($errors)) {
    $_SESSION['edit_errors'] = $errors;
    header("Location: profile.php?id=$user_id");
    exit;
}

// Update in database
$stmt = $conn->prepare("UPDATE users SET email = ?, Firstname = ?, Lastname = ?, city = ?, country = ? WHERE id = ?");
$stmt->bind_param("sssssi", $email, $Firstname, $Lastname, $city, $country, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['profile_updated'] = true;

// ✅ Redirect back to profile with success flag
header("Location: profile.php?id=$user_id&updated=1");
exit;
?>
