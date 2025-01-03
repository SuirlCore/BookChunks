<?php
require 'pdo.php';

// Prevent PHP warnings/notices from breaking JSON output
ini_set('display_errors', 0);
header('Content-Type: application/json');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$feedID = $data['feedID'] ?? null;
$books = $data['books'] ?? [];

if ($feedID && is_array($books)) {
    $conn->begin_transaction();

    try {
        // Step 1: Get current books in the feedBooks table
        $stmt = $conn->prepare("SELECT bookID FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentBooks = $result->fetch_all(MYSQLI_ASSOC);
        $currentBookIDs = array_column($currentBooks, 'bookID');

        // Step 2: Update the feedBooks table
        $stmt = $conn->prepare("DELETE FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)");
        foreach ($books as $position => $book) {
            $stmt->bind_param("iii", $feedID, $book['bookID'], $position);
            $stmt->execute();
        }

        // Step 3: Commit changes
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Feed updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update feed. Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

exit;
?>
