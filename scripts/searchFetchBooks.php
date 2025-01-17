<?php
// Database connection
include 'pdo.php';

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];

// Connect to database
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['feedID'])) {
    $feedID = (int)$_GET['feedID'];

    $stmt = $mysqli->prepare("SELECT ft.textID, ft.filename 
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

$mysqli->close();
?>
