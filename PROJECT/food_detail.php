<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include "dbconfig.php";

if (isset($_GET['id'])) {
    $food_id = $_GET['id'];

    // Include carinderia_name in the SELECT
    $food_sql = "SELECT food_id, food_name, price, description, image_path, latitude, longitude, carinderia_name FROM foods WHERE food_id = ?";
    $stmt = $conn->prepare($food_sql);
    $stmt->bind_param("i", $food_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $food = $result->fetch_assoc();
    } else {
        echo "Food item not found.";
        exit();
    }
} else {
    echo "No food item selected.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Details - Hapag Bayanihan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: white;
        }
        .header {
            background-color: orange;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
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
        .content {
            padding: 20px;
        }
        .food-details {
            display: flex;
            justify-content: space-between;
            max-width: 1000px;
            margin: 0 auto;
        }
        .food-image img {
            max-width: 500px;
            border-radius: 10px;
        }
        .food-info {
            max-width: 450px;
        }
        .food-info h2 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .food-info p {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .food-info .price {
            font-size: 22px;
            font-weight: bold;
            color: green;
        }
        .food-info .location {
            font-size: 18px;
            color: #888;
            margin-top: 10px;
        }
        .back-button {
            background-color: orange;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
        .back-button:hover {
            background-color: darkorange;
        }
      #map {
    width: 100%;
    height: 300px;
    margin-top: 20px;
}

.order-form {
    margin-top: 20px;
    padding: 20px;
    background-color: #fff8f0;
    border: 1px solid #f1c185;
    border-radius: 10px;
    max-width: 500px;
}

.order-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
}

.order-form input[type="number"],
.order-form input[type="text"],
.order-form select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

.order-form input:focus,
.order-form select:focus {
    border-color: #ffa500;
    outline: none;
    box-shadow: 0 0 3px rgba(255, 165, 0, 0.5);
}

.order-form button {
    background-color: orange;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.order-form button:hover {
    background-color: darkorange;
}

#gcashNote {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
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
        <span>👤 <?php echo htmlspecialchars($_SESSION['firstname']); ?> ▼</span>
        <div class="dropdown">
            <a href="profile.php">🙍‍♂️ Profile</a>
            <a href="#">📦 Orders & Reordering</a>
            <a href="#">🎟 Vouchers</a>
            
            <a href="#">❓ Request</a>
            <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>

<div class="content">
    <h1>Food Details</h1>
    <div class="food-details">
        <div class="food-image">
            <img src="<?php echo htmlspecialchars($food['image_path']); ?>" alt="Food Image">
        </div>
        <div class="food-info">
            <h2><?php echo htmlspecialchars($food['food_name']); ?></h2>
            <p style="font-style: italic; color: #666;">by <?php echo htmlspecialchars($food['carinderia_name']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($food['description'])); ?></p>
            <p class="price">₱<?php echo number_format($food['price'], 2); ?></p>


            <!-- Full address from reverse geocoding -->
            <p class="location" id="location-display">Loading location...</p>
            <!-- Full address from reverse geocoding -->
           

            <a href="main.php" class="back-button">Back to Food List</a>
            <div id="map"></div>
     <form method="POST" action="order.php" class="order-form">
    <!-- Example inputs, adjust based on your setup -->
    <label for="food_id">Food ID:</label>
    <input type="number" name="food_id" value="<?php echo $food['food_id']; ?>" readonly><br>

    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" required><br>

    <label for="location">Delivery Location:</label>
    <input type="text" name="location" required><br>

    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" id="payment_method" required onchange="toggleAmountField()">
        <option value="">--Select--</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="GCash">GCash</option>
    </select><br>

    <div id="paymentAmountWrapper" style="display: none;">
        <label for="payment_amount">Amount You'll Pay (₱):</label>
        <input type="number" step="0.01" name="payment_amount" id="payment_amount" min="1"readonly>
    </div>

    <div id="gcashNote" style="display: none; margin-top: 10px; background: #f0f0f0; padding: 10px; border-radius: 5px;">
        Please send payment to <strong>GCash Number: 09925335362</strong><br>
        Amount will be shown after placing the order.
    </div>

    <button type="submit">Place Order</button>
</form>

<script>
    const price = <?php echo $food['price']; ?>;

    function toggleAmountField() {
        const method = document.getElementById("payment_method").value;
        const wrapper = document.getElementById("paymentAmountWrapper");
        const gcashNote = document.getElementById("gcashNote");

        if (method === "Cash on Delivery") {
            wrapper.style.display = "block";
            gcashNote.style.display = "none";
            updatePaymentAmount(); // Automatically compute amount
        } else if (method === "GCash") {
            wrapper.style.display = "none";
            document.getElementById("payment_amount").value = "";
            gcashNote.style.display = "block";
        } else {
            wrapper.style.display = "none";
            gcashNote.style.display = "none";
            document.getElementById("payment_amount").value = "";
        }
    }

    function updatePaymentAmount() {
        const quantity = document.querySelector('input[name="quantity"]').value;
        const paymentAmount = document.getElementById("payment_amount");

        if (document.getElementById("payment_method").value === "Cash on Delivery" && quantity > 0) {
            const total = (price * quantity).toFixed(2);
            paymentAmount.value = total;
        }
    }

    document.querySelector('input[name="quantity"]').addEventListener("input", updatePaymentAmount);
</script>

        </div>
    </div>
</div>

<script>
    const latitude = <?php echo $food['latitude']; ?>;
    const longitude = <?php echo $food['longitude']; ?>;
    const apiKey = "AIzaSyDVtFjGM8FUSEaXZsbmlFdDOhOFqjtrjmI";

    function initMap() {
        const foodLocation = { lat: latitude, lng: longitude };

        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: foodLocation,
        });

        const marker = new google.maps.Marker({
            position: foodLocation,
            map: map,
            title: "<?php echo htmlspecialchars($food['food_name']); ?>",
        });

        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: foodLocation }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById("location-display").innerText = "Available at: " + results[0].formatted_address;
            } else {
                document.getElementById("location-display").innerText = "Location not available.";
            }
        });
    }

    function loadGoogleMaps() {
        const script = document.createElement("script");
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMap`;
        script.async = true;
        script.defer = true;
        document.body.appendChild(script);
    }

    loadGoogleMaps();
</script>

</body>
</html>

<?php $conn->close(); ?>
