<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Connect to the database
include 'scripts/pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch userLevel from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT userLevel FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$userLevel = $user['userLevel'] ?? 0;

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Chunks</title>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <h1>About</h1>
    <p>
        This site allows you to upload books or other documents in the form of .txt files. 
        It breaks the text up into sections of 3 sentences each, and
        allows you to scroll through the book. You can choose different
        feeds that are loaded with different books.

        Future implementation will have the ability to filter in other
        things in between the individual book chunks.
    </p>

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
        If you are curious, the code for this webpage is at
        <a href="https://github.com/SuirlCore/BookChunks"> My Github Page</a><br>
        You can also reach me with comments or suggestions by filling out the form
        On the Dev Notes page accessed in the menu.
    </p>
    
    <img src="images/reliablyAptBuzzard.jpg" alt="reliably apt buzzard logo" style="width:300px;height:300px;">
</body>
</html>
