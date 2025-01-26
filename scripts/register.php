<?php
include 'pdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $autoLogin = isset($_POST['autoLogin']) ? 1 : 0; // Check if "auto login" is selected

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match. Please try again.";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $sql = "INSERT INTO users (userName, pass, email, realFirstName, realLastName, autoLogin) 
            VALUES ('$username', '$hashed_password', '$email', '$firstName', '$lastName', '$autoLogin')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully.";

        // Get the userID for the recently created user
        $userID = $conn->insert_id; // Get the last inserted ID
        
        // If the user selected "auto login", set a cookie
        if ($autoLogin) {
            setcookie('auto_login', $userID, time() + (86400 * 30), "/"); // Set cookie for 30 days
        }
        
        header("Location: ../login.php"); // Redirect to login page after successful registration
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}

?>
