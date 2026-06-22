<?php
require_once 'config.php';

$email = "admin@marketsa.co.za";
$plainPassword = "admin123";
$hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin (full_name, email, password) VALUES (?, ?, ?)");
$name = "Admin";
$stmt->bind_param("sss", $name, $email, $hashedPassword);

$stmt->execute();

echo "Admin recreated successfully";
?>