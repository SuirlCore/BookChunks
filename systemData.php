<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}
// Database connection
include 'scripts/pdo.php';

// Connect to the database using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch required data
$query = "
    SELECT 
        users.userName, 
        users.numChunksSeen, 
        feeds.feedName, 
        userFeedProgress.lastSeenChunkID, 
        userFeedProgress.dateTimeLastSeen, 
        fullTexts.filename
    FROM 
        userFeedProgress
    JOIN 
        users ON userFeedProgress.userID = users.userID
    JOIN 
        feeds ON userFeedProgress.feedID = feeds.feedID
    JOIN 
        bookChunks ON userFeedProgress.lastSeenChunkID = bookChunks.chunkID
    JOIN 
        fullTexts ON bookChunks.bookID = fullTexts.textID
    ORDER BY 
        userFeedProgress.dateTimeLastSeen DESC;
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feed Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            font-weight: bold;
            color: #555;
        }
    </style>
    <script>
        // Reload the page every 5 seconds
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    </script>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <h1>User Feed Progress</h1>
    <?php if ($result && $result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Chunks Seen</th>
                <th>Feed Name</th>
                <th>Last Seen Chunk ID</th>
                <th>Date & Time Last Seen</th>
                <th>Time Since Last Seen</th>
                <th>Filename</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                // Calculate time since last seen
                $dateTimeLastSeen = new DateTime($row['dateTimeLastSeen']);
                $now = new DateTime();
                $timeDiff = $now->diff($dateTimeLastSeen);

                $timeSinceLastSeen = $timeDiff->format('%d days, %h hours, %i minutes, %s seconds');
            ?>
            <tr>
                <td><?= htmlspecialchars($row['userName']) ?></td>
                <td><?= htmlspecialchars($row['numChunksSeen']) ?></td>
                <td><?= htmlspecialchars($row['feedName']) ?></td>
                <td><?= htmlspecialchars($row['lastSeenChunkID']) ?></td>
                <td><?= htmlspecialchars($row['dateTimeLastSeen']) ?></td>
                <td><?= htmlspecialchars($timeSinceLastSeen) ?></td>
                <td><?= htmlspecialchars($row['filename']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="no-data">No data available to display.</p>
    <?php endif; ?>

    <?php
    // Free result set and close connection
    if ($result) $result->free();
    $conn->close();
    ?>
</body>
</html>
