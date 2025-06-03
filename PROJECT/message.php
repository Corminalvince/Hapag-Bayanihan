<?php
// Connect to database
include "dbconfig.php";

// Fetch registration requests (only name and message)
$message_sql = "SELECT name, message FROM registration_requests";
$message_result = $conn->query($message_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapag Bayanihan Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="iframe.css">

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
            <a href="admin.php" class="active">Home</a>
            <a href="message.php">Message</a>
            <a href="index.html" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
        </div>
    </div>

    <!-- Registration Requests (Displaying only Name and Message) -->
    <div class="container">
        <h2 class="text-center mb-4">Registration Requests</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($message_result->num_rows > 0): ?>
                    <?php while($row = $message_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["name"]) ?></td>
                            <td><?= htmlspecialchars($row["message"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">No registration requests found.</td>
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
