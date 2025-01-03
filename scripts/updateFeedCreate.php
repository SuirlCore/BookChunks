<?php
session_start();
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$userID = $_SESSION['user_id'];
$feedName = $data['feedName'];
$feedDescription = $data['feedDescription'] ?? '';

if (!empty($feedName)) {
    $stmt = $conn->prepare("INSERT INTO feeds (userID, feedName, feedDescription) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userID, $feedName, $feedDescription);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Feed created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create feed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Feed name is required.']);
}
?>
