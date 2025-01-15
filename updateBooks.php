<?php
// Database connection
include 'scripts/pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$userID = $_SESSION['user_id']; // Assuming the user is logged in

// Fetch feeds owned by the user
$feedsQuery = "SELECT * FROM feeds WHERE userID = ?";
$stmt = $conn->prepare($feedsQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$feeds = $stmt->get_result();
$stmt->close();

// Fetch books
$booksQuery = "SELECT * FROM fullTexts WHERE owner = ?";
$stmt = $conn->prepare($booksQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$books = $stmt->get_result();
$stmt->close();

// Add or remove books in the feed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $feedID = $_POST['feedID'];
        $bookID = $_POST['bookID'];

        if ($action === 'add') {
            // Get the next position in the feed
            $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 AS nextPosition FROM booksInFeed WHERE feedID = ?";
            $stmt = $conn->prepare($positionQuery);
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $nextPosition = $result['nextPosition'];
            $stmt->close();

            // Insert the book into the feed
            $addQuery = "INSERT INTO booksInFeed (feedID, bookID, position) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($addQuery);
            $stmt->bind_param("iii", $feedID, $bookID, $nextPosition);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'remove') {
            $removeQuery = "DELETE FROM booksInFeed WHERE feedID = ? AND bookID = ?";
            $stmt = $conn->prepare($removeQuery);
            $stmt->bind_param("ii", $feedID, $bookID);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch books in a feed (for AJAX requests)
if (isset($_GET['feedID'])) {
    $feedID = $_GET['feedID'];
    $booksInFeedQuery = "
        SELECT b.id, f.filename, b.position
        FROM booksInFeed b
        JOIN fullTexts f ON b.bookID = f.textID
        WHERE b.feedID = ?
        ORDER BY b.position ASC";
    $stmt = $conn->prepare($booksInFeedQuery);
    $stmt->bind_param("i", $feedID);
    $stmt->execute();
    $booksInFeed = $stmt->get_result();
    $stmt->close();

    echo json_encode($booksInFeed->fetch_all(MYSQLI_ASSOC));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Books</title>
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
            cursor: move;
        }
        .move-btn {
            cursor: pointer;
        }
        .move-btn:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
    <h1>Manage Your Feeds</h1>
    <form id="feed-selector">
        <label for="feeds">Select a Feed:</label>
        <select id="feeds" name="feedID">
            <?php while ($feed = $feeds->fetch_assoc()): ?>
                <option value="<?= $feed['feedID'] ?>"><?= $feed['feedName'] ?></option>
            <?php endwhile; ?>
        </select>
    </form>

    <div id="books-in-feed"></div>
    <div id="all-books">
        <h2>All Books</h2>
        <ul>
            <?php while ($book = $books->fetch_assoc()): ?>
                <li data-book-id="<?= $book['textID'] ?>">
                    <?= $book['filename'] ?>
                    <button class="add-btn" onclick="addBookToFeed(<?= $book['textID'] ?>)">Add</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <p>
        <a href='welcome.php'>Go back to the main page.</a>
    </p>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const feedSelector = document.getElementById("feeds");
            const booksInFeedDiv = document.getElementById("books-in-feed");

            feedSelector.addEventListener("change", fetchBooksInFeed);

            async function fetchBooksInFeed() {
                const feedID = feedSelector.value;
                const response = await fetch(`updateBooks.php?feedID=${feedID}`);
                const books = await response.json();

                booksInFeedDiv.innerHTML = `<h2>Books in Feed</h2>`;
                const ul = document.createElement("ul");
                books.forEach(book => {
                    const li = document.createElement("li");
                    li.setAttribute('data-book-id', book.id);
                    li.innerHTML = "${book.filename} (Position: ${book.position}) 
                                    <button class='move-btn' onclick='moveUp(${book.id})'>↑</button>
                                    <button class='move-btn' onclick='moveDown(${book.id})'>↓</button>
                                    <button class='remove-btn' onclick='removeBookFromFeed(${book.id})'>Remove</button>";
                    ul.appendChild(li);
                });
                booksInFeedDiv.appendChild(ul);
            }

            async function addBookToFeed(bookID) {
                const feedID = feedSelector.value;

                const response = await fetch('updateBooks.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'add',
                        feedID,
                        bookID
                    })
                });

                const result = await response.json();
                if (result.success) {
                    fetchBooksInFeed();
                }
            }

            async function removeBookFromFeed(bookID) {
                const feedID = feedSelector.value;

                const response = await fetch('updateBooks.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'remove',
                        feedID,
                        bookID
                    })
                });

                const result = await response.json();
                if (result.success) {
                    fetchBooksInFeed();
                }
            }

            async function moveUp(bookID) {
                const feedID = feedSelector.value;
                const response = await fetch('updateBooks.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'reorder',
                        feedID,
                        positions: JSON.stringify([{ bookID, position: 'up' }])
                    })
                });

                const result = await response.json();
                if (result.success) {
                    fetchBooksInFeed();
                }
            }

            async function moveDown(bookID) {
                const feedID = feedSelector.value;
                const response = await fetch('updateBooks.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'reorder',
                        feedID,
                        positions: JSON.stringify([{ bookID, position: 'down' }])
                    })
                });

                const result = await response.json();
                if (result.success) {
                    fetchBooksInFeed();
                }
            }

            fetchBooksInFeed();
        });
    </script>
</body>
</html>