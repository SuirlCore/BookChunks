<?php
// Database connection
include 'pdo.php';

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chunkID = (int)$_POST['chunkID'];
    $bookID = (int)$_POST['bookID'];
    $feedID = (int)$_POST['feedID'];

    // Check if a record already exists
    $stmt = $conn->prepare("SELECT * FROM userFeedProgress WHERE userID = ? AND feedID = ?");
    $stmt->bind_param("ii", $userID, $feedID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $mysqli->prepare("UPDATE userFeedProgress SET lastSeenChunkID = ? WHERE userID = ? AND feedID = ?");
        $stmt->bind_param("iii", $chunkID, $userID, $feedID);
        $stmt->execute();
        $message = "Progress updated successfully.";
    } else {
        // Insert new record
        $stmt = $mysqli->prepare("INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userID, $feedID, $chunkID);
        $stmt->execute();
        $message = "Progress created successfully.";
    }

    echo json_encode(['message' => $message]);
}

$conn->close();
?>
