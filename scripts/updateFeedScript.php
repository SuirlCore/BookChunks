<?php
session_start();
$userID = $_SESSION['user_id'];

include 'pdo.php';

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch initial data
if ($_GET['action'] === 'init') {
    $feedsQuery = "SELECT feedID, feedName FROM feeds WHERE userID = ?";
    $stmt = $mysqli->prepare($feedsQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $feedsResult = $stmt->get_result();
    $feeds = $feedsResult->fetch_all(MYSQLI_ASSOC);

    $booksQuery = "SELECT textID, filename FROM fullTexts WHERE owner = ?";
    $stmt = $mysqli->prepare($booksQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $booksResult = $stmt->get_result();
    $books = $booksResult->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    echo json_encode(['feeds' => $feeds, 'books' => $books]);
    exit;
}

// Create a new feed
if ($_POST['action'] === 'createFeed') {
    $data = json_decode(file_get_contents("php://input"), true);
    $feedName = $data['feedName'];
    $feedDescription = $data['feedDescription'] ?? "";

    $createFeedQuery = "INSERT INTO feeds (userID, feedName, feedDescription) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($createFeedQuery);
    $stmt->bind_param("iss", $userID, $feedName, $feedDescription);
    $stmt->execute();

    echo json_encode(['message' => 'Feed created successfully!', 'feedID' => $stmt->insert_id]);
    exit;
}

// Update feed
if ($_GET['action'] === 'updateFeed') {
    $data = json_decode(file_get_contents("php://input"), true);
    $feedID = $data['feedID'];
    $bookOrder = $data['bookOrder'];

    $mysqli->begin_transaction();

    try {
        $deleteBooksQuery = "DELETE FROM booksInFeed WHERE feedID = ?";
        $stmt = $mysqli->prepare($deleteBooksQuery);
        $stmt->bind_param("i", $feedID);
        $stmt->execute();

        $insertBooksQuery = "INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($insertBooksQuery);

        foreach ($bookOrder as $book) {
            $stmt->bind_param("iii", $feedID, $book['bookID'], $book['position']);
            $stmt->execute();
        }

        $deleteUserFeedQuery = "DELETE FROM userFeed WHERE feedID = ?";
        $stmt = $mysqli->prepare($deleteUserFeedQuery);
        $stmt->bind_param("i", $feedID);
        $stmt->execute();

        $selectChunksQuery = "SELECT chunkID FROM bookChunks WHERE bookID = ?";
        $chunksStmt = $mysqli->prepare($selectChunksQuery);

        $insertUserFeedQuery = "INSERT INTO userFeed (feedID, numInFeed, chunkID, userID) VALUES (?, ?, ?, ?)";
        $userFeedStmt = $mysqli->prepare($insertUserFeedQuery);

        $numInFeed = 1;

        foreach ($bookOrder as $book) {
            $chunksStmt->bind_param("i", $book['bookID']);
            $chunksStmt->execute();
            $chunksResult = $chunksStmt->get_result();

            while ($chunk = $chunksResult->fetch_assoc()) {
                $userFeedStmt->bind_param("iiii", $feedID, $numInFeed, $chunk['chunkID'], $userID);
                $userFeedStmt->execute();
                $numInFeed++;
            }
        }

        $mysqli->commit();
        echo json_encode(['message' => 'Feed updated successfully!']);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['message' => 'An error occurred: ' . $e->getMessage()]);
    }

    exit;
}
?>
