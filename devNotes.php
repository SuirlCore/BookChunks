<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// File path
$filePath = './metadata/onTheRadar.txt';

// Initialize a variable to store file contents
$fileContents = '';

// Check if the file exists
if (file_exists($filePath)) {
    // Read the file contents
    $fileContents = file_get_contents($filePath);
} else {
    $fileContents = "File not found: " . htmlspecialchars($filePath);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Notes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .file-content {
            border: 2px solid #007BFF;
            border-radius: 5px;
            padding: 15px;
            background-color: #fff;
            font-size: 16px;
            line-height: 1;
            color: #333;
            overflow-x: auto;
            max-width: 100%;
            white-space: pre-wrap; /* Preserve newlines and spaces */
        }
        .refresh-button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .refresh-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        // Reload the page when the refresh button is clicked
        function refreshPage() {
            window.location.reload();
        }
    </script>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <h1>Dev Notes</h1>
    <p>
        Items that are being worked on, or on the radar that need to be worked on.
    </p>
    <div class="file-content">
        <?= nl2br(htmlspecialchars($fileContents)) ?>
    </div>
    <button class="refresh-button" onclick="refreshPage()">Refresh</button>
</body>
</html>
