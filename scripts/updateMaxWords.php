<?php
//start session and ensure user is logged in.
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Check if maxWordsPerChunk is provided
if (!isset($_POST['maxWordsPerChunk']) || empty($_POST['maxWordsPerChunk'])) {
    die("Invalid input. Please provide a valid number.");
}

$maxWordsPerChunk = intval($_POST['maxWordsPerChunk']);
$userID = intval($_SESSION['user_id']);

// Validate the input
if ($maxWordsPerChunk < 1) {
    die("Invalid input. The value must be greater than 0.");
}

// Connect to the database using mysqli
include 'pdo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Update the user's maxWordsPerChunk
    $stmt = $conn->prepare("UPDATE users SET maxWordsPerChunk = ? WHERE userID = ?");
    $stmt->bind_param("ii", $maxWordsPerChunk, $userID);

    if ($stmt->execute()) {
        echo "Max Words Per Chunk updated successfully.";
        // Optionally update the session value
        $_SESSION['maxWordsPerChunk'] = $maxWordsPerChunk;
    } else {
        throw new Exception("Failed to update the setting.");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $stmt->close();
    $conn->close();
}
?>
