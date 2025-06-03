<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include "dbconfig.php";

// Fetch user information from the database using session user_id
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT FirstName, LastName, ContactNumber, Email FROM registration WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

// If user exists, fetch user data
if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $firstname = $user['FirstName'];
    $lastname = $user['LastName'];
    $contactnumber = $user['ContactNumber'];
    $email = $user['Email'];
} else {
    // Handle case when user is not found
    echo "User not found.";
    exit();
}

// Handle request form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the registration data from form
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $role = htmlspecialchars($_POST['role']);
    $message = htmlspecialchars($_POST['message']);
    
    // Validate the input
    if (empty($name) || empty($email) || empty($phone) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Insert the data into the registration_requests table
        $insert_sql = "INSERT INTO registration_requests (request_id, name, email, phone, role, message) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("isssss", $user_id, $name, $email, $phone, $role, $message);
        
        if ($stmt->execute()) {
            $success = "Your registration has been submitted successfully.";
         
        } else {
            $error = "There was an error submitting your registration.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hapag Bayanihan - Registration</title>
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
        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        input[type="submit"] {
            background-color: orange;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #ff7a00;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
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
            <a href="#">🏆 Hapag Donation</a>
            <a href="#">❓ Request</a>
            <a href="index.html">🔓 Logout</a>
        </div>
    </div>
</div>

<div class="content">
    <h1>Submit Your Registration</h1>
    
    <!-- Display error or success messages -->
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
    
    <form action="request.php" method="POST">
        <label for="name">Full Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($firstname . ' ' . $lastname); ?>" required><br>
        
        <label for="email">Email Address:</label><br>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
        
        <label for="phone">Phone Number:</label><br>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($contactnumber); ?>" required><br>
        
        <label for="role">Role:</label><br>
        <select id="role" name="role" required>
            <option value="kitchen_staff">Kitchen Staff</option>
            <option value="driver">Driver</option>
        </select><br>
        
        <label for="message">Optional Message:</label><br>
        <textarea id="message" name="message" rows="4" cols="50"></textarea><br>
        
        <input type="submit" value="Submit Registration">
    </form>
</div>

</body>
</html>
<?php $conn->close(); ?>
