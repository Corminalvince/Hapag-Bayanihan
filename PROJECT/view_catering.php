<?php
session_start();
include "dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check user type
$type_check = $conn->prepare("SELECT user_type FROM registration WHERE id = ?");
$type_check->bind_param("i", $user_id);
$type_check->execute();
$type_result = $type_check->get_result();
$user_type_data = $type_result->fetch_assoc();
$type_check->close();

// Process order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['address']) && isset($_POST['total_price'])) {
    $address = $_POST['address'];
    $total_price = $_POST['total_price'];
    $payment_method = $_POST['payment_method'];
    $fullname = $_POST['fullname'];
    
    // Check if food_ids are set
    if (isset($_POST['food_ids']) && is_array($_POST['food_ids'])) {
        $success = true;
        
        foreach ($_POST['food_ids'] as $food_id) {
            // Get food details
            $food_query = $conn->prepare("SELECT price FROM foods WHERE food_id = ?");
            $food_query->bind_param("i", $food_id);
            $food_query->execute();
            $food_result = $food_query->get_result();
            $food_data = $food_result->fetch_assoc();
            $food_query->close();
            
            if ($food_data) {
                // Find an available driver
                $driver_query = $conn->prepare("SELECT id FROM registration WHERE user_type = 'driver' ORDER BY RAND() LIMIT 1");
                $driver_query->execute();
                $driver_result = $driver_query->get_result();
                $driver_data = $driver_result->fetch_assoc();
                $driver_query->close();
                
                $driver_id = $driver_data ? $driver_data['id'] : null;
                
                // Insert order
                $order_stmt = $conn->prepare("INSERT INTO orders (id, food_id, quantity, total_price, payment_method, location, status, driver_id) VALUES (?, ?, 1, ?, ?, ?, 'Pending', ?)");
                $order_stmt->bind_param("iidssi", $user_id, $food_id, $food_data['price'], $payment_method, $address, $driver_id);
                
                if (!$order_stmt->execute()) {
                    $success = false;
                }
                $order_stmt->close();
                
                // Remove from catering_requests
                $remove_stmt = $conn->prepare("DELETE FROM catering_requests WHERE user_id = ? AND food_id = ?");
                $remove_stmt->bind_param("ii", $user_id, $food_id);
                $remove_stmt->execute();
                $remove_stmt->close();
            }
        }
        
        if ($success) {
            // If user is driver, redirect to driver dashboard, otherwise to main page
            if ($user_type_data && $user_type_data['user_type'] === 'driver') {
                echo "<script>alert('Order placed successfully!'); window.location.href='driver.php';</script>";
                exit();
            } else {
                echo "<script>alert('Order placed successfully!'); window.location.href='main.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Failed to place order. Please try again.');</script>";
        }
    }
}

// Fetch user details
$user_sql = "SELECT FirstName, LastName FROM registration WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Fetch requested foods
$sql = "
    SELECT f.food_id, f.food_name, f.price, f.image_path, f.carinderia_name
    FROM catering_requests cr
    JOIN foods f ON cr.food_id = f.food_id
    WHERE cr.user_id = ?
    ORDER BY cr.requested_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$foods = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $foods[] = $row;
    $total_price += $row['price'];
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catering Request Summary</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: orange;
        }
        form {
            max-width: 800px;
            margin: 0 auto;
        }
        .user-details, .payment-method {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            font-size: 18px;
        }
        .user-details input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .food-summary {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .food-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            text-align: center;
        }
        .food-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }
        .food-item h3 {
            margin: 10px 0 5px;
        }
        .food-item p {
            margin: 0;
        }
        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 30px;
            color: green;
        }
        .confirm-btn {
            display: block;
            margin: 30px auto 0;
            padding: 12px 30px;
            font-size: 16px;
            background: orange;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .confirm-btn:hover {
            background: darkorange;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #ccc;
            color: black;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h1>Selected Food for Catering</h1>

<?php if (empty($foods)): ?>
    <p style="text-align:center;">No catering requests found.</p>
<?php else: ?>
    <!-- Changed from action="order.php" to same page processing -->
    <form method="POST" action="">
        <div class="user-details">
            <label><strong>Customer Name:</strong></label><br>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user_data['FirstName'] . ' ' . $user_data['LastName']) ?>" readonly><br><br>

            <label><strong>Address:</strong></label><br>
            <input type="text" name="address" required>
        </div>

        <div class="food-summary">
            <?php foreach ($foods as $food): ?>
                <div class="food-item">
                    <img src="<?= htmlspecialchars($food['image_path']) ?>" alt="Food Image">
                    <p class="carinderia-name"><?= htmlspecialchars($food['carinderia_name']) ?></p>
                    <h3><?= htmlspecialchars($food['food_name']) ?></h3>
                    <p>₱<?= number_format($food['price'], 2) ?></p>
                    <!-- Add hidden field for each food_id -->
                    <input type="hidden" name="food_ids[]" value="<?= $food['food_id'] ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="payment-method">
            <strong>Payment Method:</strong> Cash on Delivery
        </div>

        <p class="total">Total Payment: ₱<?= number_format($total_price, 2) ?></p>

        <!-- Hidden fields to pass data -->
        <input type="hidden" name="payment_method" value="Cash on Delivery">
        <input type="hidden" name="total_price" value="<?= $total_price ?>">

        <button type="submit" class="confirm-btn">Confirm Order</button>
    </form>
<?php endif; ?>

<a href="main.php" class="back-btn">← Back to Menu</a>

</body>
</html>
<?php $conn->close(); ?>