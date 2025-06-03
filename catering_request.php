<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include "dbconfig.php";

if (!empty($_POST['selected_foods'])) {
    $userId = $_SESSION['user_id'];
    $selectedFoods = $_POST['selected_foods'];

    foreach ($selectedFoods as $foodId) {
        $stmt = $conn->prepare("INSERT INTO catering_requests (user_id, food_id, requested_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $userId, $foodId);
        $stmt->execute();
    }

    echo "<script>alert('Catering request submitted successfully!'); window.location.href='view_catering.php';</script>";
} else {
    echo "<script>alert('No food items selected.'); window.location.href='main.php';</script>";
}
?>
