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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'updateProgress' && isset($data['userID'], $data['feedID'], $data['lastSeenChunkID'])) {
        global $conn;

        // Prepare the SQL query to insert or update
        $sql = "INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE lastSeenChunkID = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $data['userID'], $data['feedID'], $data['lastSeenChunkID'], $data['lastSeenChunkID']);
        $stmt->execute();
        $stmt->close();

        // Optionally, update other fields like numChunksSeen
        $sql = "UPDATE users SET numChunksSeen = IFNULL(numChunksSeen, 0) + 1 WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['userID']);
        $stmt->execute();
        $stmt->close();
    }
}


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
$sql = "SELECT uf.chunkID FROM userFeed uf JOIN bookChunks bc ON uf.chunkID = bc.chunkID WHERE uf.feedID = ? AND uf.userID = ? ORDER BY uf.numInFeed";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $feedID, $userID);
$stmt->execute();
$result = $stmt->get_result();
$chunks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Function to clean chunk content
function cleanChunkContent($content) {
    // Remove any line breaks (\n or \r\n) that might have been inserted from the database
    $content = preg_replace("/\r\n|\r|\n/", " ", $content);
    // Replace multiple spaces with a single space
    $content = preg_replace("/\s+/", " ", $content);
    return $content;
}

// Grab the chunkContent for the chunkID
$sql = "SELECT chunkContent FROM bookChunks WHERE chunkID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lastSeenChunkID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the single chunkContent as a string
$lastChunkContent = $result->fetch_assoc()['chunkContent'];
$stmt->close();

// Clean the chunk content before sending to the frontend
$cleanedContent = cleanChunkContent($lastChunkContent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scroll View</title>
    <style>
        body {
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 90vh; 
            overflow: hidden;  
            color: <?= htmlspecialchars($fontColorChoice); ?>;
            background-color: <?= htmlspecialchars($backgroundColorChoice); ?>;
        }

        .chunk-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fff;
            background-color: <?= htmlspecialchars($backgroundColorChoice); ?>;
            font-size: <?= htmlspecialchars($fontSizeChoice); ?>;
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
            flex: 1; /* This will make buttons take equal space */
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


        .word.highlight {
            background-color: <?= htmlspecialchars($_SESSION['highlightColor']); ?>;
        }

        .line-controls {
            display: flex;
            justify-content: center;
            margin: 0;
        }

        .line-controls button {
            font-size: 16px;
            flex: 1;
            margin: 0;
            padding: 10px 20px;
            cursor: pointer;
        }

        .chunk-controls {
            display: flex;
            justify-content: center;
            margin: 0;
        }

        .chunk-controls button {
            font-size: 16px;
            flex: 1;
            margin: 0;
            padding: 10px 20px;
            cursor: pointer;
        }
        .tooltip {
            position: absolute;
            background: #333;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9em;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            display: none;
            max-width: 300px;
            z-index: 1000;
        }
        .word {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
        .loading {
            font-style: italic;
            color: #aaa;
        }
    </style>

    <script>
    const tooltip = document.getElementById('tooltip');
    const chunkContainer = document.getElementById('chunkContent');

    // Function to fetch the definition of a word
    async function fetchDefinition(word) {
      const apiUrl = `https://api.dictionaryapi.dev/api/v2/entries/en/${word}`;
      try {
        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error("Definition not found.");
        const data = await response.json();

        // Get the first definition
        const meanings = data[0].meanings;
        if (meanings && meanings.length > 0) {
          const definition = meanings[0].definitions[0].definition;
          return definition;
        }
        throw new Error("Definition not found.");
      } catch (error) {
        return error.message;
      }
    }

    function enableTooltip() {
    chunkContainer.addEventListener('click', async (e) => {
        if (e.target.classList.contains('word')) {
            const wordText = e.target.textContent.trim();
            const rect = e.target.getBoundingClientRect();

            // Show tooltip with "Loading..."
            tooltip.textContent = "Loading...";
            tooltip.classList.add('loading');
            tooltip.style.display = 'block';
            tooltip.style.left = `${rect.left + window.scrollX}px`;
            tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;

            // Fetch and display definition
            const definition = await fetchDefinition(wordText);
            tooltip.textContent = definition;
            tooltip.classList.remove('loading');
        }
    });

      // Hide the tooltip if you click outside of a word
      document.addEventListener('click', (e) => {
        if (!e.target.classList.contains('word')) {
          tooltip.style.display = 'none';
        }
      });
    }

    // Initialize the tooltip functionality
    enableTooltip();
    </script>

    <script>
    let chunks = <?php echo json_encode($chunks); ?>;
    let currentIndex = <?php echo $lastSeenChunkID ? array_search($lastSeenChunkID, array_column($chunks, 'chunkID')) : 0; ?>;
    let highlightingToggle = <?php echo isset($_SESSION['highlightingToggle']) ? $_SESSION['highlightingToggle'] : 0; ?>;


    // Call enableTooltip after loading a chunk
    function loadChunk(index) {
    if (index < 0 || index >= chunks.length) return;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'scripts/scrollViewGetChunk.php?chunkID=' + chunks[index].chunkID, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            if (data.chunkContent) {
                const chunkContent = data.chunkContent;
                const words = chunkContent.split(' ').map(word => `<span class="word">${word}</span>`).join(' ');
                const chunkElement = document.getElementById('chunkContent');
                chunkElement.innerHTML = words;

                currentIndex = index;
                currentWordIndex = 0;
                currentLineIndex = 0;

                highlightCurrentLine();
                updateProgress(index);

                // Enable tooltips for the new words
                enableTooltip();
            } else {
                console.error('Error fetching chunk content: ', data.error);
            }
        } else {
            console.error('Request failed with status: ' + xhr.status);
        }
    };
    xhr.send();
}
    function highlightCurrentLine() {
        if (highlightingToggle == 1) {

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
    }

    function getLines(chunkElement) {
        const lines = [];
        const words = chunkElement.querySelectorAll('.word');
        let currentLine = [];
        let currentLineTop = -1;

        words.forEach((word, index) => {
            const wordTop = word.getBoundingClientRect().top;
            
            // Check if this word starts a new line
            if (currentLineTop !== -1 && Math.abs(wordTop - currentLineTop) > 1) {
                // New line detected
                lines.push(currentLine);
                currentLine = [];
            }

            currentLineTop = wordTop;
            currentLine.push(index);
        });

        // Add the last line
        if (currentLine.length > 0) {
            lines.push(currentLine);
        }

        return lines;
    }

    function prevChunk() {
        if (currentIndex > 0) {
            updateProgress(currentIndex - 1);  // Update progress before loading chunk
            loadChunk(currentIndex - 1);       // Load the previous chunk
        }
    }

    function nextChunk() {
        if (currentIndex < chunks.length - 1) {
            updateProgress(currentIndex + 1);  // Update progress before loading chunk
            loadChunk(currentIndex + 1);       // Load the next chunk
        }
    }


    function prevLine() {
        if (currentLineIndex > 0) {
            currentLineIndex--;
            highlightCurrentLine();
        }
    }

    function nextLine() {
        const chunkElement = document.getElementById('chunkContent');
        const lines = getLines(chunkElement);
        if (currentLineIndex < lines.length - 1) {
            currentLineIndex++;
            highlightCurrentLine();
        }
    }

    window.onload = () => {
        loadChunk(currentIndex);

        // Add resize event listener to adjust highlighting
        window.addEventListener('resize', () => {
            highlightCurrentLine();
        });
    };

    function updateProgress(index) {
        // Send the updated progress to the server via AJAX
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
        }).then(response => {
            if (response.ok) {
                console.log("Progress updated successfully");
            } else {
                console.error("Failed to update progress");
            }
        }).catch(error => {
            console.error("Error updating progress:", error);
        });
    }

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
    <div id="tooltip" class="tooltip"></div>

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
