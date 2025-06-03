<?php
session_start();
include "dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_id'], $_POST['order_id'])) {
    $user_id = $_SESSION['user_id'];
    $voucher_id = intval($_POST['voucher_id']);
    $order_id = intval($_POST['order_id']);

    // 1. Check if the order exists and belongs to this user
    $order_check = $conn->prepare("SELECT total_price, voucher_id FROM orders WHERE order_id = ? AND id = ?");
    $order_check->bind_param("ii", $order_id, $user_id);
    $order_check->execute();
    $order_result = $order_check->get_result();
    $order = $order_result->fetch_assoc();

    if (!$order) {
        echo "<script>alert('Order not found or unauthorized access.'); window.history.back();</script>";
        exit();
    }

    if (!empty($order['voucher_id'])) {
        echo "<script>alert('A voucher has already been applied to this order.'); window.history.back();</script>";
        exit();
    }

    $total_price = $order['total_price'];

    // 2. Get voucher details
    $voucher_query = $conn->prepare("SELECT discount, expiry_date FROM vouchers WHERE id = ?");
    $voucher_query->bind_param("i", $voucher_id);
    $voucher_query->execute();
    $voucher_result = $voucher_query->get_result();
    $voucher = $voucher_result->fetch_assoc();

    if (!$voucher) {
        echo "<script>alert('Voucher not found.'); window.history.back();</script>";
        exit();
    }

    $today = date('Y-m-d');
    if ($voucher['expiry_date'] < $today) {
        echo "<script>alert('Voucher has expired.'); window.history.back();</script>";
        exit();
    }

    // 3. Calculate discount
    $discount = floatval($voucher['discount']);
    $discount_amount = ($discount / 100) * $total_price;
    $discounted_total = $total_price - $discount_amount;

    // 4. Update the order with discount and voucher info
    $update = $conn->prepare("UPDATE orders 
        SET voucher_id = ?, discount = ?, discounted_total = ?, total_price = ? 
        WHERE order_id = ? AND id = ?");
    $update->bind_param("idddii", $voucher_id, $discount, $discounted_total, $discounted_total, $order_id, $user_id);

    if ($update->execute()) {
        echo "<script>alert('Voucher applied! New total: ₱" . number_format($discounted_total, 2) . "'); window.location.href='main.php';</script>";
    } else {
        echo "<script>alert('Failed to apply voucher. Please try again.'); window.history.back();</script>";
    }

    $update->close();
} else {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
}
?>
