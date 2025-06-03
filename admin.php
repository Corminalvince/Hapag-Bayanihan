<?php
// Connect to database
include "dbconfig.php";

// Update logic (if AJAX sent a POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['user_type'])) {
    $id = intval($_POST['id']);
    $user_type = trim($_POST['user_type']);
    
    $update_sql = "UPDATE registration SET User_type = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $user_type, $id);
    $stmt->execute();

    echo "success";
    exit;
}

// Fetch users
$sql = "SELECT id, FirstName, LastName, ContactNumber, User_type FROM registration"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapag Bayanihan Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="iframe.css">

    <script>
        function editUserType(cell, id) {
            let currentValue = cell.innerText;
            let newValue = prompt("Enter new User Type:", currentValue);
            
            if (newValue !== null && newValue !== "" && newValue !== currentValue) {
                // Send update request
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText.trim() === "success") {
                        cell.innerText = newValue; // Update the table instantly
                        alert("User Type updated successfully!");
                    } else {
                        alert("Failed to update User Type.");
                    }
                };
                xhr.send(`id=${id}&user_type=${encodeURIComponent(newValue)}`);
            }
        }
    </script>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
        }

        .header {
            background-color: orange;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 40px;
        }

        .logo-text {
            font-size: 1.5em;
            font-weight: bold;
        }

        .nav a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 8px 14px;
            border-radius: 5px;
            transition: transform 0.3s, background-color 0.3s;
        }

        .nav a:hover, .nav a.active {
            background-color: green;
            transform: scale(1.2);
            text-decoration: underline;
        }

        .container {
            margin-top: 40px;
        }

        td.user-type-cell {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="logo.png" alt="Hapag Logo">
            <div class="logo-text">Hapag Bayanihan</div>
        </div>
        <div class="nav">
            <a href="#" class="active">Home</a>
            <a href="message.php">Message</a>
<a href="index.html" onclick="return confirm('Are you sure you want to logout?')">Logout</a>

        </div>
    </div>

    <!-- User Table -->
    <div class="container">
        <h2 class="text-center mb-4">Registered Users</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>User Type (Click to Edit)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["id"]) ?></td>
                            <td><?= htmlspecialchars($row["FirstName"]) ?></td>
                            <td><?= htmlspecialchars($row["LastName"]) ?></td>
                            <td><?= htmlspecialchars($row["ContactNumber"]) ?></td>
                            <td class="user-type-cell" onclick="editUserType(this, <?= $row['id'] ?>)">
                                <?= htmlspecialchars($row["User_type"]) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
$conn->close();
?>
