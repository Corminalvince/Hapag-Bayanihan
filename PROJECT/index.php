<?php
session_start();
include "dbconfig.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare and bind to prevent SQL injection
    $stmt = $conn->prepare("SELECT ID, FirstName, LastName, ContactNumber, Password, User_type FROM registration WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Fetch data
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row["Password"])) {
            // Start session and store user data
            $_SESSION["user_id"] = $row["ID"];
            $_SESSION["username"] = $username;
            $_SESSION["firstname"] = $row["FirstName"];
            $_SESSION["lastname"] = $row["LastName"];
            $_SESSION["contact"] = $row["ContactNumber"];
            $_SESSION["user_type"] = $row["User_type"];

            // Redirect based on user type
            if ($row["User_type"] === "admin") {
                    echo "<script>
                            alert('Admin login successful!');
                            window.location.href = 'admin.php';
                          </script>";
            } elseif ($row["User_type"] === "driver") {
                echo "<script>
                        alert('Driver login successful!');
                        window.location.href = 'driver.php';
                      </script>";
            } elseif ($row["User_type"] === "kitchen") {
                echo "<script>
                        alert('Kitchen staff login successful!');
                        window.location.href = 'kitchen.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Login successful!');
                        window.location.href = 'main.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Invalid password. Please try again.');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Username not found. Please register.');
                window.history.back();
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
