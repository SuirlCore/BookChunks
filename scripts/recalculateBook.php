<?php
//start session and ensure user is logged in.
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Ensure maxWordsPerChunk is set
if (!isset($_SESSION['maxWordsPerChunk'])) {
    die('Max words per chunk is not set in the session.');
}

// Check if book_id is provided
if (!isset($_POST['book_id'])) {
    die('Book ID is not provided.');
}

$book_id = intval($_POST['book_id']);
$maxWordsPerChunk = $_SESSION['maxWordsPerChunk'];

// Function to parse text into chunks
function parseTextToSections($text, $maxWords) {
    $sentences = preg_split('/(?<=[.!?])\s+/', $text);
    $sections = [];
    $currentSection = '';
    $currentWordCount = 0;

    foreach ($sentences as $sentence) {
        $wordCount = str_word_count($sentence);

        if ($currentWordCount + $wordCount > $maxWords) {
            $sections[] = trim($currentSection);
            $currentSection = $sentence;
            $currentWordCount = $wordCount;
        } else {
            $currentSection .= ' ' . $sentence;
            $currentWordCount += $wordCount;
        }
    }

    if (!empty(trim($currentSection))) {
        $sections[] = trim($currentSection);
    }

    return $sections;
}

include 'pdo.php';

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if the book is in any feed
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM booksInFeed WHERE bookID = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['count'] > 0) {
        throw new Exception("This book is currently part of a feed. Please delete the feed before recalculating the book.");
    }

    // Fetch all chunks for the given book ID
    $stmt = $conn->prepare("SELECT chunkID, chunkContent FROM bookChunks WHERE bookID = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chunks = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($chunks)) {
        throw new Exception("No chunks found for the given book ID.");
    }

    // Combine all chunk contents into a single variable
    $fullText = '';
    foreach ($chunks as $chunk) {
        $fullText .= ' ' . $chunk['chunkContent'];
    }

    // Parse the combined text into new chunks
    $newChunks = parseTextToSections($fullText, $maxWordsPerChunk);

    // Delete the old chunks for the book
    $stmt = $conn->prepare("DELETE FROM bookChunks WHERE bookID = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();

    // Insert the new chunks
    $stmt = $conn->prepare("INSERT INTO bookChunks (bookID, chunkNum, chunkContent, hasBeenSeen) VALUES (?, ?, ?, 0)");
    foreach ($newChunks as $index => $chunkContent) {
        $chunkNum = $index + 1;
        $stmt->bind_param("iis", $book_id, $chunkNum, $chunkContent);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    $_SESSION['upload_message'] = "Book recalculated successfully.";
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    die("Error: " . $e->getMessage());
} finally {
    $stmt->close();
    $conn->close();
    header("Location: ../uploadPage.php");
}
?>
