<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

include 'pdo.php';

// Function to upload a section into the database
function uploadSectionToDB($conn, $textID, $sectionNumber, $sectionText) {
    $stmt = $conn->prepare("INSERT INTO bookChunks (bookID, chunkNum, chunkContent, hasBeenSeen) VALUES (?, ?, ?, 0)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
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
        $_SESSION['upload_message'] = "Please upload a valid text file.";
        header("Location: ../uploadPage.php");
        exit();
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

    // Insert the uploaded file into the texts table and get the textID
    $fileName = $file['name'];
    $stmt = $conn->prepare("INSERT INTO fullTexts (filename, owner) VALUES (?, ?)");
    if (!$stmt) {
        $_SESSION['upload_message'] = "Failed to prepare the database statement.";
        header("Location: ../uploadPage.php");
        exit();
    }
    $stmt->bind_param("si", $fileName, $userID);
    if (!$stmt->execute()) {
        $_SESSION['upload_message'] = "Failed to execute the database statement.";
        header("Location: ../uploadPage.php");
        exit();
    }
    $textID = $stmt->insert_id;
    $stmt->close();

    $_SESSION['upload_message'] = "Uploading sections...";

    // Upload each section to the database, numbered sequentially, and linked to textID
    $sectionNumber = 1;
    $success = true;

    foreach ($sections as $section) {
        try {
            uploadSectionToDB($conn, $textID, $sectionNumber, $section);
        } catch (Exception $e) {
            $_SESSION['upload_message'] = "Failed to upload section $sectionNumber: " . $e->getMessage();
            $success = false;
            break;
        }
        $sectionNumber++;
    }

    // Close the database connection
    $conn->close();

    if ($success) {
        $_SESSION['upload_message'] = "File uploaded successfully.";
        header("Location: ../uploadPage.php");
        exit();
    } else {
        $_SESSION['upload_message'] = "An error occurred while uploading the text. Please try again.";
        header("Location: ../uploadPage.php");
        exit();
    }
} else {
    $_SESSION['upload_message'] = "No file uploaded.";
    header("Location: ../uploadPage.php");
    exit();
}
?>
