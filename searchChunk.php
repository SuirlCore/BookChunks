<?php
// Database connection
include 'scripts/pdo.php';

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
    <p>
        If you want to start scrolling part way through a book, search through here and choose where you want your
        feed to start at.
    </p>

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
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('feedForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var feedID = document.getElementById('feedID');
        if (feedID && feedID.value !== "") {
            fetch(`scripts/searchFetchBooks.php?feedID=${feedID.value}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.books && data.books.length > 0) {
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
                            e.preventDefault();

                            var bookID = document.getElementById('bookID');
                            var searchText = document.getElementById('search');
                            if (bookID && searchText && bookID.value !== "" && searchText.value !== "") {
                                fetch(`scripts/searchSearchChunks.php?bookID=${bookID.value}&search=${searchText.value}`)
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.results && data.results.length > 0) {
                                            let resultHTML = `<form id="resultForm" method="POST">`;
                                            data.results.forEach(result => {
                                                resultHTML += `<div class='result-item'>`;
                                                resultHTML += `<input type='radio' name='chunkID' value='${result.chunkID}' required> ${result.chunkContent}`;
                                                resultHTML += `</div>`;
                                            });
                                            resultHTML += `<input type='hidden' name='bookID' value='${bookID.value}'>`;
                                            resultHTML += `<input type='hidden' name='feedID' value='${feedID.value}'>`;
                                            resultHTML += `<button type='submit'>Select</button>`;
                                            resultHTML += `</form>`;

                                            document.getElementById('results').innerHTML = resultHTML;

                                            document.getElementById('resultForm').addEventListener('submit', function(e) {
                                                e.preventDefault();

                                                var chunkID = document.querySelector('input[name="chunkID"]:checked');
                                                if (chunkID) {
                                                    fetch('scripts/searchUpdateProgress.php', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                        },
                                                        body: `chunkID=${chunkID.value}&bookID=${bookID.value}&feedID=${feedID.value}`
                                                    })
                                                    .then(response => {
                                                        if (!response.ok) {
                                                            throw new Error('Network response was not ok');
                                                        }
                                                        return response.json();
                                                    })
                                                    .then(data => {
                                                        alert(data.message);
                                                    })
                                                    .catch(error => {
                                                        console.error('Error:', error);
                                                    });
                                                }
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
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });
});

    </script>
</body>
</html>
