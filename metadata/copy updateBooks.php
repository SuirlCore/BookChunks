<?php
// Database connection
include 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];

// Fetch feeds owned by the user
$feedsQuery = "SELECT * FROM feeds WHERE userID = ?";
$stmt = $conn->prepare($feedsQuery);

if ($stmt) {
    $stmt->bind_param("i", $userID);
    if ($stmt->execute()) {
        $feeds = $stmt->get_result();
    } else {
        error_log("Error executing feeds query: " . $stmt->error);
        $feeds = false; // Mark as failed
    }
    $stmt->close();
} else {
    error_log("Error preparing feeds query: " . $conn->error);
    $feeds = false; // Mark as failed
}

// Handle case where $feeds is empty or query failed
if (!$feeds) {
    $feeds = [];
    error_log("Feeds query failed or returned no results.");
}

// Fetch books owned by the user
$booksQuery = "SELECT * FROM fullTexts WHERE owner = ?";
$stmt = $conn->prepare($booksQuery);

if ($stmt) {
    $stmt->bind_param("i", $userID);
    if ($stmt->execute()) {
        $books = $stmt->get_result();
    } else {
        error_log("Error executing books query: " . $stmt->error);
        $books = false; // Mark as failed
    }
    $stmt->close();
} else {
    error_log("Error preparing books query: " . $conn->error);
    $books = false; // Mark as failed
}

if (!$books) {
    $books = [];
    error_log("Books query failed or returned no results.");
}

// Handle POST requests for adding, removing, or reordering books in feeds
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $feedID = $_POST['feedID'] ?? null;
    $textID = $_POST['textID'] ?? null;
    $bookID = $_POST['bookID'] ?? null;
    $response = ['success' => false];

    if ($action === 'add') {
        $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 AS nextPosition FROM booksInFeed WHERE feedID = ?";
        if ($stmt = $conn->prepare($positionQuery)) {
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->bind_result($nextPosition);
            $stmt->fetch();
            $stmt->close();

            $addQuery = "INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($addQuery)) {
                $stmt->bind_param("iii", $feedID, $textID, $nextPosition);
                $response['success'] = $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($action === 'remove') {
        $removeQuery = "DELETE FROM booksInFeed WHERE feedID = ? AND bookID = ?";
        if ($stmt = $conn->prepare($removeQuery)) {
            $stmt->bind_param("ii", $feedID, $bookID);
            $response['success'] = $stmt->execute();
            $stmt->close();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Return books in feed for GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['feedID'])) {
    $feedID = $_GET['feedID'];
    $booksInFeedQuery = "
        SELECT b.bookID, f.filename, b.position
        FROM booksInFeed b
        JOIN fullTexts f ON b.bookID = f.textID
        WHERE b.feedID = ?
        ORDER BY b.position ASC";

    if ($stmt = $conn->prepare($booksInFeedQuery)) {
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $result = $stmt->get_result();

        $books = [];
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }

        $stmt->close();

        header('Content-Type: application/json');
        echo json_encode($books);
        exit;
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feeds</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        form, #books-in-feed, #all-books {
            margin: 20px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
        }
        .move-btn, .add-btn, .remove-btn {
            cursor: pointer;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .move-btn:hover, .add-btn:hover, .remove-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Manage Your Feeds</h1>
    <form id="feed-selector">
        <label for="feeds">Select a Feed:</label>
        <select id="feeds" name="feedID">
            <?php while ($feed = $feeds->fetch_assoc()): ?>
                <option value="<?= $feed['feedID'] ?>">
                    <?= htmlspecialchars($feed['feedName']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div id="books-in-feed"></div>

    <div id="all-books">
        <h2>All Books</h2>
        <ul>
            <?php while ($book = $books->fetch_assoc()): ?>
                <li data-book-id="<?= $book['textID'] ?>">
                    <?= htmlspecialchars($book['filename']) ?>
                    <button class="add-btn" onclick="addBookToFeed(<?= $book['textID'] ?>)">Add</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const feedSelector = document.getElementById("feeds");
            const booksInFeedDiv = document.getElementById("books-in-feed");

            feedSelector.addEventListener("change", fetchBooksInFeed);

            async function fetchBooksInFeed() {
                const feedID = feedSelector.value;
                const response = await fetch(`updateBooks.php?feedID=${feedID}`);
                const books = await response.json();

                booksInFeedDiv.innerHTML = `<h2>Books in Feed</h2>`;
                const ul = document.createElement("ul");
                books.forEach((book) => {
                    const li = document.createElement("li");
                    li.setAttribute("data-book-id", book.bookID);
                    li.innerHTML = `
                        ${book.filename} (Position: ${book.position})
                        <button class="remove-btn" onclick="removeBookFromFeed(${book.bookID})">Remove</button>
                    `;
                    ul.appendChild(li);
                });
                booksInFeedDiv.appendChild(ul);
            }

            window.addBookToFeed = async function (textID) {
                const feedID = feedSelector.value;

                const response = await fetch("updateBooks.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({
                        action: "add",
                        feedID,
                        textID,
                    }),
                });

                const result = await response.json();
                if (result.success) fetchBooksInFeed();
            };

            window.removeBookFromFeed = async function (bookID) {
                const feedID = feedSelector.value;

                const response = await fetch("updateBooks.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({
                        action: "remove",
                        feedID,
                        bookID,
                    }),
                });

                const result = await response.json();
                if (result.success) fetchBooksInFeed();
            };

            fetchBooksInFeed();
        });
    </script>
</body>
</html>
