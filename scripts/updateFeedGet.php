<?php
session_start();
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userID = $_SESSION['user_id']; // Assume the user is logged in

if ($_GET['data'] === 'feeds') {
    $stmt = $conn->prepare("SELECT feedID, feedName FROM feeds WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $feeds = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['feeds' => $feeds]);
} elseif ($_GET['data'] === 'books') {
    $stmt = $conn->prepare("SELECT textID as bookID, filename FROM fullTexts WHERE owner = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['books' => $books]);
}
?>
