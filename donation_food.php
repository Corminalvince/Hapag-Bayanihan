<?php
session_start();
include "dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['food_id'], $_POST['quantity'], $_POST['location'])) {
    $food_id = intval($_POST['food_id']);
    $quantity = intval($_POST['quantity']);
    $location = trim($_POST['location']);

    $price_stmt = $conn->prepare("SELECT price FROM foods WHERE food_id = ?");
    $price_stmt->bind_param("i", $food_id);
    $price_stmt->execute();
    $price_result = $price_stmt->get_result();

   if ($price_result->num_rows > 0) {
    $food = $price_result->fetch_assoc();
    $total_price = $food['price'] * $quantity;

    $insert = $conn->prepare("INSERT INTO donations (user_id, food_id, quantity, total_price, location, donation_date) VALUES (?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("iiids", $user_id, $food_id, $quantity, $total_price, $location);
    $insert->execute();
    $insert->close();

    // --- Voucher Generation Logic ---
    // 30% chance to generate a voucher
   if (rand(1, 100) <= 30) {
    $voucher_code = strtoupper(bin2hex(random_bytes(4))); // Generates 8-char random code
    $discount = rand(5, 20); // Random discount between 5% and 20%
    $expiry_date = date('Y-m-d', strtotime('+30 days')); // Voucher valid for 30 days

    $voucher_stmt = $conn->prepare("INSERT INTO vouchers (user_id, code, discount, expiry_date, is_used) VALUES (?, ?, ?, ?, 0)");
    $voucher_stmt->bind_param("ssds", $user_id, $voucher_code, $discount, $expiry_date);
    $voucher_stmt->execute();
    $voucher_stmt->close();

    echo "<script>alert('Donation successful! 🎉 You also received a voucher: $voucher_code for $discount% off!'); window.location.href='main.php';</script>";
} else {
    echo "<script>alert('Donation submitted successfully!'); window.location.href='main.php';</script>";
}
    exit();

    }
    $price_stmt->close();
}

// Get available foods
$foods = $conn->query("SELECT food_id, food_name, price, image_path FROM foods");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donate Food - Hapag Bayanihan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff8f0;
            margin: 0;
        }
        .header {
            background-color: orange;
            padding: 15px 20px;
            color: white;
            font-size: 22px;
            font-weight: bold;
        }
        .container {
            padding: 30px;
        }
        .food-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .food-image {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            object-fit: cover;
            border-radius: 8px;
        }
        .food-details {
            flex: 1;
        }
        .food-details h3 {
            margin: 0;
        }
        .food-details p {
            margin: 5px 0;
            color: #555;
        }
        form {
            margin-top: 10px;
        }
        input[type="number"],
        input[type="text"] {
            padding: 6px 10px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .button {
            background-color: orange;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: darkorange;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: orange;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="header">Donate Food</div>
<div class="container">
    <?php if ($foods->num_rows > 0): ?>
        <?php while ($food = $foods->fetch_assoc()): ?>
            <div class="food-card">
                <img src="<?php echo htmlspecialchars($food['image_path']); ?>" class="food-image" alt="Food Image">
                <div class="food-details">
                    <h3><?php echo htmlspecialchars($food['food_name']); ?></h3>
                    <p>Price: ₱<?php echo number_format($food['price'], 2); ?></p>
                    <form method="post">
                        <input type="hidden" name="food_id" value="<?php echo $food['food_id']; ?>">
                        <input type="number" name="quantity" min="1" value="1" required>
                        <input type="text" name="location" placeholder="Enter location" required>
                        <button type="submit" class="button">Donate</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No food items available for donation.</p>
    <?php endif; ?>
    <a href="main.php" class="back-link">← Back to Food List</a>
</div>
</body>
</html>
<?php $conn->close(); ?>
