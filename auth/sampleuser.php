<?php
include "../dbconnect.php";

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Sample user added.";
} else {
    echo "Error: " . $stmt->error;
}
?>