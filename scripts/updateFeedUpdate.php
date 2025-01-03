<?php
require 'pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$data = json_decode(file_get_contents('php://input'), true);
$feedID = $data['feedID'];
$books = $data['books'];

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("DELETE FROM userFeed WHERE feedID = ?");
    $stmt->bind_param("i", $feedID);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO userFeed (feedID, numInFeed, chunkID, userID) VALUES (?, ?, ?, ?)");
    foreach ($books as $index => $chunkID) {
        $stmt->bind_param("iiii", $feedID, $index, $chunkID, $_SESSION['userID']);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Feed updated successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to update feed.']);
}
?>
