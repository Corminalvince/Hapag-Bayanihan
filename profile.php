<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include "dbconfig.php";

// Upload new profile image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $userId = $_SESSION['user_id'];
    $image = $_FILES['profile_image'];

    if ($image['error'] === 0 && is_uploaded_file($image['tmp_name'])) {
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = "user_" . $userId . "." . $ext;
        $targetPath = "uploads/" . $filename;

        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE registration SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $userId);
            $stmt->execute();
            $stmt->close();
            $_SESSION['profile_image'] = $filename;
        }
    }
}

// Load profile image
if (!isset($_SESSION['profile_image']) !== $_SESSION['user_id']) {
    $stmt = $conn->prepare("SELECT profile_image FROM registration WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($image);
    if ($stmt->fetch()) {
        $_SESSION['profile_image'] = $image ?? 'default.png';
    }
    $stmt->close();
}

$raw_image = $_SESSION['profile_image'] ?? 'default.png';
$profile_image = strpos($raw_image, 'uploads/') === false ? "uploads/$raw_image" : $raw_image;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main - Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
        }

        .header {
            background-color: orange;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 22px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .logo img {
            height: 40px;
            margin-right: 12px;
        }

        .user-menu {
            position: relative;
            cursor: pointer;
        }

        .user-menu span {
            font-weight: 240;
        }

        .dropdown {
             display: none;
            position: absolute;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 10px;
            width: 220px;
            z-index: 1;
        }
.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

        .user-menu:hover .dropdown {
            display: block;
        }

        .dropdown a {
            display: flex;
            padding: 12px 50px;
            text-decoration: none;
            color: #333;
            transition: background 0.2s ease;
        }

        .dropdown a:hover {
            background-color: #f2f2f2;
        }

        .page-content {
            display: flex;
            justify-content: center;
            padding: 40px 16px;
            background-color: #fafafa;
        }

        .profile-container {
            background-color: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 480px;
        }

        .profile-img {
            display: block;
            margin: 0 auto 24px;
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid orange;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 24px;
            text-align: center;
        }

        label {
            font-size: 14px;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: orange;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: darkorange;
        }

        form {
            margin-bottom: 24px;
        }

    </style>
</head>
<body>

<div class="header">
    <a href="main.php" class="logo">
        <img src="logo.png" alt="Logo">
        Hapag Bayanihan
    </a>
    <div class="user-menu">
     <div class="user-menu">
    <div class="user-info">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-icon">
        <span><?php echo htmlspecialchars($_SESSION['firstname'] ?? 'User'); ?> ▼</span>
    </div>

</div>

        <div class="dropdown">
           <a href="profile.php">🙍‍♂️ Profile</a>
            <a href="view_order.php">📦 Orders & Reordering</a>
            <a href="available_vouchers.php">🎟 Vouchers</a>
            <a href="view_catering.php">🏆 Hapag Catering</a>
            <a href="request.php">❓ Request</a>
            <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="profile-container">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-img">

        <form method="POST" enctype="multipart/form-data">
            <label for="profile_image">Change Profile Picture</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
            <button type="submit">Upload</button>
        </form>

        <h2>My Profile</h2>
        <form>
            <label for="firstname">First Name</label>
            <input type="text" id="firstname" value="<?php echo htmlspecialchars($_SESSION['firstname'] ?? ''); ?>" readonly>

            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" value="<?php echo htmlspecialchars($_SESSION['lastname'] ?? ''); ?>" readonly>

            <label for="mobile">Mobile Number</label>
            <input type="text" id="mobile" placeholder="<?php echo htmlspecialchars($_SESSION['contact'] ?? ''); ?>" readonly>

            <button type="submit" disabled>Save</button>
        </form>
    </div>
</div>

</body>
</html>
