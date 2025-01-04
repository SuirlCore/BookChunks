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
    <title>Welcome</title>
</head>
<body>
    <h2>Welcome</h2>
    Temp Welcome page
    <br>
    
    <a href="uploadPage.php">Upload a text file</a>
    <br>
    <a href="updateFeed.php">add a book to your feed</a>
    <br>
    <a href="scrollView.php">Scroll a feed</a>
    <br>

</body>
</html>
