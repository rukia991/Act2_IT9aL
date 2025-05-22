<?php
include "dbconnect.php";

$imageName = 'default.jpg'; // Set default image

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageTmpName = $_FILES['image']['tmp_name'];
    $originalName = basename($_FILES['image']['name']);
    $imageName = time() . '_' . $originalName;
    move_uploaded_file($imageTmpName, 'images/' . $imageName);
}

// Check if the form was submitted
if (isset($_POST['savit'])) {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? null);
    $description = trim($_POST['description'] ?? '');

    // Validate required fields
    if (empty($name) || empty($category)) {
        echo "<script>alert('⚠️ Please fill in required fields: name and category.');
        window.history.back();</script>";
        exit();
    }

    // If description is empty, set to null or an empty string
    if (empty($description)) {
        $description = '';
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO cars (name, category, brand, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $category, $brand, $description, $imageName);

    if ($stmt->execute()) {
        echo "<script>
            alert('✅ Car successfully added!');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>alert('❌ Error: " . $conn->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>