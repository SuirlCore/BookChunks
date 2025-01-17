<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

include 'pdo.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookID = intval($_POST['book_id']);
    $userID = $_SESSION['user_id'];

    $dbConn = new mysqli($servername, $username, $password, $dbname);
    if ($dbConn->connect_error) {
        die("Connection failed: " . $dbConn->connect_error);
    }

    // Verify that the book belongs to the user
    $stmt = $dbConn->prepare("SELECT textID as id FROM fullTexts WHERE textID = ? AND owner = ?");
    $stmt->bind_param("ii", $bookID, $userID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();

        // Check for references in userFeeds, booksInFeed, or userFeedProgress
        $stmt = $dbConn->prepare("
        SELECT COUNT(*) FROM (
        -- Records in userFeed where chunkID corresponds to the bookID in bookChunks
        SELECT uf.feedID AS sourceID, 'userFeed' AS sourceTable
        FROM userFeed uf
        INNER JOIN bookChunks bc ON uf.chunkID = bc.chunkID
        WHERE bc.bookID = ?

        UNION

        -- Records in booksInFeed where bookID matches
        SELECT bif.feedID AS sourceID, 'booksInFeed' AS sourceTable
        FROM booksInFeed bif
        WHERE bif.bookID = ?

        UNION

        -- Records in userFeedProgress where lastSeenChunkID corresponds to the bookID in bookChunks
        SELECT ufp.feedID AS sourceID, 'userFeedProgress' AS sourceTable
        FROM userFeedProgress ufp
        INNER JOIN bookChunks bc ON ufp.lastSeenChunkID = bc.chunkID
        WHERE bc.bookID = ?
        ) AS referencedBooks;
        ");
        $stmt->bind_param("iii", $bookID, $bookID, $bookID);
        $stmt->execute();
        $stmt->bind_result($referenceCount);
        $stmt->fetch();
        $stmt->close();

        if ($referenceCount > 0) {
            $dbConn->close();
            die("This book is currently referenced in a feed. Please remove the book from all feeds before deleting.");
        }

        // Delete from bookChunks table
        $stmt = $dbConn->prepare("DELETE FROM bookChunks WHERE bookID = ?");
        $stmt->bind_param("i", $bookID);
        $stmt->execute();

        // Delete from fullTexts table
        $stmt = $dbConn->prepare("DELETE FROM fullTexts WHERE textID = ?");
        $stmt->bind_param("i", $bookID);
        $stmt->execute();

        $stmt->close();
        $dbConn->close();

        header("Location: ../uploadPage.php");
        exit();
    } else {
        $stmt->close();
        $dbConn->close();
        die("Unauthorized action or book does not exist.");
    }
} else {
    die("Invalid request.");
}
?>
