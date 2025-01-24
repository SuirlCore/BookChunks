<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
include 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfileAndSettings'])) {
    $userID = intval($_POST['userID']);
    $userName = $_POST['userName'];
    $realFirstName = $_POST['realFirstName'];
    $realLastName = $_POST['realLastName'];
    $email = $_POST['email'];
    $fontSize = $_POST['fontSize'];
    $fontColor = $_POST['fontColor'];
    $fontSelect = $_POST['fontSelect'];
    $backgroundColor = $_POST['backgroundColor'];
    $lineHeight = $_POST['lineHeight'];
    $buttonColor = $_POST['buttonColor'];
    $buttonHoverColor = $_POST['buttonHoverColor'];
    $buttonTextColor = $_POST['buttonTextColor'];
    $maxWordsPerChunk = intval($_POST['maxWordsPerChunk']);
    $textToVoice = intval($_POST['textToVoice']);
    $autoLogin = intval($_POST['autoLogin']);
    $highlightColor = $_POST['highlightColor'];
    $highlightingToggle = $_POST['highlightingToggle'];

    // Prepare the update query
    $query = "UPDATE users SET 
        userName = ?, 
        realFirstName = ?, 
        realLastName = ?, 
        email = ?, 
        fontSelect = ?,
        fontSize = ?, 
        fontColor = ?, 
        backgroundColor = ?, 
        lineHeight = ?, 
        buttonColor = ?, 
        buttonHoverColor = ?, 
        buttonTextColor = ?, 
        highlightColor = ?, 
        highlightingToggle = ?,
        maxWordsPerChunk = ?, 
        textToVoice = ?, 
        autoLogin = ?";

    // If a new password is provided, include it in the query
    $params = [
        $userName,
        $realFirstName,
        $realLastName,
        $email,
        $fontSelect,
        $fontSize,
        $fontColor,
        $backgroundColor,
        $lineHeight,
        $buttonColor,
        $buttonHoverColor,
        $buttonTextColor,
        $highlightColor,
        $highlightingToggle,
        $maxWordsPerChunk,
        $textToVoice,
        $autoLogin
    ];

    $types = "sssssssssssssiiii"; // Data types for the bind_param method

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", pass = ?";
        $params[] = $password; // Add the password to the parameters
        $types .= "s"; // Add the type for the password
    }

    $query .= " WHERE userID = ?";
    $params[] = $userID; // Add the userID to the parameters
    $types .= "i"; // Add the type for the userID

    // Prepare the statement
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Profile and settings updated successfully.";
    } else {
        echo "Error updating profile and settings: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    // Set or remove the autoLogin cookie based on the user's choice
    if ($autoLogin == '1') {
        setcookie('auto_login', $userID, time() + 3600 * 24 * 30, "/"); // Cookie expires in 30 days
    } else {
        setcookie('auto_login', '', time() - 3600, "/"); // Expire the cookie immediately
    }

    // Update session variables
    $_SESSION['fontSize'] = $fontSize;
    $_SESSION['fontColor'] = $fontColor;
    $_SESSION['fontSelect'] = $fontSelect;
    $_SESSION['backgroundColor'] = $backgroundColor;
    $_SESSION['lineHeight'] = $lineHeight;
    $_SESSION['highlightColor'] = $highlightColor;
    $_SESSION['highlightingToggle'] = $highlightingToggle;
    $_SESSION['buttonColor'] = $buttonColor;
    $_SESSION['buttonHoverColor'] = $buttonHoverColor;
    $_SESSION['buttonTextColor'] = $buttonTextColor;
    $_SESSION['maxWordsPerChunk'] = $maxWordsPerChunk;
    $_SESSION['textToVoice'] = $textToVoice;
    $_SESSION['autoLogin'] = $autoLogin;

    // Redirect back to the original page
    header("Location: ../updateUser.php");
    exit();
}
?>
