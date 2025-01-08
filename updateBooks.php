<?php
// Database connection
include 'pdo.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $feedID = $_POST['feedID'];
    $textID = $_POST['textID'];
    $response = ['success' => false];
    if ($action === 'add') {
        // Get the next available position for the new book in the feed
        $positionQuery = "SELECT COALESCE(MAX(position), 0) + 1 AS nextPosition FROM booksInFeed WHERE feedID = ?";
        if ($stmt = $conn->prepare($positionQuery)) {
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->bind_result($nextPosition);
            $stmt->fetch();
            $stmt->close();

            // Add the book to the feed
            $addQuery = "INSERT INTO booksInFeed (feedID, textID, position) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($addQuery)) {
                $stmt->bind_param("iii", $feedID, $textID, $nextPosition);
                $response['success'] = $stmt->execute();
                $stmt->close();
            }
        }
    } elseif ($action === 'remove') {
        // Remove the book from the feed
        $removeQuery = "DELETE FROM booksInFeed WHERE feedID = ? AND textID = ?";
        if ($stmt = $conn->prepare($removeQuery)) {
            $stmt->bind_param("ii", $feedID, $textID);
            $response['success'] = $stmt->execute();
            $stmt->close();
        }
    } elseif ($action === 'reorder') {
        // Get the current position of the book
        $direction = $_POST['direction'];
        $currentPositionQuery = "SELECT position FROM booksInFeed WHERE feedID = ? AND textID = ?";
        if ($stmt = $conn->prepare($currentPositionQuery)) {
            $stmt->bind_param("ii", $feedID, $textID);
            $stmt->execute();
            $stmt->bind_result($currentPosition);
            $stmt->fetch();
            $stmt->close();

            // Swap positions based on direction (up or down)
            if ($direction === 'up') {
                $swapQuery = "
                    UPDATE booksInFeed AS b1
                    JOIN booksInFeed AS b2 ON b1.position = b2.position + 1
                    SET b1.position = b2.position, b2.position = b1.position
                    WHERE b1.feedID = ? AND b2.feedID = ? AND b1.textID = ? AND b2.position = ?";
            } elseif ($direction === 'down') {
                $swapQuery = "
                    UPDATE booksInFeed AS b1
                    JOIN booksInFeed AS b2 ON b1.position = b2.position - 1
                    SET b1.position = b2.position, b2.position = b1.position
                    WHERE b1.feedID = ? AND b2.feedID = ? AND b1.textID = ? AND b2.position = ?";
            }

            if ($stmt = $conn->prepare($swapQuery)) {
                $stmt->bind_param("iiii", $feedID, $feedID, $textID, $currentPosition);
                $response['success'] = $stmt->execute();
                $stmt->close();
            }
        }
    }
}

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

// Fetch books in the feed for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['feedID'])) {
    $feedID = $_GET['feedID'];
    $booksInFeedQuery = "
        SELECT b.textID, f.filename, b.position
        FROM booksInFeed b
        JOIN fullTexts f ON b.textID = f.textID
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

        // Return the books in the feed as JSON
        echo json_encode($books);
        exit;
    }
}

// Fetch books in feed for AJAX
if (isset($_GET['feedID'])) {
    $feedID = $_GET['feedID'];
    $booksInFeedQuery = "
        SELECT b.bookID, f.filename, b.position
        FROM booksInFeed b
        JOIN fullTexts f ON b.bookID = f.bookID
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
            li.setAttribute("data-book-id", book.textID);
            li.innerHTML = `
                ${book.filename} (Position: ${book.position})
                <button class="move-btn" onclick="moveUp(${book.textID})">↑</button>
                <button class="move-btn" onclick="moveDown(${book.textID})">↓</button>
                <button class="remove-btn" onclick="removeBookFromFeed(${book.textID})">Remove</button>
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

    window.removeBookFromFeed = async function (textID) {
        const feedID = feedSelector.value;

        const response = await fetch("updateBooks.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "remove",
                feedID,
                textID,
            }),
        });

        const result = await response.json();
        if (result.success) fetchBooksInFeed();
    };

    window.moveUp = async function (textID) {
        const feedID = feedSelector.value;

        const response = await fetch("updateBooks.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "reorder",
                feedID,
                textID,
                direction: "up",
            }),
        });

        const result = await response.json();
        if (result.success) fetchBooksInFeed();
    };

    window.moveDown = async function (textID) {
        const feedID = feedSelector.value;

        const response = await fetch("updateBooks.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "reorder",
                feedID,
                textID,
                direction: "down",
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
