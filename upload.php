<?php
session_start();
$userID = $_SESSION['user_id'];

include 'pdo.php';

// Function to upload a section into the database
function uploadSectionToDB($dbConn, $textID, $sectionNumber, $sectionText) {
    $stmt = $dbConn->prepare("INSERT INTO bookChunks (bookID, chunkNum, chunkContent, hasBeenSeen) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iis", $textID, $sectionNumber, $sectionText);
    $stmt->execute();
    $stmt->close();
}

// Function to parse the text into sections of 3 sentences
function parseTextToSections($text) {
    // Split text by sentence-ending punctuation (.!?)
    $sentences = preg_split('/(?<=[.!?])\s+/', $text);
    $sections = [];
    
    // Group sentences into sections of 3 sentences
    for ($i = 0; $i < count($sentences); $i += 3) {
        $section = implode(' ', array_slice($sentences, $i, 3));
        $sections[] = $section;
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

    // Parse the text into sections of 3 sentences
    $sections = parseTextToSections($text);

    // Database connection
    $dbHost = 'localhost';      // Database host
    $dbUser = 'root';           // Database user
    $dbPassword = '';           // Database password
    $dbName = 'your_database';  // Database name

    $dbConn = new mysqli($servername, $username, $password, $bookChunk);

    // Check if connection was successful
    if ($dbConn->connect_error) {
        die("Connection failed: " . $dbConn->connect_error);
    }

    // Insert the uploaded file into the texts table and get the textID
    $fileName = $file['name'];  // Get the file name
    $stmt = $dbConn->prepare("INSERT INTO fullTexts (filename, owner) VALUES (?, ?)");
    $stmt->bind_param("ss", $fileName, $userID);
    $stmt->execute();
    $textID = $stmt->insert_id;  // Get the generated textID for the uploaded file
    $stmt->close();

    // Upload each section to the database, numbered sequentially, and linked to textID
    $sectionNumber = 1;
    foreach ($sections as $section) {
        uploadSectionToDB($dbConn, $textID, $sectionNumber, $section);
        echo "Uploaded section $sectionNumber: " . substr($section, 0, 50) . "...<br>";  // Preview of the first 50 characters
        $sectionNumber++;
    }

    // Close the database connection
    $dbConn->close();
} else {
    echo "No file uploaded.";
}
?>
