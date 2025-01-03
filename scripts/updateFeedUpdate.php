<?php
session_start();
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$userID = $_SESSION['user_id'];
$feedID = $data['feedID'];
$bookIDs = $data['bookIDs'];

if (!empty($feedID) && !empty($bookIDs)) {
    foreach ($bookIDs as $index => $bookID) {
        $stmt = $conn->prepare("INSERT INTO userFeed (feedID, numInFeed, chunkID, userID) 
                                SELECT ?, ?, chunkID, ? FROM bookChunks WHERE bookID = ?");
        $stmt->bind_param("iiii", $feedID, $index, $userID, $bookID);
        $stmt->execute();
    }
    echo json_encode(['message' => 'Books added to feed successfully.']);
} else {
    echo json_encode(['message' => 'Invalid input.']);
}
?>
