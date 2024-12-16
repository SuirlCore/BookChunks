<?php
include 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);
echo 'test 1<br>';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo 'test 2<br>';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    echo 'test 3<br>';
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    echo 'test 4<br>';
    // Insert the user into the database
    $sql = "INSERT INTO users (userName, pass, email, realFirstName, realLastName) VALUES ('$username', '$hashed_password', '$email', '$firstName', '$lastName');";
    echo 'test 5<br>';
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully.";
        header("Location: login.html"); // Redirect to login page after successful registration
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    echo 'test 6<br>';
    $conn->close();
}
?>