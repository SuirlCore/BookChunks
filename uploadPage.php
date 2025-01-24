<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

include 'scripts/pdo.php';

$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT textID as id, filename FROM fullTexts WHERE owner = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body {
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh; 
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Text File</title>
</head>
<body>
    <h2>Upload a Text File to start.</h2>
    <p>
        This can take 30 seconds or so, be patient. When your book shows up in the table below, its finished.
    </p>
    <form action="scripts/upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="text_file" accept=".txt" required><br><br>
        <input type="submit" value="Upload and Process">
    </form>

    <?php if (isset($_SESSION['upload_message'])): ?>
        <p style="color: <?= htmlspecialchars($_SESSION['fontColor']); ?>;">
            <?= htmlspecialchars($_SESSION['upload_message']); ?>
        </p>
        <?php unset($_SESSION['upload_message']); ?>
    <?php endif; ?>

    <p>
        As of now we only recognise .txt files with a UTF-8 encoding.
        You can use the following website to convert a pdf or most other types of files to a text file.<br>
        <a href="https://cloudconvert.com/pdf-to-txt">https://cloudconvert.com/pdf-to-txt</a><br>
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
                    <form action="scripts/recalculateBook.php" method="POST" style="display: inline;">
                        <input type="hidden" name="book_id" value="<?= $book['id']; ?>">
                        <button type="submit" onclick="submit">Recalculate</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
