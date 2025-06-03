<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
require 'dbconfig.php'; // Replace with your actual DB connection file

// Get form data
$user_id = $_SESSION['user_id'];
$food_name = $_POST['food_name'];
$description = $_POST['description'];
$price = $_POST['price'];
$quantity = intval($_POST['quantity']); // ✅ New: Capture quantity
$carinderia_name = $_POST['carinderia_name'] ?? '';
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

// Handle file upload
$target_dir = "uploads/";
$food_image_path = "";

if (!empty($_FILES["food_image"]["name"])) {
    $target_file = $target_dir . basename($_FILES["food_image"]["name"]);
    if (move_uploaded_file($_FILES["food_image"]["tmp_name"], $target_file)) {
        $food_image_path = $target_file;
    }
}

// ✅ Updated SQL insert with quantity
$sql = "INSERT INTO foods (kitchen_id,food_name, description, price, quantity, image_path, latitude, longitude, carinderia_name)
        VALUES (?,?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issdissss",$user_id, $food_name, $description, $price, $quantity, $food_image_path, $latitude, $longitude, $carinderia_name);

if ($stmt->execute()) {
        echo "<script>
                        alert('Admin login successful!');
                        window.location.href = 'kitchen.php';
                      </script>";
    header("Location: kitchen.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
