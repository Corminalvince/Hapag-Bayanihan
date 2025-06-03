<?php
session_start();
include "dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get driver info
$type_check = $conn->prepare("SELECT user_type, firstname FROM registration WHERE id = ?");
$type_check->bind_param("i", $user_id);
$type_check->execute();
$type_result = $type_check->get_result();
$user = $type_result->fetch_assoc();

if (!$user || $user['user_type'] !== 'driver') {
    echo "<script>alert('Access denied: Only drivers can access this page.'); window.location.href='main.php';</script>";
    exit();
}

$firstname = $user['firstname'];

// Handle Accept, Decline, or Deliver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Accepted and rider will pick up and it will deliver to you' WHERE order_id = ? AND driver_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'decline') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Declined' WHERE order_id = ? AND driver_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $stmt->close();

        // Reassign to another driver
        $driver_query = $conn->prepare("SELECT id FROM registration WHERE user_type = 'driver' AND id != ? ORDER BY RAND() LIMIT 1");
        $driver_query->bind_param("i", $user_id);
        $driver_query->execute();
        $driver_result = $driver_query->get_result();

        if ($driver = $driver_result->fetch_assoc()) {
            $new_driver_id = $driver['id'];
            $reassign = $conn->prepare("UPDATE orders SET driver_id = ?, status = 'Pending' WHERE order_id = ?");
            $reassign->bind_param("ii", $new_driver_id, $order_id);
            $reassign->execute();
            $reassign->close();
        }
    } elseif ($action === 'deliver') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE order_id = ? AND driver_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get assigned orders
$sql = $conn->prepare("SELECT o.order_id, o.order_date, o.quantity, o.total_price, o.payment_method, o.location,
                              o.status, f.food_name, f.image_path, u.firstname AS customer_name 
                       FROM orders o
                       JOIN foods f ON o.food_id = f.food_id
                       JOIN registration u ON o.id = u.id
                       WHERE o.driver_id = ?
                       ORDER BY o.order_date DESC");
$sql->bind_param("i", $user_id);
$sql->execute();
$result = $sql->get_result();

// Load profile image
if (!isset($_SESSION['profile_image'])!== $_SESSION['user_id']) {
    $stmt = $conn->prepare("SELECT profile_image FROM registration WHERE id = ?");
    $stmt->bind_param("i",$user_id);
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
    <title>Driver Dashboard</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; background: #f8f8f8; }
        .header { background: orange; color: white; padding: 15px 20px; font-size: 18px; display: flex; justify-content: space-between; }
        .order-list { padding: 20px; }
        .order-card { background: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 15px; margin-bottom: 20px; display: flex; gap: 15px; }
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
        .dropdown a {
            text-decoration: none;
            color: #333;
            display: block;
            padding: 8px 0;
        }
        .order-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }
        .order-info { flex: 1; }
        .order-info p { margin: 5px 0; }
        .btn { padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .accept-btn {  background: green; color: white; margin-top: 10px; }
        .decline-btn {  background: red; color: white; margin-top: 10px; }
        .delivered-btn { background: blue; color: white; margin-top: 10px; }
        .status { font-weight: bold; }
        .action-buttons { display: flex; justify-content: flex-end; gap: 10px; }
    </style>
</head>
<body>

<div class="header">
   
    🚚 Driver Dashboard

   <div class="user-menu">
     <div class="user-info">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-icon">
       <span><?php echo htmlspecialchars($firstname); ?> ▼</span>
    </div>
        
        <div class="dropdown">
       
             <a href="profile_driver.php">🙍‍♂️ Profile</a>
               <a href="driver.php">🚚 Driver Dashboard</a>
                <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>

<div class="order-list">
    <h2>Your Assigned Orders</h2>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Food">
                <div class="order-info">
                    <h3><?php echo htmlspecialchars($row['food_name']); ?></h3>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['customer_name']); ?></p>
                    <p><strong>Qty:</strong> <?php echo $row['quantity']; ?> | 
                       <strong>Total:</strong> ₱<?php echo number_format($row['total_price'], 2); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                    <p><strong>Ordered:</strong> <?php echo date("F j, Y g:i A", strtotime($row['order_date'])); ?></p>
                    <p><strong>Status:</strong> <span class="status"><?php echo $row['status']; ?> </span></p>
  <form method="POST" onsubmit="return confirm('Confirm this action?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="action" value="accept" class="btn accept-btn">Accept</button>
                                <button type="submit" name="action" value="decline" class="btn decline-btn">Decline</button>
                            </form>

                             <form method="POST" onsubmit="return confirm('Mark this order as delivered?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="action" value="deliver" class="btn delivered-btn">Mark as Delivered</button>
                            </form>

                    <div class="action-buttons">
                        <?php if ($row['status'] === 'Pending'): ?>
                            <form method="POST" onsubmit="return confirm('Confirm this action?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="action" value="accept" class="btn accept-btn">Accept</button>
                                <button type="submit" name="action" value="decline" class="btn decline-btn">Decline</button>
                            </form>
                        <?php elseif ($row['status'] === 'Accepted' && $row['status'] === 'Preparing'): ?>
                            <form method="POST" onsubmit="return confirm('Mark this order as delivered?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="action" value="deliver" class="btn delivered-btn">Mark as Delivered</button>
                            </form>
                       
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders assigned to you right now.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
