<?php
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$feedID = $_GET['feedID'];
$stmt = $conn->prepare("SELECT b.bookID, f.filename FROM userFeed u 
                        JOIN bookChunks b ON u.chunkID = b.chunkID
                        JOIN fullTexts f ON b.bookID = f.textID
                        WHERE u.feedID = ?");
$stmt->bind_param("i", $feedID);
$stmt->execute();
$result = $stmt->get_result();
$feed = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['feed' => $feed]);
?>
