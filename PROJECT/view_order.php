<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include "dbconfig.php";
$user_id = $_SESSION['user_id'];

// Handle cancellation and reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_order_id'])) {
        $cancel_id = intval($_POST['cancel_order_id']);
        $cancel_stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND id = ?");
        $cancel_stmt->bind_param("ii", $cancel_id, $user_id);
        $cancel_stmt->execute();
        $cancel_stmt->close();
    } elseif (isset($_POST['reorder_id'])) {
        $reorder_id = intval($_POST['reorder_id']);
        $re_stmt = $conn->prepare("SELECT food_id, quantity, payment_method, location FROM orders WHERE order_id = ? AND id = ?");
        $re_stmt->bind_param("ii", $reorder_id, $user_id);
        $re_stmt->execute();
        $re_result = $re_stmt->get_result();

        if ($re_result->num_rows > 0) {
            $order = $re_result->fetch_assoc();
            $food_id = $order['food_id'];
            $quantity = $order['quantity'];
            $method = $order['payment_method'];
            $location = $order['location'];

            $price_stmt = $conn->prepare("SELECT price FROM foods WHERE food_id = ?");
            $price_stmt->bind_param("i", $food_id);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();

            if ($price_result->num_rows > 0) {
                $food = $price_result->fetch_assoc();
                $total_price = $food['price'] * $quantity;

                $insert = $conn->prepare("INSERT INTO orders (id, food_id, quantity, total_price, payment_method, location, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $insert->bind_param("iiidss", $user_id, $food_id, $quantity, $total_price, $method, $location);
                $insert->execute();
                $insert->close();
            }
            $price_stmt->close();
        }
        $re_stmt->close();
    }
}

// Fetch updated orders with driver_status
$sql = "SELECT o.order_id, o.order_date, o.quantity, o.total_price, o.payment_method, o.location, o.status,
               f.food_name, f.image_path
        FROM orders o
        JOIN foods f ON o.food_id = f.food_id
        WHERE o.id = ? ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Hapag Bayanihan</title>
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
        .order-container {
            padding: 30px;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .order-image {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            object-fit: cover;
            border-radius: 8px;
        }
        .order-details {
            flex: 1;
        }
        .order-details h3 {
            margin: 0;
        }
        .order-details p {
            margin: 5px 0;
            color: #555;
        }
        .actions {
            margin-top: 10px;
        }
        .actions form {
            display: inline;
        }
        .button {
            background-color: orange;
            border: none;
            color: white;
            padding: 8px 12px;
            margin-right: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: darkorange;
        }
        .back-link {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            background: orange;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .status {
            font-weight: bold;
            color: #444;
        }
    </style>
</head>
<body>
<div class="header">My Orders</div>
<div class="order-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="order-image" alt="Food Image">
                <div class="order-details">
                    <h3><?php echo htmlspecialchars($row['food_name']); ?></h3>
                    <p>Location: <?php echo $row['location']; ?></p> 
                    <p>Quantity: <?php echo $row['quantity']; ?></p>
                    <p>Payment Method: <?php echo $row['payment_method']; ?></p>
                    <p>Total Price: ₱<?php echo number_format($row['total_price'], 2); ?></p>
                    <p>Ordered on: <?php echo date("F j, Y g:i A", strtotime($row['order_date'])); ?></p>

                    <p class="status">
                        <?php
                            if ($row['status'] === 'Pending') {
                                echo "🚕 Waiting for driver to be assigned";
                            } elseif ($row['status'] === 'Accepted') {
                                echo "✅ Driver accepted your order";
                            } elseif ($row['status'] === 'Declined') {
                                echo "❌ Previous driver declined - reassigning";
                            } else {
                                echo "🕒 Status: " . htmlspecialchars($row['status']);
                            }
                        ?>
                    </p>

                    <div class="actions">
                        <form method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="cancel_order_id" value="<?php echo $row['order_id']; ?>">
                            <button type="submit" class="button">Cancel</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Do you want to reorder this item?');">
                            <input type="hidden" name="reorder_id" value="<?php echo $row['order_id']; ?>">
                            <button type="submit" class="button">Reorder</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You haven't placed any orders yet.</p>
    <?php endif; ?>
    <a href="main.php" class="back-link">← Back to Food List</a>
</div>
</body>
</html>
<?php $conn->close(); ?>
