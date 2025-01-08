<?php
// Database connection
include 'pdo.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedID = $_POST['feedID'] ?? null;

    if (!$feedID) {
        echo json_encode(['success' => false, 'message' => 'Feed ID is required.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Step 1: Delete all records from the userFeed table for the specific feed
        $deleteQuery = "DELETE FROM userFeed WHERE feedID = ? AND userID = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $feedID, $userID);
        $stmt->execute();

        // Step 2: Retrieve all chunkIDs from the bookChunks table for all books in the current feed
        $chunksQuery = "
            SELECT c.chunkID
            FROM bookChunks c
            JOIN booksInFeed b ON c.bookID = b.bookID
            WHERE b.feedID = ?";
        $stmt = $conn->prepare($chunksQuery);
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $result = $stmt->get_result();

        $chunks = [];
        while ($row = $result->fetch_assoc()) {
            $chunks[] = $row['chunkID'];
        }

        // Step 3: Insert each chunkID into userFeed with the next unique numInFeed
        $insertQuery = "INSERT INTO userFeed (feedID, numInFeed, chunkID, userID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);

        $numInFeed = 1;
        foreach ($chunks as $chunkID) {
            $stmt->bind_param("iiii", $feedID, $numInFeed, $chunkID, $userID);
            $stmt->execute();
            $numInFeed++;
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error during synchronization: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Synchronization failed.']);
    }
}
?>
