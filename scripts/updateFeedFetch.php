<?php
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$feedID = $_GET['feedID'] ?? null;

if ($feedID) {
    $stmt = $conn->prepare("
        SELECT fb.bookID, ft.filename
        FROM booksInFeed fb
        JOIN fullTexts ft ON fb.bookID = ft.textID
        WHERE fb.feedID = ?
        ORDER BY fb.position ASC
    ");
    $stmt->bind_param("i", $feedID);
    $stmt->execute();
    $result = $stmt->get_result();
    $booksInFeed = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['booksInFeed' => $booksInFeed]);
} else {
    echo json_encode(['error' => 'Feed ID not provided']);
}
?>
