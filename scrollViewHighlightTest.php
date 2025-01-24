<?php
// Database connection
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
    <title>Dynamic Line Highlighting</title>
    <style>
        body {
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            color: <?= htmlspecialchars($fontColorChoice); ?>;
            background-color: <?= htmlspecialchars($backgroundColorChoice); ?>;
        }

        .chunk-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            font-size: <?= htmlspecialchars($fontSizeChoice); ?>;
            line-height: <?= htmlspecialchars($_SESSION['lineHeight']); ?>;
        }

        .highlight {
            background-color: <?= htmlspecialchars($_SESSION['highlightColor']); ?>;
        }

        .line-controls, .chunk-controls {
            display: flex;
            justify-content: space-evenly;
            padding: 10px;
            background: #A9A9A9;
        }

        .line-controls {
            border-bottom: 1px solid #d3d3d3;
        }

        .chunk-controls {
            border-top: 1px solid #d3d3d3;
        }

        button {
            flex: 1;
            color: <?= htmlspecialchars($_SESSION['buttonTextColor']); ?>;
            font-size: 18px;
            font-weight: bold;
            border: none;
            background: <?= htmlspecialchars($_SESSION['buttonColor']); ?>;
            cursor: pointer;
            transition: background 0.2s ease-in-out;
        }

        button:hover {
            background: <?= htmlspecialchars($_SESSION['buttonHoverColor']); ?>;
        }

        button:disabled {
            background: #d6d6d6;
            color: #aaa;
            cursor: not-allowed;
        }
    </style>
    <script>
        let chunks = <?php echo json_encode($chunks); ?>;
        let currentIndex = <?php echo $lastSeenChunkID ? array_search($lastSeenChunkID, array_column($chunks, 'chunkID')) : 0; ?>;
        let currentWordIndex = 0;

        function loadChunk(index) {
            if (index < 0 || index >= chunks.length) return;

            const chunkContent = chunks[index].chunkContent;
            const words = chunkContent.split(' ').map(word => `<span>${word}</span>`).join(' ');
            document.getElementById('chunkContent').innerHTML = words;

            currentIndex = index;
            currentWordIndex = 0;

            highlightCurrentLine();
            updateProgress(index);
        }

        function highlightCurrentLine() {
            const chunkContainer = document.getElementById('chunkContent');
            const words = chunkContainer.querySelectorAll('span');
            const containerWidth = chunkContainer.clientWidth;
            let currentLineWidth = 0;

            // Reset all highlights
            words.forEach(word => word.classList.remove('highlight'));

            // Highlight words that fit in the visible line
            let startWord = currentWordIndex;
            let endWord = currentWordIndex;

            for (let i = currentWordIndex; i < words.length; i++) {
                const wordWidth = words[i].offsetWidth;
                if (currentLineWidth + wordWidth > containerWidth) break;

                currentLineWidth += wordWidth;
                endWord = i;
            }

            for (let i = startWord; i <= endWord; i++) {
                words[i].classList.add('highlight');
            }
        }

        function nextLine() {
            const words = document.getElementById('chunkContent').querySelectorAll('span');
            if (currentWordIndex < words.length - 1) {
                currentWordIndex += countWordsInLine();
                highlightCurrentLine();
            }
        }

        function prevLine() {
            if (currentWordIndex > 0) {
                currentWordIndex -= countWordsInLine(true);
                highlightCurrentLine();
            }
        }

        function countWordsInLine(reverse = false) {
            const chunkContainer = document.getElementById('chunkContent');
            const words = chunkContainer.querySelectorAll('span');
            const containerWidth = chunkContainer.clientWidth;
            let currentLineWidth = 0;
            let wordCount = 0;

            if (reverse) {
                for (let i = currentWordIndex - 1; i >= 0; i--) {
                    const wordWidth = words[i].offsetWidth;
                    if (currentLineWidth + wordWidth > containerWidth) break;

                    currentLineWidth += wordWidth;
                    wordCount++;
                }
            } else {
                for (let i = currentWordIndex; i < words.length; i++) {
                    const wordWidth = words[i].offsetWidth;
                    if (currentLineWidth + wordWidth > containerWidth) break;

                    currentLineWidth += wordWidth;
                    wordCount++;
                }
            }

            return wordCount;
        }

        function updateProgress(index) {
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

            window.addEventListener('resize', () => {
                highlightCurrentLine();
            });
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

    <?php if ($_SESSION['highlightingToggle'] == 1): ?>
        <div class="line-controls">
            <button onclick="prevLine()">Previous Line</button>
            <button onclick="nextLine()">Next Line</button>
        </div>
    <?php endif; ?>

    <div class="chunk-controls">
        <button onclick="prevChunk()">Previous Chunk</button>
        <button onclick="nextChunk()">Next Chunk</button>
    </div>
</body>
</html>


<?php
// Handle AJAX POST requests for progress updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'updateProgress' && isset($data['userID'], $data['feedID'], $data['lastSeenChunkID'])) {
        $sql = "INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE lastSeenChunkID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $data['userID'], $data['feedID'], $data['lastSeenChunkID'], $data['lastSeenChunkID']);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
?>
