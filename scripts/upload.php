<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

include 'scripts/pdo.php';

// Function to upload a section into the database
function uploadSectionToDB($dbConn, $textID, $sectionNumber, $sectionText) {
    $stmt = $dbConn->prepare("INSERT INTO bookChunks (bookID, chunkNum, chunkContent, hasBeenSeen) VALUES (?, ?, ?, 0)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $dbConn->error);
    }
    $stmt->bind_param("iis", $textID, $sectionNumber, $sectionText);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
}

// Function to parse the text into sections of 3 sentences
function parseTextToSections($text) {
    $sentences = preg_split('/(?<=[.!?])\s+/', $text);
    $sections = [];
    $currentSection = '';
    $currentWordCount = 0;

    foreach ($sentences as $sentence) {
        $wordCount = str_word_count($sentence);

        // Check if adding this sentence would exceed 50 words
        if ($currentWordCount + $wordCount > 50) {
            // Add the current section to the list and reset
            $sections[] = trim($currentSection);
            $currentSection = $sentence;
            $currentWordCount = $wordCount;
        } else {
            // Append the sentence to the current section
            $currentSection .= ' ' . $sentence;
            $currentWordCount += $wordCount;
        }
    }

    // Add the last section if it contains text
    if (!empty(trim($currentSection))) {
        $sections[] = trim($currentSection);
    }

    return $sections;
}


// Check if a file is uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['text_file'])) {
    $file = $_FILES['text_file'];

    // Ensure the file is a .txt file
    if ($file['type'] !== 'text/plain') {
        die("Please upload a valid text file.");
    }

    // Read the content of the file
    $text = file_get_contents($file['tmp_name']);

    // Detect file encoding
    $encoding = mb_detect_encoding($text, "UTF-8, ISO-8859-1, ISO-8859-15", true);

    // Convert to UTF-8 if necessary
    if ($encoding !== 'UTF-8') {
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
    }

    // Parse the text into sections of 3 sentences
    $sections = parseTextToSections($text);

    $dbConn = new mysqli($servername, $username, $password, $dbname);

    // Check if connection was successful
    if ($dbConn->connect_error) {
        die("Connection failed: " . $dbConn->connect_error);
    }

    // Insert the uploaded file into the texts table and get the textID
    $fileName = $file['name'];
    $stmt = $dbConn->prepare("INSERT INTO fullTexts (filename, owner) VALUES (?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $dbConn->error);
    }
    $stmt->bind_param("si", $fileName, $userID);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $textID = $stmt->insert_id;
    $stmt->close();

    // Upload each section to the database, numbered sequentially, and linked to textID
    $sectionNumber = 1;
    $success = true;

    foreach ($sections as $section) {
        try {
            uploadSectionToDB($dbConn, $textID, $sectionNumber, $section);
        } catch (Exception $e) {
            echo "Failed to upload section $sectionNumber: " . $e->getMessage() . "<br>";
            $success = false;
            break;
        }
        $sectionNumber++;
    }

    // Close the database connection
    $dbConn->close();

    if ($success) {
        // Redirect to uploadPage.php if successful
        header("Location: ../uploadPage.php");
        exit;
    } else {
        echo "An error occurred while uploading the text. Please try again.";
    }
} else {
    echo "No file uploaded.";
}
?>
