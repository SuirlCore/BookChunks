<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
    
        }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Chunks</title>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <h1>Instructions</h1>
    <p>
        To start off, go to the "Setup Feed" page in the navigation menu.
        Upload a text file, then create a feed. After a feed is created,
        add books to your feed. You can choose what order the books go
        into the feed. You can then choose to start the feed halfway into 
        the book if needed.

        At this point, modifying the feed after you start scrolling through
        is not recommended. If you want to change things up, create a new
        feed.
    </p>
    
    <img src="images/reliablyAptBuzzard.jpg" alt="reliably apt buzzard logo" style="width:300px;height:300px;">
</body>
</html>
