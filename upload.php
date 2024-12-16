<?php
session_start();
$userID = $_SESSION['user_id'];

include 'pdo.php';

// Include the pdfparser library
require 'vendor/autoload.php'; // Include Composer autoload or adjust the path accordingly

use Smalot\PdfParser\Parser;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdfFile'])) {
    // Check if the file is a valid PDF
    $file = $_FILES['pdfFile'];
    if ($file['type'] != 'application/pdf') {
        echo "Please upload a valid PDF file.";
        exit;
    }

    // Move the uploaded file to a temporary location
    $filePath = 'uploads/' . basename($file['name']);
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo "Error uploading the file.";
        exit;
    }

    // Parse the PDF to extract text
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    $text = $pdf->getText();

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare and execute the SQL query to insert the PDF text into the database
        $stmt = $pdo->prepare("INSERT INTO fullTexts (filename, text, owner) VALUES (:filename, :text, '$userID');");
        $stmt->bindParam(':filename', $file['name']);
        $stmt->bindParam(':text', $text);
        $stmt->execute();

        echo "PDF uploaded and text stored successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No file uploaded.";
}
?>
