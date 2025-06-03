<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

include "dbconfig.php";

$user_id = $_SESSION['user_id'];
$food_name = $_POST['food_name'] ?? '';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

if (!$food_name || !$latitude || !$longitude) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO orders (user_id, food_name, latitude, longitude) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issd", $user_id, $food_name, $latitude, $longitude);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Order placed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

$stmt->close();
$conn->close();
?>