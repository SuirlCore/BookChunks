<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
include 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfileAndSettings'])) {
    $userID = intval($_POST['userID']);
    $userName = $conn->real_escape_string($_POST['userName']);
    $realFirstName = $conn->real_escape_string($_POST['realFirstName']);
    $realLastName = $conn->real_escape_string($_POST['realLastName']);
    $email = $conn->real_escape_string($_POST['email']);
    $fontSize = $conn->real_escape_string($_POST['fontSize']);
    $fontColor = $conn->real_escape_string($_POST['fontColor']);
    $backgroundColor = $conn->real_escape_string($_POST['backgroundColor']);
    
    $updateProfileQuery = "UPDATE users SET 
        userName = '$userName', 
        realFirstName = '$realFirstName', 
        realLastName = '$realLastName', 
        email = '$email' 
        WHERE userID = $userID";

    // If a new password is provided, hash and update it
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $updateProfileQuery = "UPDATE users SET 
            userName = '$userName', 
            pass = '$password', 
            realFirstName = '$realFirstName', 
            realLastName = '$realLastName', 
            email = '$email' 
            WHERE userID = $userID";
    }

    // Settings update query
    $updateSettingsQuery = "UPDATE users SET 
        fontSize = '$fontSize', 
        fontColor = '$fontColor', 
        backgroundColor = '$backgroundColor' 
        WHERE userID = $userID";

    $profileResult = $conn->query($updateProfileQuery);
    $settingsResult = $conn->query($updateSettingsQuery);

    if ($profileResult === TRUE && $settingsResult === TRUE) {
        $_SESSION['message'] = "Profile and settings updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating data: " . $conn->error;
    }

    $conn->close();

    $_SESSION['fontSize'] = $fontSize;
    $_SESSION['fontColor'] = $fontColor;
    $_SESSION['backgroundColor'] = $backgroundColor;

    // Redirect back to the original page
    header("Location: ../updateUser.php");
    exit();
}

?>