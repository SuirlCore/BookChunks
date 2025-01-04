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
        // Step 1: Get current books in the feed
        $stmt = $conn->prepare("SELECT bookID FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentBooks = $result->fetch_all(MYSQLI_ASSOC);
        $currentBookIDs = array_column($currentBooks, 'bookID');

        // Step 2: Remove chunkContent for books that are no longer in the feed
        $removedBooks = array_diff($currentBookIDs, array_column($books, 'bookID'));
        if (!empty($removedBooks)) {
            $placeholders = implode(',', array_fill(0, count($removedBooks), '?'));
            $deleteStmt = $conn->prepare("
                DELETE uf 
                FROM userFeed uf 
                JOIN bookChunks bc ON uf.chunkID = bc.chunkID 
                WHERE uf.feedID = ? AND bc.bookID IN ($placeholders)
            ");
            $params = array_merge([$feedID], $removedBooks);
            $deleteStmt->bind_param(str_repeat('i', count($params)), ...$params);
            $deleteStmt->execute();
        }

        // Step 3: Delete existing books from booksInFeed
        $stmt = $conn->prepare("DELETE FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();

        // Step 4: Insert updated books into booksInFeed
        $stmt = $conn->prepare("INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)");
        foreach ($books as $position => $book) {
            $stmt->bind_param("iii", $feedID, $book['bookID'], $position);
            $stmt->execute();
        }

        // Step 5: Add chunkContent for the newly added books in the correct order
        $newBooks = array_diff(array_column($books, 'bookID'), $currentBookIDs);
        if (!empty($newBooks)) {
            // Fetch books ordered by position from booksInFeed
            $fetchBooksStmt = $conn->prepare("
                SELECT b.bookID, b.position 
                FROM booksInFeed b 
                WHERE b.feedID = ? 
                ORDER BY b.position
            ");
            $fetchBooksStmt->bind_param("i", $feedID);
            $fetchBooksStmt->execute();
            $result = $fetchBooksStmt->get_result();
            $orderedBooks = $result->fetch_all(MYSQLI_ASSOC);

            $chunkStmt = $conn->prepare("SELECT chunkID FROM bookChunks WHERE bookID = ?");
            $insertStmt = $conn->prepare("
                INSERT INTO userFeed (feedID, numInFeed, chunkID, userID) 
                VALUES (?, ?, ?, ?)
            ");

            // Get userID for the feed
            $userStmt = $conn->prepare("SELECT userID FROM feeds WHERE feedID = ?");
            $userStmt->bind_param("i", $feedID);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $userID = $userResult->fetch_assoc()['userID'];

            $numInFeed = 0;
            foreach ($orderedBooks as $book) {
                $chunkStmt->bind_param("i", $book['bookID']);
                $chunkStmt->execute();
                $chunks = $chunkStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                foreach ($chunks as $chunk) {
                    $chunkID = $chunk['chunkID'];
                    $insertStmt->bind_param("iiii", $feedID, $numInFeed, $chunkID, $userID);
                    $insertStmt->execute();
                    $numInFeed++;
                }
            }
        }

        // Step 6: Commit changes
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Feed updated successfully with chunkContent managed in correct order.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update feed. Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

exit;
?>
