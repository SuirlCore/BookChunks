<?php
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$feedID = $data['feedID'] ?? null;
$books = $data['books'] ?? [];

if ($feedID && is_array($books)) {
    $conn->begin_transaction();

    try {
        // Clear existing books in the feed
        $stmt = $conn->prepare("DELETE FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();

        // Add books with their new order
        $stmt = $conn->prepare("INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)");
        foreach ($books as $position => $bookID) {
            $stmt->bind_param("iii", $feedID, $bookID, $position);
            $stmt->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Feed updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update feed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>







<?php
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

        // Step 3: Identify books added and removed
        $newBookIDs = array_column($books, 'bookID');
        $addedBooks = array_diff($newBookIDs, $currentBookIDs);
        $removedBooks = array_diff($currentBookIDs, $newBookIDs);

        // Step 4: Add chunkContent for added books
        if (!empty($addedBooks)) {
            $stmt = $conn->prepare("
                SELECT bc.chunkID, bc.chunkContent
                FROM bookChunks bc
                WHERE bc.bookID = ?
            ");

            $insertStmt = $conn->prepare("
                INSERT INTO userFeed (feedID, numInFeed, chunkID, userID)
                VALUES (?, ?, ?, ?)
            ");

            $userID = getUserIdFromFeed($feedID, $conn); // Custom function to get the user ID for the feed
            foreach ($addedBooks as $bookID) {
                $stmt->bind_param("i", $bookID);
                $stmt->execute();
                $result = $stmt->get_result();
                $chunks = $result->fetch_all(MYSQLI_ASSOC);

                foreach ($chunks as $index => $chunk) {
                    $insertStmt->bind_param("iiii", $feedID, $index, $chunk['chunkID'], $userID);
                    $insertStmt->execute();
                }
            }
        }

        // Step 5: Remove chunkContent for removed books
        if (!empty($removedBooks)) {
            $stmt = $conn->prepare("
                DELETE uf
                FROM userFeed uf
                JOIN bookChunks bc ON uf.chunkID = bc.chunkID
                WHERE uf.feedID = ? AND bc.bookID = ?
            ");

            foreach ($removedBooks as $bookID) {
                $stmt->bind_param("ii", $feedID, $bookID);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Feed updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update feed. Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}

/**
 * Function to get the user ID associated with a feed
 */
function getUserIdFromFeed($feedID, $conn) {
    $stmt = $conn->prepare("SELECT userID FROM feeds WHERE feedID = ?");
    $stmt->bind_param("i", $feedID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['userID'];
}
?>
