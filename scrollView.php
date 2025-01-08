<?php
// scrollView.php

// Database connection
include 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Assume the user is logged in, and we have their userID
$userID = $_SESSION['user_id'];

// Fetch user's feeds
$sql = "SELECT feedID, feedName FROM feeds WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$feeds = $stmt->get_result();

// Handle feed selection
$selectedFeedID = $_POST['feedID'] ?? null;
if ($selectedFeedID) {
    // Get last seen chunk for the selected feed
    $sql = "SELECT lastSeenChunkID FROM userFeedProgress WHERE userID = ? AND feedID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userID, $selectedFeedID);
    $stmt->execute();
    $progressResult = $stmt->get_result();
    $lastSeenChunkID = $progressResult->fetch_assoc()['lastSeenChunkID'] ?? 1;

    // Fetch chunks for the selected feed
    $sql = "SELECT bookChunks.chunkID, bookChunks.chunkContent
            FROM userFeed
            JOIN bookChunks ON userFeed.chunkID = bookChunks.chunkID
            WHERE userFeed.feedID = ?
            ORDER BY userFeed.numInFeed ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selectedFeedID);
    $stmt->execute();
    $chunks = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scroll View</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        #feedSelector {
            margin: 20px;
        }
        #content {
            height: 80vh;
            overflow-y: auto;
            padding: 20px;
            border: 1px solid #ccc;
            margin: 20px;
        }
        .chunk {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <form id="feedSelector" method="POST">
        <label for="feedID">Choose a feed:</label>
        <select name="feedID" id="feedID" onchange="this.form.submit()">
            <option value="">-- Select a feed --</option>
            <?php while ($feed = $feeds->fetch_assoc()): ?>
                <option value="<?= $feed['feedID'] ?>" <?= ($feed['feedID'] == $selectedFeedID) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($feed['feedName']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div id="content">
        <?php if (isset($chunks)): ?>
            <?php while ($chunk = $chunks->fetch_assoc()): ?>
                <div class="chunk" data-chunk-id="<?= $chunk['chunkID'] ?>">
                    <?= nl2br(htmlspecialchars($chunk['chunkContent'])) ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <script>
        const contentDiv = document.getElementById('content');
        const feedID = <?= json_encode($selectedFeedID) ?>;

        contentDiv.addEventListener('scroll', () => {
            const chunks = document.querySelectorAll('.chunk');
            let lastVisibleChunkID = null;

            chunks.forEach(chunk => {
                const rect = chunk.getBoundingClientRect();
                if (rect.top >= 0 && rect.bottom <= window.innerHeight) {
                    lastVisibleChunkID = chunk.dataset.chunkId;
                }
            });

            if (lastVisibleChunkID) {
                fetch('updateProgress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feedID, chunkID: lastVisibleChunkID })
                });
            }
        });
    </script>
</body>
</html>
