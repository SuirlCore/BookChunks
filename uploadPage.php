<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

include 'scripts/pdo.php'; // Include your database connection file

// Fetch books uploaded by the user
$dbConn = new mysqli($servername, $username, $password, $dbname);
if ($dbConn->connect_error) {
    die("Connection failed: " . $dbConn->connect_error);
}

$userID = $_SESSION['user_id'];
$stmt = $dbConn->prepare("SELECT textID as id, filename FROM fullTexts WHERE owner = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$dbConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Text File</title>
</head>
<body>
    <h2>Upload a Text File to start.</h2>
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

    <h3>Your Uploaded Books</h3>
    <table border="1">
        <tr>
            <th>Filename</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['filename']); ?></td>
                <td>
                    <form action="scripts/deleteBook.php" method="POST" style="display: inline;">
                        <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
  
    </table>

</body>
</html>


