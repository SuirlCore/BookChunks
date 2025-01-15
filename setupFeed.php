<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form with Frames</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
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
    </script>
</head>
<body>
    <div id="top-frame">
        <?php include 'navigation.php'; ?>
        <form onsubmit="navigateToPage(); return false;">
            <label for="action-select">Choose an action:</label>
            <select id="action-select">
                <option value="uploadPage.php">Upload a Book</option>
                <option value="updateFeed.php">Create or Delete a Feed</option>
                <option value="updateBooks.php">Add or Remove Books from Feeds</option>
            </select>
            <button type="submit">Go</button>
        </form>
    </div>
    <iframe id="bottom-frame"></iframe>
</body>
</html>
