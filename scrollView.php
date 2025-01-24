<?php
// database connection
include 'scripts/pdo.php';

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}
$userID = $_SESSION['user_id'];

// Fetch user preferences for text and background
$sql = "SELECT fontSize, fontColor, backgroundColor FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$choices = $result->fetch_assoc();
$fontSizeChoice = $choices['fontSize'];
$fontColorChoice = $choices['fontColor'];
$backgroundColorChoice = $choices['backgroundColor'];
$stmt->close();

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
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh; 
            overflow: hidden;  
            color: <?= htmlspecialchars($fontColorChoice); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($backgroundColorChoice); ?>; /* Dynamic background color */
    
        }

        .chunk-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fff;
            background-color: <?= htmlspecialchars($backgroundColorChoice); ?>; /* Dynamic background color */
            font-size: <?= htmlspecialchars($fontSizeChoice); ?>; /* Dynamic font size */
            line-height: <?= htmlspecialchars($_SESSION['lineHeight']); ?>;
        }

        .navigation {
            display: flex; 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            height: 60px; 
            background: #A9A9A9; 
        }

        .navigation button {
            flex: 1; 
            color: <?= htmlspecialchars($_SESSION['buttonTextColor']); ?>;
            font-size: 18px; 
            font-weight: bold; 
            border: none;
            background: <?= htmlspecialchars($_SESSION['buttonColor']); ?>; 
            cursor: pointer;
            transition: background 0.2s ease-in-out; 
        }

        .navigation button:hover {
            background: <?= htmlspecialchars($_SESSION['buttonHoverColor']); ?>; 
        }

        .navigation button:disabled { 
            background: #d6d6d6; 
            color: #aaa; 
            cursor: not-allowed; 
        }

    </style>
    <script>
        let chunks = <?php echo json_encode($chunks); ?>;
        let currentIndex = <?php echo $lastSeenChunkID ? array_search($lastSeenChunkID, array_column($chunks, 'chunkID')) : 0; ?>;

        // Added logic to enable/disable buttons
        function loadChunk(index) {
            if (index < 0 || index >= chunks.length) return;

            document.getElementById('chunkContent').innerText = chunks[index].chunkContent;
            currentIndex = index;

            // Update button states (Added)
            document.getElementById('prevButton').disabled = index === 0; /* Added */
            document.getElementById('nextButton').disabled = index === chunks.length - 1; /* Added */

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

        window.onload = () => {
            loadChunk(currentIndex);
        };
    </script>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="feed-selector">
        <form method="GET" action="">
            <label for="feedID">Select Feed:</label>
            <select name="feedID" id="feedID" onchange="this.form.submit()">
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
        <button id="prevButton" onclick="prevChunk()">Previous</button>
        <button id="nextButton" onclick="nextChunk()">Next</button>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'updateProgress' && isset($data['userID'], $data['feedID'], $data['lastSeenChunkID'])) {
        // Ensure the database connection is available
        global $conn;

        // Prepare the SQL query to insert or update
        $sql = "INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE lastSeenChunkID = ?";
                
        $stmt = $conn->prepare($sql);
        
        // Bind parameters: userID, feedID, lastSeenChunkID
        $stmt->bind_param("iiii", $data['userID'], $data['feedID'], $data['lastSeenChunkID'], $data['lastSeenChunkID']);
        
        // Execute the query
        $stmt->execute();
        
        // Close the statement
        $stmt->close();

        // Increment numChunksSeen in users table
        $sql = "UPDATE users SET numChunksSeen = IFNULL(numChunksSeen, 0) + 1 WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['userID']);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
?>
