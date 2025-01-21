<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Feed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
        }
        #top-frame {
            height: 20%;
            width: 100%;
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
