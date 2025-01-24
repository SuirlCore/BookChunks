<?php
// Include database connection
include 'pdo.php';

// Function to clean chunk content
function cleanChunkContent($content) {
    // Remove any line breaks (\n or \r\n) that might have been inserted from the database
    $content = preg_replace("/\r\n|\r|\n/", " ", $content);
    // Replace multiple spaces with a single space
    $content = preg_replace("/\s+/", " ", $content);
    return $content;
}

// Get the chunk ID from the request
if (isset($_GET['chunkID'])) {
    $chunkID = (int)$_GET['chunkID'];

    // Fetch the chunk content from the database
    global $conn;
    $sql = "SELECT chunkContent FROM bookChunks WHERE chunkID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $chunkID);
    $stmt->execute();
    $result = $stmt->get_result();
    $chunk = $result->fetch_assoc();
    $stmt->close();

    // If chunk exists, return the cleaned content
    if ($chunk) {
        echo json_encode(['chunkContent' => cleanChunkContent($chunk['chunkContent'])]);
    } else {
        echo json_encode(['error' => 'Chunk not found']);
    }
} else {
    echo json_encode(['error' => 'No chunkID specified']);
}
?>
