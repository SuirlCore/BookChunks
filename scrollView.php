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
    <title>Scroll Through Feed</title>
    <link rel="stylesheet" href="css/scrollView.css">
</head>
<body>
    <div class="container">
        <h1>Feed Viewer</h1>
        <select id="feedSelect"></select>
        <div id="contentViewer" class="scrollable"></div>
    </div>
    <script src="script/scrollView.js"></script>
</body>
</html>
