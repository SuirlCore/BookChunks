<?php
session_start();
$userID = $_SESSION['user_id'];
echo "Welcome, " . $_SESSION['username'] . "!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create and Manage Feeds</title>
    <link rel="stylesheet" href="css/updateFeed.css">
</head>
<body>
    <div class="container">
        <h1>Create a New Feed</h1>
        <form id="create-feed-form">
            <label for="feedName">Feed Name:</label>
            <input type="text" id="feedName" name="feedName" required>
            <label for="feedDescription">Feed Description:</label>
            <textarea id="feedDescription" name="feedDescription"></textarea>
            <button type="submit">Create Feed</button>
        </form>
        <div id="feed-management">
            <h2>Manage Feed</h2>
            <select id="feedSelect"></select>
            <h3>Available Books</h3>
            <ul id="availableBooks"></ul>
            <h3>Books in Feed</h3>
            <ul id="feedBooks"></ul>
            <button id="updateFeed">Update Feed</button>
        </div>
    </div>
    <script src="scripts/updateFeed.js"></script>
</body>
</html>
