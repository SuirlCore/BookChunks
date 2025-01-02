<?php
session_start();
$userID = $_SESSION['user_id'];
echo $userID;
echo '<br>';

include 'pdo.php';
echo 'test 1<br>';
// Function to upload a section into the database
function uploadSectionToDB($dbConn, $textID, $sectionNumber, $sectionText) {
    $stmt = $dbConn->prepare("INSERT INTO bookChunks (bookID, chunkNum, chunkContent, hasBeenSeen) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iis", $textID, $sectionNumber, $sectionText);
    $stmt->execute();
    $stmt->close();
}
echo 'test 2<br>';
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

    echo "Total sections: " . count($sections) . "<br>";

    return $sections;
}
echo 'test 3<br>';
// Check if a file is uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['text_file'])) {
    $file = $_FILES['text_file'];

    // Ensure the file is a .txt file
    if ($file['type'] !== 'text/plain') {
        die("Please upload a valid text file.");
    }
    echo 'test 4<br>';
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
    echo 'test 5<br>';
    // Insert the uploaded file into the texts table and get the textID
    $fileName = $file['name'];  // Get the file name
    $stmt = $dbConn->prepare("INSERT INTO fullTexts (filename, owner) VALUES (?, ?)");
    $stmt->execute([$fileName, $userID]);
    $textID = $dbConn->insert_id;;  // Get the generated textID for the uploaded file
    $stmt->close();
    echo $textID;

    echo '<br>';
    echo 'test 6<br>';
    // Upload each section to the database, numbered sequentially, and linked to textID
    $sectionNumber = 1;
    foreach ($sections as $section) {
        echo 'sectionNumber = '. $sectionNumber. '<br>';
        uploadSectionToDB($dbConn, $textID, $sectionNumber, $section);
        echo "Uploaded section $sectionNumber: " . substr($section, 0, 50) . "...<br>";  // Preview of the first 50 characters
        $sectionNumber++;
    }
    echo 'test 7<br>';
    // Close the database connection
    $dbConn->close();
} else {
    echo "No file uploaded.";
}
?>
