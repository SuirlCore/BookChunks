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
        <div>
            <label for="feedSelect">Choose Feed:</label>
            <select id="feedSelect"></select>
        </div>
        <div>
            <h2>Available Books</h2>
            <div id="bookList"></div>
        </div>
        <button id="addToFeedBtn">Add Selected Books to Feed</button>
    </div>
    <script src="scripts/updateFeed.js"></script>
</body>
</html>