<?php
include "dbconnect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    // Delete the image file before removing the record (optional but recommended)
    $imgQuery = $conn->prepare("SELECT image FROM cars WHERE id = ?");
    $imgQuery->bind_param("i", $id);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result();
    if ($imgRow = $imgResult->fetch_assoc()) {
        $imgPath = "uploads/" . $imgRow['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    // Delete car record
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>