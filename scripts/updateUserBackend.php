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
    $backgroundColor = $_POST['backgroundColor'];
    $lineHeight = $_POST['lineHeight'];
    $buttonColor = $_POST['buttonColor'];
    $buttonHoverColor = $_POST['buttonHoverColor'];
    $buttonTextColor = $_POST['buttonTextColor'];
    $maxWordsPerChunk = intval($_POST['maxWordsPerChunk']);
    $textToVoice = intval($_POST['textToVoice']);
    $autoLogin = intval($_POST['autoLogin']);
    $highlightColor = $_POST['hilightColor'];

    // Prepare the update query
    $query = "UPDATE users SET 
        userName = ?, 
        realFirstName = ?, 
        realLastName = ?, 
        email = ?, 
        fontSize = ?, 
        fontColor = ?, 
        backgroundColor = ?, 
        lineHeight = ?, 
        buttonColor = ?, 
        buttonHoverColor = ?, 
        buttonTextColor = ?, 
        highlightColor = ?, 
        maxWordsPerChunk = ?, 
        textToVoice = ?, 
        autoLogin = ?";

    // If a new password is provided, include it in the query
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query .= ", pass = ?";
    }

    $query .= " WHERE userID = ?";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters based on whether a password was provided
    if (!empty($_POST['password'])) {
        $stmt->bind_param(
            "ssssssssssssiiii",
            $userName,
            $realFirstName,
            $realLastName,
            $email,
            $fontSize,
            $fontColor,
            $backgroundColor,
            $lineHeight,
            $buttonColor,
            $buttonHoverColor,
            $buttonTextColor,
            $highlightColor,
            $maxWordsPerChunk,
            $textToVoice,
            $autoLogin,
            $password,
            $userID
        );
    } else {
        $stmt->bind_param(
            "ssssssssssssiii",
            $userName,
            $realFirstName,
            $realLastName,
            $email,
            $fontSize,
            $fontColor,
            $backgroundColor,
            $lineHeight,
            $buttonColor,
            $buttonHoverColor,
            $buttonTextColor,
            $highlightColor,
            $maxWordsPerChunk,
            $textToVoice,
            $autoLogin,
            $userID
        );
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo "Profile and settings updated successfully.";
    } else {
        echo "Error updating profile and settings: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}


    if ($profileResult === TRUE && $settingsResult === TRUE) {
        $_SESSION['message'] = "Profile and settings updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating data: " . $conn->error;
    }

    $conn->close();

    $_SESSION['fontSize'] = $fontSize;
    $_SESSION['fontColor'] = $fontColor;
    $_SESSION['backgroundColor'] = $backgroundColor;

    $_SESSION['lineHeight'] = $lineHeight;
    $_SESSION['highlightColor'] = $highlightColor;
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
