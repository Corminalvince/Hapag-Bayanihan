<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include "dbconfig.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['food_id'], $_POST['quantity'], $_POST['payment_method'], $_POST['location'], $_POST['payment_amount'])) {
    $user_id = $_SESSION['user_id'];
    $food_id = intval($_POST['food_id']);
    $quantity = intval($_POST['quantity']);
    $payment_method = trim($_POST['payment_method']);
    $location = trim($_POST['location']);
    $payment_amount = floatval($_POST['payment_amount']);

    // Validate payment method
    $valid_methods = ["Cash on Delivery", "GCash"];
    if (!in_array($payment_method, $valid_methods)) {
        echo "Invalid payment method selected.";
        exit();
    }

    // Get food price and quantity
    $stmt = $conn->prepare("SELECT price, quantity FROM foods WHERE food_id = ?");
    $stmt->bind_param("i", $food_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Food item not found.";
        exit();
    }

    $food = $result->fetch_assoc();
    $price = $food['price'];
    $available_quantity = $food['quantity'];

    if ($quantity > $available_quantity) {
        echo "<script>alert('Not enough stock available.'); window.history.back();</script>";
        exit();
    }

    $total_price = $price * $quantity;

    if ($payment_method === "Cash on Delivery" && $payment_amount < $total_price) {
        echo "<script>alert('Payment amount is less than the total price.'); window.history.back();</script>";
        exit();
    }

    $change = ($payment_method === "Cash on Delivery") ? $payment_amount - $total_price : 0;

    // Assign a driver
    $driver_query = $conn->query("SELECT id FROM registration WHERE user_type = 'driver' ORDER BY RAND() LIMIT 1");
    if ($driver_query->num_rows === 0) {
        echo "<script>alert('No available drivers at the moment.'); window.history.back();</script>";
        exit();
    }

    $driver = $driver_query->fetch_assoc();
    $driver_id = $driver['id'];

    // Insert order
    $insert = $conn->prepare("INSERT INTO orders (id, food_id, quantity, total_price, payment_method, location, driver_id, status, order_date, assigned_at, payment_amount, `change`)
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending Driver Confirmation', NOW(), NOW(), ?, ?)");
    $insert->bind_param("iiidssidd", $user_id, $food_id, $quantity, $total_price, $payment_method, $location, $driver_id, $payment_amount, $change);

    if ($insert->execute()) {
        // Update stock
        $update_qty = $conn->prepare("UPDATE foods SET quantity = quantity - ? WHERE food_id = ?");
        $update_qty->bind_param("ii", $quantity, $food_id);
        $update_qty->execute();
        $update_qty->close();

        if ($payment_method === "GCash") {
            $gcash_number = "09925335362";
            echo "<script>
                alert('Please send ₱" . number_format($total_price, 2) . " to GCash number: $gcash_number');
                window.location.href = 'main.php';
            </script>";
        } else {
            echo "<script>
                alert('Order placed! Please pay ₱" . number_format($total_price, 2) . " to the delivery driver. Change: ₱" . number_format($change, 2) . "');
                window.location.href = 'main.php';
            </script>";
        }
        
    } else {
        echo "Error placing order: " . $conn->error;
    }

    $insert->close();
} else {
    echo "Invalid order submission.";
}

$conn->close();
?>
