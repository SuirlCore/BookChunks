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

echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
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
    <a href="updateFeed.php">Manage your feeds</a>
    <br>
    <a href="updateBooks.php">Add or remove books from a feed</a>
    <br>
    <a href="scrollView.php">Scroll a feed</a>
    <br>
    <br>
    <?php if ($userLevel == 1): ?>
        <a href="systemData.php">System Usage</a>
        <br>
    <?php endif; ?>
    <img src="images/reliablyAptBuzzard.jpg" alt="reliably apt buzzard logo" style="width:300px;height:300px;">
</body>
</html>
