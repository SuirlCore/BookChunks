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
    <title>Upload Text File</title>
</head>
<body>
    <h2>Upload a Text File</h2>
    <form action="scripts/upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="text_file" accept=".txt" required><br><br>
        <input type="submit" value="Upload and Process">
    </form>
    <p>
        If you are having problems uploading a whole file, the problem may be the encoding for the text
        file being uploaded. This program only recognises UTF-8 files but we are working on fixing this
        in the future.<br>

        Try going to <a href="www.freeformatter.com">www.freeformatter.com</a> to ensure that your text file is in the proper format.
    </p>
</body>
</html>