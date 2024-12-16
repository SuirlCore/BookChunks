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
    <title>Upload PDF</title>
</head>
<body>
    <h1>Upload a PDF File</h1>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="pdfFile">Choose a PDF file:</label>
        <input type="file" name="pdfFile" id="pdfFile" accept=".pdf" required><br><br>
        <input type="submit" value="Upload PDF">
    </form>
</body>
</html>