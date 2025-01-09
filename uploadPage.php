<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

echo "Welcome, " . $_SESSION['username'] . "!";

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<div style='color: green; font-size: 16px; margin: 10px 0; padding: 10px; border: 1px solid green; background-color: #e8f7e8;'>Upload was successful!</div>";
}

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
        As of now we only recognise .txt files with a UTF-8 encoding.
        You can use the following website to convert a pdf or most other types of files to a text file.<br>

        <a href = "https://cloudconvert.com/pdf-to-txt"> https://cloudconvert.com/pdf-to-txt</a><br>
    </p>
    
    <p>
        If you are having problems uploading a whole file, the problem may be the encoding for the text
        file being uploaded.<br>

        Try going to <a href="https://www.freeformatter.com/convert-file-encoding.html">www.freeformatter.com</a> to ensure that your text 
        file is in the proper format.<br>
    </p>

    <p>
        <a href='welcome.php'>Go back to the main page.</a>
    </p>
</body>
</html>


