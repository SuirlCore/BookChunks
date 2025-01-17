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
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        .file-content {
            border: 2px solid #007BFF;
            border-radius: 5px;
            padding: 15px;
            background-color: #fff;
            font-size: 16px;
            line-height: 1;
            color: #333;
            overflow-x: auto;
            max-width: 100%;
            white-space: pre-wrap; /* Preserve newlines and spaces */
        }
        .refresh-button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .refresh-button:hover {
            background-color: #0056b3;
        }
        form {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type="submit"] {
            padding: 10px 15px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>

    <!-- Form for user recommendations -->
    <form id="recommendationForm" method="POST">
        <h2>Submit Your Recommendation for website updates</h2>
        <label for="recommendationText">Recommendation:</label>
        <input type="text" id="recommendationText" name="recommendationText" required>
        <button type="submit">Submit Recommendation</button>
    </form>

    <?php
    // Database connection
    include 'scripts/pdo.php';

    // Handle the recommendation form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recommendationText'])) {
        $recommendationText = trim($_POST['recommendationText']);
        $userID = $_SESSION['user_id'];

        if ($recommendationText !== '') {
            // Connect to database
            $mysqli = new mysqli($servername, $username, $password, $dbname);

            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

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
