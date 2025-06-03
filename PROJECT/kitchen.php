<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kitchen Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
        }

        .header {
            background-color: orange;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .user-menu {
            position: relative;
            display: inline-block;
            color: white;
            cursor: pointer;
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

        .user-menu:hover .dropdown {
            display: block;
        }

        .dropdown a {
            text-decoration: none;
            color: #333;
            display: block;
            padding: 8px 0;
        }

        .page-content {
            background-color: white;
            min-height: 80vh;
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .profile-container {
            background: orange;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            font-size: 24px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            margin-top: 20px;
        }

        input, button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            box-sizing: border-box;
        }

        button {
            margin-top: 30px;
            background-color: #d3d3d3;
            color: #555;
            cursor: pointer;
        }

        #map {
            height: 300px;
            margin-top: 10px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="kitchen.php" class="logo">
        <img src="logo.png" alt="Logo">
        Hapag Bayanihan
    </a>
    <div class="user-menu">
        <span>👤 <?php echo htmlspecialchars($_SESSION['firstname'] ?? 'User'); ?> ▼</span>
        <div class="dropdown">
            <a href="profile.php">🙍‍♂️ Profile</a>
            <a href="#">📦 Orders & reordering</a>
            <a href="#">🎟 Vouchers</a>
            <a href="#">🏆 Hapag rewards</a>
            <a href="#">❓ Help Center</a>
            <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="profile-container">
        <h2>Upload Food Details 🍲</h2>

        <form action="upload_foods.php" method="POST" enctype="multipart/form-data">
          <label for="food_name">Food Name</label>
<input type="text" id="food_name" name="food_name" required>

<label for="description">Description</label>
<input type="text" id="description" name="description" required>
<label for="price">Price (₱)</label>
<input type="number" step="0.01" id="price" name="price" required>

<label for="quantity">Quantity Available</label>
<input type="number" id="quantity" name="quantity" min="1" required>

<label for="carinderia_name">Carinderia Name</label>
<input type="text" id="carinderia_name" name="carinderia_name" value="<?php echo htmlspecialchars($_SESSION['carinderia_name'] ?? ''); ?>" required>

<label for="food_image">Upload Image</label>
<input type="file" id="food_image" name="food_image" accept="image/*">

            <label for="map">Choose Location</label>
            <div id="map"></div>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Upload Food</button>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([9.84999, 124.14354], 13); // Default: Manila

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker;

    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
</script>

</body>
</html>
