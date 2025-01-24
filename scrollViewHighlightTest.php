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
            white-space: pre-wrap;
            word-wrap: break-word;
            display: block;
            width: 100%;
            box-sizing: border-box;
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

        .word {
            display: inline-block;
            padding-right: 2px; /* Ensure space between words */
        }

        .line {
            display: block;
        }
    </style>
    <script>
        let chunks = <?php echo json_encode($chunks); ?>;
        let currentIndex = <?php echo $lastSeenChunkID ? array_search($lastSeenChunkID, array_column($chunks, 'chunkID')) : 0; ?>;
        let currentWordIndex = 0;
        let currentLineIndex = 0;

        function loadChunk(index) {
            if (index < 0 || index >= chunks.length) return;

            const chunkContent = chunks[index].chunkContent;
            const words = chunkContent.split(' ').map(word => `<span class="word">${word}</span>`).join(' ');
            const chunkElement = document.getElementById('chunkContent');

            chunkElement.innerHTML = words;

            currentIndex = index;
            currentWordIndex = 0;
            currentLineIndex = 0;

            highlightCurrentLine();
            updateProgress(index);
        }

        function highlightCurrentLine() {
            const chunkElement = document.getElementById('chunkContent');
            const words = chunkElement.querySelectorAll('.word');
            const lines = getLines(chunkElement);

            // Reset highlights
            words.forEach(word => word.classList.remove('highlight'));

            // Get words on the current visible line
            const visibleLine = lines[currentLineIndex];

            // Highlight the words in the current visible line
            visibleLine.forEach(wordIndex => {
                words[wordIndex].classList.add('highlight');
            });
        }

        function getLines(chunkElement) {
            const lines = [];
            const words = chunkElement.querySelectorAll('.word');
            let currentLine = [];
            let currentLineWidth = 0;
            const containerWidth = chunkElement.offsetWidth;

            let currentLineStart = 0;
            words.forEach((word, index) => {
                currentLineWidth += word.offsetWidth;
                if (currentLineWidth > containerWidth) {
                    lines.push(currentLine);
                    currentLine = [];
                    currentLineWidth = word.offsetWidth;
                }
                currentLine.push(index);
            });

            // Add the last line
            if (currentLine.length > 0) {
                lines.push(currentLine);
            }

            return lines;
        }

        function nextLine() {
            const lines = getLines(document.getElementById('chunkContent'));
            if (currentLineIndex < lines.length - 1) {
                currentLineIndex++;
                currentWordIndex = lines[currentLineIndex][0];
                highlightCurrentLine();
            }
        }

        function prevLine() {
            if (currentLineIndex > 0) {
                currentLineIndex--;
                currentWordIndex = getLines(document.getElementById('chunkContent'))[currentLineIndex][0];
                highlightCurrentLine();
            }
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
