<?php
session_start();

include 'pdo.php'; // Include your database connection variables

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

header('Content-Type: application/json');
$userID = $_SESSION['userID'] ?? null;

if (!$userID) {
    echo json_encode(["status" => "error", "message" => "User not authenticated."]);
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $feedID = intval($_POST['feed_id'] ?? 0);
    $confirmDelete = $_POST['confirm_delete'] ?? false;

    // Check if the feed belongs to the current user
    $stmt = $conn->prepare("SELECT feedID FROM feeds WHERE feedID = ? AND userID = ?");
    $stmt->bind_param("ii", $feedID, $userID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Feed not found or access denied."]);
        exit;
    }
    $stmt->close();

    // Check for associated records
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS relatedRecords 
        FROM userFeed WHERE feedID = ? UNION ALL
        SELECT COUNT(*) FROM booksInFeed WHERE feedID = ? UNION ALL
        SELECT COUNT(*) FROM userFeedProgress WHERE feedID = ?
    ");
    $stmt->bind_param("iii", $feedID, $feedID, $feedID);
    $stmt->execute();
    $result = $stmt->get_result();
    $relatedRecords = 0;

    while ($row = $result->fetch_assoc()) {
        $relatedRecords += $row['relatedRecords'];
    }

    $stmt->close();

    // If there are related records, confirm deletion
    if ($relatedRecords > 0 && !$confirmDelete) {
        echo json_encode(["status" => "confirm", "message" => "This feed has associated data. Deleting it will also remove related records. Are you sure?"]);
        exit;
    }

    // Delete related records first
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM userFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM booksInFeed WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM userFeedProgress WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $stmt->close();

        // Delete the feed
        $stmt = $conn->prepare("DELETE FROM feeds WHERE feedID = ?");
        $stmt->bind_param("i", $feedID);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Feed deleted successfully."]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to delete feed: " . $e->getMessage()]);
    }
