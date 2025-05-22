<?php
include 'dbconnect.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $brand = trim($_POST['brand']);
    $description = trim($_POST['description']);
    $created_at = $_POST['created_at'];

    // Handle image upload
    $image = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $upload_dir = "images/";

    // Check if a new image is uploaded
    if (!empty($image)) {
        $image_path = $upload_dir . basename($image);

        // Optionally, delete the old image here if you store it
        move_uploaded_file($tmp_name, $image_path);

        // Update with new image
        $sql = "UPDATE cars SET 
                    name = ?, 
                    category = ?, 
                    brand = ?, 
                    description = ?, 
                    image = ?, 
                    created_at = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $category, $brand, $description, $image, $created_at, $id);
    } else {
        // Update without changing image
        $sql = "UPDATE cars SET 
                    name = ?, 
                    category = ?, 
                    brand = ?, 
                    description = ?, 
                    created_at = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $category, $brand, $description, $created_at, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Car details updated successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Failed to update car details.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
