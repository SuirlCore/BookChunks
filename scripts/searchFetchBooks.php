<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

// Database connection
include 'pdo.php';

$userID = $_SESSION['user_id'];

if (isset($_GET['feedID'])) {
    $feedID = (int)$_GET['feedID'];

    $stmt = $conn->prepare("SELECT ft.textID, ft.filename 
                              FROM booksInFeed bif
                              JOIN fullTexts ft ON bif.bookID = ft.textID 
                              WHERE bif.feedID = ?");
    $stmt->bind_param("i", $feedID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    echo json_encode(['books' => $books]);
}

$conn->close();
?>
