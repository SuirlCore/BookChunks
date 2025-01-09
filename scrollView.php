<?php
// database connection
include 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];

// Fetch all feeds for the user
$sql = "SELECT feedID, feedName FROM feeds WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$feeds = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Default feed ID
$feedID = isset($_GET['feedID']) ? (int)$_GET['feedID'] : ($feeds[0]['feedID'] ?? 0);

// Fetch last seen chunk
$sql = "SELECT lastSeenChunkID FROM userFeedProgress WHERE userID = ? AND feedID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $userID, $feedID);
$stmt->execute();
$result = $stmt->get_result();
$lastSeen = $result->fetch_assoc();
$lastSeenChunkID = $lastSeen ? $lastSeen['lastSeenChunkID'] : null;
$stmt->close();

// Fetch all chunk IDs for the feed
$sql = "SELECT uf.chunkID, bc.chunkContent FROM userFeed uf JOIN bookChunks bc ON uf.chunkID = bc.chunkID WHERE uf.feedID = ? AND uf.userID = ? ORDER BY uf.numInFeed";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $feedID, $userID);
$stmt->execute();
$result = $stmt->get_result();
$chunks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chunk Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .chunk-container {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
        }
        .navigation {
            display: flex;
            justify-content: space-between;
        }
        .navigation button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .feed-selector {
            margin-bottom: 20px;
        }
    </style>
    <script>
        let chunks = <?php echo json_encode($chunks); ?>;
        let currentIndex = <?php echo $lastSeenChunkID ? array_search($lastSeenChunkID, array_column($chunks, 'chunkID')) : 0; ?>;

        function loadChunk(index) {
            if (index < 0 || index >= chunks.length) return;

            document.getElementById('chunkContent').innerText = chunks[index].chunkContent;
            currentIndex = index;

            // Update last seen chunk ID in the database
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'updateProgress',
                    userID: <?php echo $userID; ?>,
                    feedID: <?php echo $feedID; ?>,
                    lastSeenChunkID: chunks[index].chunkID
                })
            });
        }

        function prevChunk() {
            loadChunk(currentIndex - 1);
        }

        function nextChunk() {
            loadChunk(currentIndex + 1);
        }

        async function updateChunksForFeed(feedID) {
            const response = await fetch(`?feedID=${feedID}`);
            const parser = new DOMParser();
            const htmlDoc = parser.parseFromString(await response.text(), 'text/html');
            const newChunks = JSON.parse(htmlDoc.querySelector('script').innerText.match(/let chunks = (.*?);/)[1]);

            chunks = newChunks;
            currentIndex = 0;
            loadChunk(currentIndex);
        }

        window.onload = () => {
            loadChunk(currentIndex);
        };
    </script>
</head>
<body>
    <div class="feed-selector">
        <form method="GET" action="">
            <label for="feedID">Select Feed:</label>
            <select name="feedID" id="feedID" onchange="updateChunksForFeed(this.value)">
                <?php foreach ($feeds as $feed): ?>
                    <option value="<?php echo $feed['feedID']; ?>" <?php echo $feed['feedID'] == $feedID ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($feed['feedName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="chunk-container" id="chunkContent">Loading...</div>
    <div class="navigation">
        <button onclick="prevChunk()">Previous</button>
        <button onclick="nextChunk()">Next</button>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'updateProgress' && isset($data['userID'], $data['feedID'], $data['lastSeenChunkID'])) {
        $sql = "INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE lastSeenChunkID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $data['userID'], $data['feedID'], $data['lastSeenChunkID'], $data['lastSeenChunkID']);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
?>
