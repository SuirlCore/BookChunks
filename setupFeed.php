<?php 
    // Start session and retrieve user ID
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.html"); // Redirect to login page if not logged in
        exit();
    }
    $userID = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Feed</title>
    <style>
         :root {
            --font-color: <?= isset($_SESSION['fontColor']) ? htmlspecialchars($_SESSION['fontColor']) : '#000000'; ?>;
            --background-color: <?= isset($_SESSION['backgroundColor']) ? htmlspecialchars($_SESSION['backgroundColor']) : '#FFFFFF'; ?>;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--font-color);
            background-color: var(--background-color);
        }

        #top-frame {
            height: 20%;
            width: 100%;
            color: var(--font-color);
            background-color: var(--background-color);
        }

        #bottom-frame {
            height: 80%;
            width: 100%;
            border: none;
        }
    </style>
    <script>
        function navigateToPage() {
            const selection = document.getElementById("action-select").value;
            const iframe = document.getElementById("bottom-frame");
            iframe.src = selection;
        }

        // Automatically load the first option on page load
        window.onload = function() {
            navigateToPage();
        };
    </script>
</head>
<body>
    <div id="top-frame">
        <?php include 'navigation.php'; ?>
        <form onsubmit="navigateToPage(); return false;">
            <label for="action-select">Choose an action:</label>
            <select id="action-select">
                <option value="uploadPage.php">Step 1: Upload a Book</option>
                <option value="updateFeed.php">Step 2: Create or Delete a Feed</option>
                <option value="updateBooks.php">Step 3: Add or Remove Books from Feeds</option>
                <option value="searchChunk.php">Step 4: Change Start Point of Feed</option>
            </select>
            <button type="submit">Go</button>
        </form>
    </div>
    <iframe id="bottom-frame"></iframe>
</body>
</html>
