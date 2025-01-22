<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// File path
$filePath = './metadata/onTheRadar.txt';

// Initialize a variable to store file contents
$fileContents = '';

// Check if the file exists
if (file_exists($filePath)) {
    // Read the file contents
    $fileContents = file_get_contents($filePath);
} else {
    $fileContents = "File not found: " . htmlspecialchars($filePath);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Notes</title>
    <style>
        :root {
            --font-color: <?= isset($_SESSION['fontColor']) ? htmlspecialchars($_SESSION['fontColor']) : '#000000'; ?>;
            --background-color: <?= isset($_SESSION['backgroundColor']) ? htmlspecialchars($_SESSION['backgroundColor']) : '#FFFFFF'; ?>;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--font-color); /* Dynamic font color */
            background-color: var(--background-color); /* Dynamic background color */
        }

        h1, h2, p, label {
            color: var(--font-color); /* Use dynamic text color */
        }

        .file-content {
            border: 2px solid var(--font-color); /* Border matches the text color */
            border-radius: 5px;
            padding: 15px;
            background-color: var(--background-color); /* Dynamic background color */
            font-size: 16px;
            line-height: 1.5;
            color: var(--font-color); /* Dynamic text color */
            overflow-x: auto;
            max-width: 100%;
            white-space: pre-wrap; /* Preserve newlines and spaces */
        }

        .refresh-button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: var(--font-color); /* Dynamic button background */
            color: var(--background-color); /* Dynamic button text color */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .refresh-button:hover {
            background-color: #555; /* Slightly darker color on hover */
        }

        form {
            background-color: var(--background-color); /* Dynamic form background */
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid var(--font-color); /* Match input border to font color */
            border-radius: 4px;
            /*color: var(--font-color); /* Match input text to font color */
            /*background-color: var(--background-color); /* Match input background to page background */
        }

        button[type="submit"] {
            padding: 10px 15px;
            background-color: var(--font-color); /* Button matches font color */
            color: var(--background-color); /* Button text matches background color */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #555; /* Slightly darker color on hover */
        }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <!-- Form for user recommendations -->
    <form id="recommendationForm" method="POST">
        <h2>Submit a Recommendation for website updates</h2>
        <p>
            Or let us know if you found a bug, or something doesnt work right. Please provide
            as much information as possible.
        </p>
        <label for="recommendationText">Recommendation:</label>
        <input type="text" id="recommendationText" name="recommendationText" required>
        <button type="submit">Submit Recommendation</button>
    </form>

    <?php

    // Handle the recommendation form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recommendationText'])) {
        $recommendationText = trim($_POST['recommendationText']);
        $userID = $_SESSION['user_id'];

        if ($recommendationText !== '') {
            // Database connection
            include 'scripts/pdo.php';

            // Prepare the SQL statement to insert the recommendation
            $stmt = $mysqli->prepare("INSERT INTO userRecomendations (userID, recomendationText) VALUES (?, ?)");
            $stmt->bind_param("is", $userID, $recommendationText);
            
            if ($stmt->execute()) {
                echo "<p>Thank you for your recommendation!</p>";
            } else {
                echo "<p>There was an error submitting your recommendation. Please try again.</p>";
            }

            $stmt->close();
            $mysqli->close();
        } else {
            echo "<p>Please enter a recommendation.</p>";
        }
    }
    ?>

    <h1>Dev Notes</h1>
    <p>
        Items that are being worked on, or on the radar that need to be worked on.
    </p>
    <div class="file-content">
        <?= nl2br(htmlspecialchars($fileContents)) ?>
    </div>
    <button class="refresh-button" onclick="refreshPage()">Refresh</button>

</body>
</html>
