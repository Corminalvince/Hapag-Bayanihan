    <?php
session_start();
include "dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's latest order
$order_query = $conn->prepare("SELECT order_id, total_price, voucher_id FROM orders WHERE id = ? ORDER BY order_date DESC LIMIT 1");
$order_query->bind_param("i", $user_id);
$order_query->execute();
$order_result = $order_query->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    echo "No orders found.";
    exit();
}

$order_id = $order['order_id'];
$order_total = $order['total_price'];
$voucher_applied = $order['voucher_id'];

// Fetch available vouchers
$voucher_result = $conn->query("SELECT * FROM vouchers ORDER BY voucher_id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Vouchers</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8f8f8; }
        h2 { color: #333; }
        .voucher { background: white; border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .voucher h3 { margin: 0; }
        .voucher p { margin: 5px 0; }
        .apply-btn {
            background: green;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .info { padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px; color: #856404; margin-bottom: 20px; }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background: #007bff;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<h2>🛍️ Your Order</h2>
<p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
<p><strong>Total Price:</strong> ₱<?php echo number_format($order_total, 2); ?></p>

<?php if ($voucher_applied): ?>
    <div class="info">
        A voucher has already been applied to this order. You cannot apply another one.
    </div>
<?php else: ?>
    <h2>🎟️ Available Vouchers</h2>

    <?php if ($voucher_result->num_rows > 0): ?>
        <?php while ($voucher = $voucher_result->fetch_assoc()): ?>
            <?php
                $discount_amount = ($voucher['discount'] / 100) * $order_total;
                $new_total = $order_total - $discount_amount;
            ?>
            <div class="voucher">
                <h3><?php echo htmlspecialchars($voucher['code']); ?> - <?php echo $voucher['discount']; ?>% Off</h3>
                <p>Discount: ₱<?php echo number_format($discount_amount, 2); ?></p>
                <p>New Total: <strong>₱<?php echo number_format($new_total, 2); ?></strong></p>
                <form method="POST" action="apply_voucher.php">
                    <input type="hidden" name="voucher_id" value="<?php echo $voucher['voucher_id']; ?>">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                    <button type="submit" class="apply-btn">Apply This Voucher</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No vouchers available at the moment.</p>
    <?php endif; ?>
<?php endif; ?>

<!-- Back to Main -->
<a href="main.php" class="back-btn">← Back to Main</a>

</body>
</html>

<?php $conn->close(); ?>
