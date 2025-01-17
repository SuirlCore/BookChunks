<?php
// Database connection
include 'scripts/pdo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session and retrieve user ID
session_start();
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$userID = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Book Chunks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .results {
            margin-top: 20px;
        }
        .result-item {
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        .form-control {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Search Book Chunks</h1>

    <form id="feedForm" method="POST">
        <label for="feedID">Choose a feed:</label>
        <select name="feedID" id="feedID" required>
            <option value="">Select a feed</option>
            <?php
            // Connect to the database
            $mysqli = new mysqli($servername, $username, $password, $dbname);

            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }

            $stmt = $mysqli->prepare("SELECT feedID, feedName FROM feeds WHERE userID = ?");
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $feedResult = $stmt->get_result();
            while ($feed = $feedResult->fetch_assoc()) {
                echo "<option value='" . $feed['feedID'] . "'>" . htmlspecialchars($feed['feedName']) . "</option>";
            }
            ?>
        </select>
        <br><br>
        <button type="submit">Next</button>
    </form>

    <div class="results" id="results">
        <!-- Results will be shown here -->
    </div>

    <script>
        document.getElementById('feedForm').addEventListener('submit', function(e) {
            e.preventDefault();  // Prevent form from submitting

            var feedID = document.getElementById('feedID').value;

            if (feedID) {
                // Fetch books for the selected feed
                fetch(`scripts/searchFetchBooks.php?feedID=${feedID}`)
                    .then(response => response.json())
                    .then(data => {
                        let booksHTML = `<form id="bookForm" method="POST">`;
                        booksHTML += `<label for="bookID">Choose a book:</label>`;
                        booksHTML += `<select name="bookID" id="bookID" required>`;
                        booksHTML += `<option value="">Select a book</option>`;
                        data.books.forEach(book => {
                            booksHTML += `<option value="${book.textID}">${book.filename}</option>`;
                        });
                        booksHTML += `</select>`;
                        booksHTML += `<br><br>`;
                        booksHTML += `<label for="search">Search:</label>`;
                        booksHTML += `<input type="text" id="search" name="search" required>`;
                        booksHTML += `<br><br>`;
                        booksHTML += `<button type="submit">Search</button>`;
                        booksHTML += `</form>`;

                        document.getElementById('results').innerHTML = booksHTML;

                        document.getElementById('bookForm').addEventListener('submit', function(e) {
                            e.preventDefault();  // Prevent form from submitting

                            var bookID = document.getElementById('bookID').value;
                            var searchText = document.getElementById('search').value;

                            if (bookID && searchText) {
                                // Perform search using AJAX
                                fetch(`scripts/searchSearchChunks.php?bookID=${bookID}&search=${searchText}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.results.length > 0) {
                                            let resultHTML = `<form id="resultForm" method="POST">`;
                                            data.results.forEach(result => {
                                                resultHTML += `<div class='result-item'>`;
                                                resultHTML += `<input type='radio' name='chunkID' value='${result.chunkID}' required> ${result.chunkContent}`;
                                                resultHTML += `</div>`;
                                            });
                                            resultHTML += `<input type='hidden' name='bookID' value='${bookID}'>`;
                                            resultHTML += `<input type='hidden' name='feedID' value='${feedID}'>`;
                                            resultHTML += `<button type='submit'>Select</button>`;
                                            resultHTML += `</form>`;

                                            document.getElementById('results').innerHTML = resultHTML;

                                            document.getElementById('resultForm').addEventListener('submit', function(e) {
                                                e.preventDefault();  // Prevent form from submitting

                                                var chunkID = document.querySelector('input[name="chunkID"]:checked').value;
                                                var bookID = document.getElementById('bookID').value;
                                                var feedID = document.getElementById('feedID').value;

                                                // Send chunkID, bookID, and feedID to update or create userFeedProgress
                                                fetch('scripts/searchUpdateProgress.php', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                    },
                                                    body: `chunkID=${chunkID}&bookID=${bookID}&feedID=${feedID}`
                                                })
                                                .then(response => response.json())
                                                .then(data => {
                                                    alert(data.message);
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                });
                                            });
                                        } else {
                                            document.getElementById('results').innerHTML = `<p>No results found.</p>`;
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                    });
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    </script>
</body>
</html>
