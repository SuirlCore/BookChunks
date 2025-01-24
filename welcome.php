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
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
        }
        .responsive-image-container {
        display: flex;
        align-items: center;
        background-color: #c0d1ae; /* Color shown to the right of the image */
        max-height: 100px; /* Ensure the container doesn't exceed 100px */
        overflow: hidden; /* Prevent image overflow */
    }

    .responsive-image-container img {
        max-height: 100px; /* Limit image height */
        width: auto; /* Maintain aspect ratio */
    }
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Chunks</title>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <div class="responsive-image-container">
        <img src="images/bookChunkBanner.png" alt="Book Chunks Banner">
    </div>
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

    <p>
        If you need to find some books to get you started, here are a couple of sites that may help:<br>
        <a href="https://www.gutenberg.org/"> Project Gutenberg</a><br>
        
    </p>
    
    <img src="images/reliablyAptBuzzard.jpg" alt="reliably apt buzzard logo" style="width:100px;height:100px;">
</body>
</html>
