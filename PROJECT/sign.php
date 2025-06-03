
<?php
    include "dbconfig.php";
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["first_name"];
    $lastname = $_POST["last_name"];
    $contact_number = $_POST["contact_number"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password_raw = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $usertype ="user";

    if ($password_raw !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    $password = password_hash($password_raw, PASSWORD_DEFAULT);


        $stmt = $conn->prepare("INSERT INTO registration (FirstName, LastName, ContactNumber, Username,Email, Password, User_type) VALUES (?,?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstname, $lastname, $contact_number, $username,$email, $password,$usertype);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Registration successful!');
                    window.location.href = 'index.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Error: " . addslashes($stmt->error) . "');
                    window.history.back();
                  </script>";
        }

        $stmt->close();
        $conn->close();
    
}
?>
