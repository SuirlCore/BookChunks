<?php
session_start();

// Mock user authentication
$userID = $_SESSION['user_id'] ?? null;

// Database connection
include 'pdo.php';

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

// Handle feed creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    header('Content-Type: application/json');

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
    </style>
</head>
<body>
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

    <script>
        document.getElementById('create-feed-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('feed_name', document.getElementById('feed-name').value);
            formData.append('feed_description', document.getElementById('feed-description').value);

            fetch('updateFeed.php', {
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

                fetch('scripts/updateFeedBackend.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'confirm') {
                        if (confirm(data.message)) {
                            formData.append('confirm_delete', '1');
                            fetch('updateFeedBackend.php', {
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
