<?php
$host = "127.0.0.1";
$port = 3307;
$user = "root";
$password = "";
$dbname = "access_control";

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
