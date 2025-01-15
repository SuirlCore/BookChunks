<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// User authentication
$userID = $_SESSION['user_id'] ?? null;

// Database connection
include 'scripts/pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

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

    if ($_POST['action'] === 'create') {
        // Handle feed creation
        $feedName = trim($_POST['feed_name'] ?? '');
        $feedDescription = trim($_POST['feed_description'] ?? '');

        if (empty($feedName)) {
            echo json_encode(["status" => "error", "message" => "Feed name is required."]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO feeds (userID, feedName, feedDescription) VALUES (?, ?, ?)");
        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Failed to prepare statement."]);
            exit;
        }

        $stmt->bind_param("iss", $userID, $feedName, $feedDescription);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Feed created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create feed."]);
        }

        $stmt->close();
        exit;
    } elseif ($_POST['action'] === 'delete') {
        // Handle feed deletion
        $feedID = intval($_POST['feed_id'] ?? 0);
        $confirmDelete = $_POST['confirm_delete'] ?? false;

        // Check if the feed belongs to the current user
        $stmt = $conn->prepare("SELECT feedID FROM feeds WHERE feedID = ? AND userID = ?");
        $stmt->bind_param("ii", $feedID, $userID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Feed not found or access denied."]);
            exit;
        }
        $stmt->close();

        // Check for associated records
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS relatedRecords 
            FROM userFeed WHERE feedID = ? UNION ALL
            SELECT COUNT(*) FROM booksInFeed WHERE feedID = ? UNION ALL
            SELECT COUNT(*) FROM userFeedProgress WHERE feedID = ?
        ");
        $stmt->bind_param("iii", $feedID, $feedID, $feedID);
        $stmt->execute();
        $result = $stmt->get_result();
        $relatedRecords = 0;

        while ($row = $result->fetch_assoc()) {
            $relatedRecords += $row['relatedRecords'];
        }

        $stmt->close();

        // If there are related records, confirm deletion
        if ($relatedRecords > 0 && !$confirmDelete) {
            echo json_encode(["status" => "confirm", "message" => "This feed has associated data. Deleting it will also remove related records. Are you sure?"]);
            exit;
        }

        // Delete related records first
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("DELETE FROM userFeed WHERE feedID = ?");
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM booksInFeed WHERE feedID = ?");
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM userFeedProgress WHERE feedID = ?");
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->close();

            // Delete the feed
            $stmt = $conn->prepare("DELETE FROM feeds WHERE feedID = ?");
            $stmt->bind_param("i", $feedID);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Feed deleted successfully."]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Failed to delete feed: " . $e->getMessage()]);
        }
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Fetch feeds for the current user
$stmt = $conn->prepare("SELECT feedID, feedName, feedDescription FROM feeds WHERE userID = ?");
if (!$stmt) {
    die("Failed to prepare statement: " . $conn->error);
}
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$feeds = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Feeds</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        .feed-list, .create-feed {
            margin-top: 20px;
        }
        .feed-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .feed-item button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        form input, form button {
            padding: 10px;
            margin-right: 10px;
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
    <?php include 'navigation.php'; ?>

    <h1>Upload a Text File</h1>

    <form action="scripts/upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="text_file" accept=".txt" required><br><br>
        <input type="submit" value="Upload and Process">
    </form>

    <h1>Manage Feeds</h1>

    <div class="create-feed">
        <h2>Create a New Feed</h2>
        <form id="create-feed-form">
            <input type="text" id="feed-name" name="feed_name" placeholder="Feed Name" required>
            <input type="text" id="feed-description" name="feed_description" placeholder="Feed Description">
            <button type="submit">Create Feed</button>
        </form>
    </div>

    <div class="feed-list">
        <h2>Your Feeds</h2>
        <div id="feeds-container">
            <?php foreach ($feeds as $feed): ?>
                <div class="feed-item" data-feed-id="<?= $feed['feedID'] ?>">
                    <span><?= htmlspecialchars($feed['feedName']) ?> - <?= htmlspecialchars($feed['feedDescription']) ?></span>
                    <button class="delete-feed">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h1>Manage Books in Feeds</h1>
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

    <div>
        <button id="synchronize-btn" style="background-color: #28a745; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
            Synchronize Feed
        </button>
    </div>

    <script>
    document.getElementById("synchronize-btn").addEventListener("click", async function () {
        const feedID = document.getElementById("feeds").value;

        if (!feedID) {
            alert("Please select a feed.");
            return;
        }

        if (!confirm("This will synchronize the feed and overwrite existing data. Proceed?")) {
            return;
        }

        const response = await fetch("scripts/synchronizeFeed.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ feedID }),
        });

        const result = await response.json();
        if (result.success) {
            alert("Feed synchronized successfully!");
        } else {
            alert("Synchronization failed: " + (result.message || "Unknown error."));
        }
    });
    </script>

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
    
    <script>
        document.getElementById('create-feed-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('feed_name', document.getElementById('feed-name').value);
            formData.append('feed_description', document.getElementById('feed-description').value);

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        });

        document.querySelectorAll('.delete-feed').forEach(button => {
            button.addEventListener('click', function() {
                const feedID = this.closest('.feed-item').getAttribute('data-feed-id');
                if (!feedID) return;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('feed_id', feedID);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'confirm') {
                        if (confirm(data.message)) {
                            formData.append('confirm_delete', '1');
                            fetch('', {
                                method: 'POST',
                                body: formData
                            }).then(response => response.json()).then(innerData => {
                                alert(innerData.message);
                                if (innerData.status === 'success') location.reload();
                            });
                        }
                    } else {
                        alert(data.message);
                        if (data.status === 'success') location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred.');
                });
            });
        });
    </script>
</body>
</html>


