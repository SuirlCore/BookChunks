<?php
session_start();
$userID = $_SESSION['user_id']; // Assume user is logged in and ID is stored in session

include 'pdo.php';

$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch available feeds and last seen chunk
if ($_GET['action'] === 'fetchFeeds') {
    $feedsQuery = "SELECT feedID, feedName FROM feeds WHERE userID = ?";
    $stmt = $mysqli->prepare($feedsQuery);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $feedsResult = $stmt->get_result();
    $feeds = $feedsResult->fetch_all(MYSQLI_ASSOC);

    $lastSeenQuery = "SELECT lastSeenChunkID FROM userFeedProgress WHERE userID = ? AND feedID = ?";
    $stmt = $mysqli->prepare($lastSeenQuery);
    $stmt->bind_param("ii", $userID, $feeds[0]['feedID']);
    $stmt->execute();
    $lastSeenResult = $stmt->get_result();
    $lastSeenChunkID = $lastSeenResult->fetch_assoc()['lastSeenChunkID'] ?? null;

    echo json_encode(['feeds' => $feeds, 'lastSeenChunkID' => $lastSeenChunkID]);
    exit;
}

// Fetch chunks for a feed
if ($_GET['action'] === 'fetchChunks') {
    $feedID = $_GET['feedID'];
    $chunksQuery = "SELECT chunkID, chunkContent FROM bookChunks 
                    JOIN userFeed ON bookChunks.chunkID = userFeed.chunkID 
                    WHERE userFeed.feedID = ? AND userFeed.userID = ? 
                    ORDER BY userFeed.numInFeed ASC";
    $stmt = $mysqli->prepare($chunksQuery);
    $stmt->bind_param("ii", $feedID, $userID);
    $stmt->execute();
    $chunksResult = $stmt->get_result();
    $chunks = $chunksResult->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['chunks' => $chunks]);
    exit;
}

// Update last seen chunk
if ($_GET['action'] === 'updateLastSeenChunk') {
    $data = json_decode(file_get_contents("php://input"), true);
    $feedID = $data['feedID'];
    $chunkID = $data['chunkID'];

    $updateQuery = "INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID)
                    VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lastSeenChunkID = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param("iiii", $userID, $feedID, $chunkID, $chunkID);
    $stmt->execute();

    echo json_encode(['message' => 'Last seen chunk updated']);
    exit;
}
?>
