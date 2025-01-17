<?php
// Database connection
include 'scripts/pdo.php';

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Book Chunks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .results {
            margin-top: 20px;
        }
        .result-item {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Search Book Chunks</h1>
    <form id="searchForm" method="POST">
        <label for="book">Choose a book:</label>
        <select name="bookID" id="bookID" required>
            <option value="">Select a book</option>
            <?php
            // Connect to the database
            $mysqli = new mysqli($servername, $username, $password, $dbname);

            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            // Fetch books owned by the user (replace 1 with the actual userID)
            $stmt = $mysqli->prepare("SELECT textID, filename FROM fullTexts WHERE owner = ?");
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['textID'] . "'>" . htmlspecialchars($row['filename']) . "</option>";
            }

            $stmt->close();
            ?>
        </select>
        <br><br>
        <label for="search">Search for text:</label>
        <input type="text" id="search" name="search" required>
        <br><br>
        <button type="submit">Search</button>
    </form>

    <div class="results" id="results">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'], $_POST['bookID'])) {
            $search = $_POST['search'];
            $bookID = (int)$_POST['bookID'];

            // Search chunks for the given bookID
            $stmt = $mysqli->prepare("SELECT chunkID, chunkContent FROM bookChunks WHERE bookID = ? AND chunkContent LIKE ?");
            $likeSearch = "%" . $search . "%";
            $stmt->bind_param("is", $bookID, $likeSearch);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<form id='chooseChunkForm' method='POST'>";
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='result-item'>";
                    echo "<input type='radio' name='chunkID' value='" . $row['chunkID'] . "' required> ";
                    echo htmlspecialchars($row['chunkContent']);
                    echo "</div>";
                }
                echo "<input type='hidden' name='bookID' value='" . $bookID . "'>";
                echo "<label for='feedID'>Choose a feed:</label>";
                echo "<select name='feedID' id='feedID' required>";
                
                // Fetch feeds for the user
                $stmt = $mysqli->prepare("SELECT feedID, feedName FROM feeds WHERE userID = ?");
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $feedResult = $stmt->get_result();
                while ($feed = $feedResult->fetch_assoc()) {
                    echo "<option value='" . $feed['feedID'] . "'>" . htmlspecialchars($feed['feedName']) . "</option>";
                }
                echo "</select><br><br>";
                echo "<button type='submit'>Submit</button>";
                echo "</form>";
            } else {
                echo "<p>No results found.</p>";
            }

            $stmt->close();
        }

        // Handle submission of chosen chunk
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['chunkID'], $_POST['feedID'])) {
            $chunkID = (int)$_POST['chunkID'];
            $feedID = (int)$_POST['feedID'];

            // Check if a record already exists
            $stmt = $mysqli->prepare("SELECT * FROM userFeedProgress WHERE userID = ? AND feedID = ?");
            $stmt->bind_param("ii", $userID, $feedID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing record
                $stmt = $mysqli->prepare("UPDATE userFeedProgress SET lastSeenChunkID = ? WHERE userID = ? AND feedID = ?");
                $stmt->bind_param("iii", $chunkID, $userID, $feedID);
                $stmt->execute();
            } else {
                // Insert new record
                $stmt = $mysqli->prepare("INSERT INTO userFeedProgress (userID, feedID, lastSeenChunkID) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $userID, $feedID, $chunkID);
                $stmt->execute();
            }

            echo "<p>Progress updated successfully.</p>";

            $stmt->close();
        }

        $mysqli->close();
        ?>
    </div>
</body>
</html>
