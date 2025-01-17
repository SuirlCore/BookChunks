<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

include 'pdo.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $bookID = intval($_POST['book_id']);
    $userID = $_SESSION['user_id'];

    $dbConn = new mysqli($servername, $username, $password, $dbname);
    if ($dbConn->connect_error) {
        die("Connection failed: " . $dbConn->connect_error);
    }

    // Verify that the book belongs to the user
    $stmt = $dbConn->prepare("SELECT id FROM fullTexts WHERE id = ? AND owner = ?");
    $stmt->bind_param("ii", $bookID, $userID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Delete from bookChunks table
        $stmt = $dbConn->prepare("DELETE FROM bookChunks WHERE bookID = ?");
        $stmt->bind_param("i", $bookID);
        $stmt->execute();

        // Delete from fullTexts table
        $stmt = $dbConn->prepare("DELETE FROM fullTexts WHERE id = ?");
        $stmt->bind_param("i", $bookID);
        $stmt->execute();

        $stmt->close();
        $dbConn->close();

        header("Location: ../uploadPage.php");
        exit();
    } else {
        $stmt->close();
        $dbConn->close();
        die("Unauthorized action or book does not exist.");
    }
} else {
    die("Invalid request.");
}
?>
