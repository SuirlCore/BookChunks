<?php
require 'pdo.php';

session_start();
$userID = $_SESSION['user_id'] ?? null;

// Validate the user ID
if (!$userID) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$dataType = $_GET['data'] ?? ''; // Get the 'data' parameter

// Handle requests based on 'data' parameter
if ($dataType === 'feeds') {
    // Fetch feeds for the user
    $stmt = $conn->prepare("SELECT feedID, feedName FROM feeds WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $feeds = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['feeds' => $feeds]);

} elseif ($dataType === 'books') {
    // Fetch books for the user
    $stmt = $conn->prepare("SELECT textID AS bookID, filename FROM fullTexts WHERE owner = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['books' => $books]);

} elseif (isset($_GET['feedID'])) {
    // Fetch books for a specific feed
    $feedID = $_GET['feedID'];
    $stmt = $conn->prepare("
        SELECT fb.bookID, ft.filename
        FROM booksInFeed fb
        JOIN fullTexts ft ON fb.bookID = ft.textID
        JOIN feeds f ON fb.feedID = f.feedID
        WHERE fb.feedID = ? AND f.userID = ?
        ORDER BY fb.position ASC
    ");
    $stmt->bind_param("ii", $feedID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $booksInFeed = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['feedBooks' => $booksInFeed]);

} else {
    // Invalid request
    echo json_encode(['error' => 'Invalid request']);
}
?>
