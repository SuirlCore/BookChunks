<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

echo "Welcome, " . $_SESSION['username'] . "!";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feeds</title>
    <link rel="stylesheet" href="css/updateFeed.css">
</head>
<body>
    <div class="container">
        <h1>Manage Your Feeds</h1>
        <div id="feedSection">
            <label for="feedSelect">Choose Feed:</label>
            <select id="feedSelect"></select>
            <button id="newFeedBtn">Create New Feed</button>
        </div>
        <div id="newFeedForm" style="display: none;">
            <h2>Create a New Feed</h2>
            <input type="text" id="newFeedName" placeholder="Feed Name" required>
            <textarea id="newFeedDescription" placeholder="Feed Description"></textarea>
            <button id="createFeedBtn">Create Feed</button>
        </div>
        <div>
            <h2>Available Books</h2>
            <div id="bookList"></div>
        </div>
        <div>
            <h2>Current Feed</h2>
            <ul id="feedList" class="sortable"></ul>
        </div>
        <div>
            <button id="updateFeedBtn">Update Feed</button>
        </div>
    </div>
    <script src="scripts/updateFeed.js"></script>
</body>
</html>
