<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'scripts/pdo.php';

$userID = $_SESSION['user_id'];

// Fetch books and collections owned by the user
$stmtBooks = $conn->prepare("SELECT textID as id, filename FROM fullTexts WHERE owner = ?");
$stmtBooks->bind_param("i", $userID);
$stmtBooks->execute();
$resultBooks = $stmtBooks->get_result();
$books = $resultBooks->fetch_all(MYSQLI_ASSOC);
$stmtBooks->close();

$stmtCollections = $conn->prepare("SELECT collectionID as id, collectionName FROM collections WHERE userID = ?");
$stmtCollections->bind_param("i", $userID);
$stmtCollections->execute();
$resultCollections = $stmtCollections->get_result();
$collections = $resultCollections->fetch_all(MYSQLI_ASSOC);
$stmtCollections->close();

// Fetch books organized by collections
$queryBooksInCollections = "
    SELECT c.collectionName, f.filename, ic.itemID 
    FROM itemsInCollection ic
    INNER JOIN collections c ON ic.collectionID = c.collectionID
    INNER JOIN fullTexts f ON ic.itemID = f.textID
    WHERE c.userID = ?
    ORDER BY c.collectionName, ic.positionID ASC";
$stmtBooksInCollections = $conn->prepare($queryBooksInCollections);
$stmtBooksInCollections->bind_param("i", $userID);
$stmtBooksInCollections->execute();
$booksInCollections = $stmtBooksInCollections->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtBooksInCollections->close();

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
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; 
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; 
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Text File</title>
</head>
<body>
    <?php if (isset($_SESSION['upload_message'])): ?>
        <p style="color: <?= htmlspecialchars($_SESSION['fontColor']); ?>;">
            <?= htmlspecialchars($_SESSION['upload_message']); ?>
        </p>
        <?php unset($_SESSION['upload_message']); ?>
    <?php endif; ?>

    <h2>Upload a Text File</h2>
    <form action="scripts/uploadTest.php" method="POST" enctype="multipart/form-data">
        <label for="text_file">Select File:</label>
        <input type="file" name="text_file" accept=".txt" required><br><br>

        <label for="collection">Add to Collection:</label>
        <select id="collection" name="collection">
            <option value="new">Create New Collection</option>
            <?php foreach ($collections as $collection): ?>
                <option value="<?= $collection['id']; ?>"><?= htmlspecialchars($collection['collectionName']); ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <label for="new_collection_name">New Collection Name:</label>
        <input type="text" id="new_collection_name" name="new_collection_name" placeholder="Enter new collection name"><br><br>

        <input type="submit" value="Upload and Process">
    </form>

    <h3>Your Uploaded Books by Collection</h3>
    <table border="1">
        <tr>
            <th>Collection</th>
            <th>Filename</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($booksInCollections as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['collectionName']); ?></td>
                <td><?= htmlspecialchars($book['filename']); ?></td>
                <td>
                    <form action="scripts/deleteBook.php" method="POST" style="display: inline;">
                        <input type="hidden" name="book_id" value="<?= $book['itemID']; ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
