<?php
// Database connection
include 'pdo.php';

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];

if (isset($_GET['bookID']) && isset($_GET['search'])) {
    $bookID = (int)$_GET['bookID'];
    $search = '%' . $_GET['search'] . '%';

    $stmt = $mysqli->prepare("SELECT chunkID, chunkContent 
                              FROM bookChunks 
                              WHERE bookID = ? AND chunkContent LIKE ?");
    $stmt->bind_param("is", $bookID, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    echo json_encode(['results' => $results]);
}

$mysqli->close();
?>
