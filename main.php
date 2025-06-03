<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include "dbconfig.php";

// Fetch food items for slideshow (with carinderia_name)
$slideshow_sql = "SELECT food_id, food_name, price, image_path, carinderia_name 
                  FROM foods 
                  WHERE quantity > 0 
                  ORDER BY created_at DESC 
                  LIMIT 5";
$slideshow_result = $conn->query($slideshow_sql);

// Fetch food items for grid (with carinderia_name)
$grid_sql = "SELECT food_id, food_name, price, image_path, carinderia_name 
             FROM foods 
             WHERE quantity > 0 
             ORDER BY created_at DESC";

$grid_result = $conn->query($grid_sql);
$search = $_GET['search'] ?? '';
$search_param = "%$search%";

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT food_id, food_name, price, image_path, carinderia_name 
                            FROM foods 
                            WHERE quantity > 0 AND food_name LIKE ? 
                            ORDER BY created_at DESC");
    $stmt->bind_param("s", $search_param);
} else {
    $stmt = $conn->prepare("SELECT food_id, food_name, price, image_path, carinderia_name 
                            FROM foods 
                            WHERE quantity > 0 
                            ORDER BY created_at DESC");
}
$stmt->execute();
$grid_result = $stmt->get_result();
// Execute the query once and store results in an array
$foods = [];
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $foods[] = $row;
}


// Load profile image
if (!isset($_SESSION['profile_image']) !==$_SESSION['user_id'] ) {
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
    <title>Hapag Bayanihan - Food List</title>
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
  

.profile-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}
      .user-info {
    display: flex;
    align-items: center;
    gap: 10px;
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
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .food-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .food-card img {
            max-width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 8px;
        }
        .food-card h3 {
            margin: 5px 0;
            font-size: 18px;
        }
        .food-card p {
            margin: 0;
            font-weight: bold;
        }.food-grids {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
         .food-cards {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
            text-decoration: none;
            color: inherit;
            display: block;
        }
         .food-grids { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .food-cards { border: 1px solid #eee; border-radius: 10px; padding: 10px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; color: inherit; display: block; }
        .food-cards img { max-width: 100%; height: 250px; object-fit: cover; border-radius: 8px; }
        .food-cards h3 { margin: 5px 0; font-size: 18px; }
        .food-cards p { margin: 0; font-weight: bold; }
        .carinderia-name {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
        }
        .slideshow-container {
            position: relative;
            width: 1000px;
            margin: 30px auto;
        }
        .mySlides {
            display: none;
        }
        .fade {
            animation: fade 1.5s ease-in-out;
        }
        @keyframes fade {
            from {opacity: .4}
            to {opacity: 1}
        }
        .dot {
            height: 10px;
            width: 10px;
            margin: 0 4px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .active-dot {
            background-color: orange;
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
      <div class="user-info">
        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="profile-icon">
        <span><?php echo htmlspecialchars($_SESSION['firstname'] ?? 'User'); ?> ▼</span>
    </div>   <div class="dropdown">
            <a href="profile.php">🙍‍♂️ Profile</a>
            <a href="view_order.php">📦 Orders & Reordering</a>
            <a href="available_vouchers.php">🎟 Vouchers</a>
            <a href="view_catering.php">🏆 Hapag Catering</a>
            <a href="request.php">❓ Request</a>
            <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>


<div class="content">
    <h1>Featured Food from Carinderias</h1>
    <div class="slideshow-container">
        <?php 
        $slideIndex = 0;
        if ($slideshow_result && $slideshow_result->num_rows > 0): 
            while ($row = $slideshow_result->fetch_assoc()):
                $slideIndex++;
        ?>
            <div class="mySlides fade">
                <a class="food-cards" href="food_detail.php?id=<?php echo $row['food_id']; ?>">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Food Image">
                    <p class="carinderia-name"><?php echo htmlspecialchars($row['carinderia_name']); ?></p>
                    <h3><?php echo htmlspecialchars($row['food_name']); ?></h3>
                    <p>₱<?php echo number_format($row['price'], 2); ?></p>
                </a>
            </div>
        <?php endwhile; endif; ?>
    </div>
    <br>
    <div style="text-align:center">
        <?php for ($i = 1; $i <= $slideIndex; $i++): ?>
            <span class="dot"></span>
        <?php endfor; ?>
    </div>
</div>
<form method="GET" action="" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search food..." value="<?php echo htmlspecialchars($search); ?>" 
           style="padding: 10px; width: 1000px;margin: 20px; height: 30px; border-radius: 5px; border: 1px solid #ccc; ">
    <button type="submit" style="padding: 8px 12px; background-color: orange; color: white; border: none; background-size: cover; border-radius: 5px;">Search</button>
</form>
<div class="content">
    <h1>Available Food from Carinderias</h1>
    <div class="food-grid">
        <?php if ($grid_result && $grid_result->num_rows > 0): ?>
            <?php while ($row = $grid_result->fetch_assoc()): ?>
                <a class="food-card" href="food_detail.php?id=<?php echo $row['food_id']; ?>">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Food Image">
                    <p class="carinderia-name"><?php echo htmlspecialchars($row['carinderia_name']); ?></p>
                    <h3><?php echo htmlspecialchars($row['food_name']); ?></h3>
                    <p>₱<?php echo number_format($row['price'], 2); ?></p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No food items available at the moment.</p>
        <?php endif; ?>
    </div>
</div>
<div class="content">
    <h1>Select Food for Catering</h1>
    <form method="POST" action="catering_request.php">
    <div class="food-grids">
    <?php if (!empty($foods)): ?>
        <?php foreach ($foods as $row): ?>
            <div class="food-cards">
                <input type="checkbox" name="selected_foods[]" value="<?php echo $row['food_id']; ?>">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Food Image">
                <p class="carinderia-name"><?php echo htmlspecialchars($row['carinderia_name']); ?></p>
                <h3><?php echo htmlspecialchars($row['food_name']); ?></h3>
                <p>₱<?php echo number_format($row['price'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No food items available at the moment.</p>
    <?php endif; ?>
</div>
        <div style="margin-top: 20px; text-align: center;">
            <button type="submit" style="padding: 10px 20px; background-color: green; color: white; border: none; border-radius: 5px;">
                Submit Catering Request
            </button>
        </div>
    </form>
</div>
<script>
let slideIndex = 0;
showSlides();

function showSlides() {
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");

    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }

    slideIndex++;
    if (slideIndex > slides.length) { slideIndex = 1 }    

    for (let i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active-dot");
    }

    if (slides.length > 0) {
        slides[slideIndex - 1].style.display = "block";  
        dots[slideIndex - 1].classList.add("active-dot");
    }

    setTimeout(showSlides, 3000); // 3 seconds
}
</script>

</body>
</html>
<?php $conn->close(); ?>
