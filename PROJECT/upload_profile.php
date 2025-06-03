<?php
session_start();
include "dbconfig.php"; // ensure this connects $conn

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $user_id = $_SESSION['user_id'];
    $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $filename = "profile_" . $user_id . "." . $ext;
    $target_file = $target_dir . $filename;

    // Validate it's an image
    $check = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($check !== false) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Save to DB
            $sql = "UPDATE registration SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();

            // Store in session (optional)
            $_SESSION['profile_image'] = $filename;

            header("Location: profile.php");
            exit();
        } else {
            echo "Failed to upload image.";
        }
    } else {
        echo "File is not a valid image.";
    }
}
?>
