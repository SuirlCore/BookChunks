<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'pdo.php'; // Replace with your database connection file
$userID = $_SESSION['user_id'];

// Fetch collections owned by the user
$collections = [];
try {
    $stmt = $conn->prepare("SELECT collectionID, collectionName FROM collections WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    die("Error fetching collections: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create_flashcard') {
            // Add a new flashcard
            $cardName = $_POST['card_name'];
            $cardContent = $_POST['card_content'];
            $cardAnswer = $_POST['card_answer'];
            $collectionID = $_POST['collection_id'];

            if ($cardName && $cardContent && $cardAnswer && $collectionID) {
                try {
                    $stmt = $conn->prepare("INSERT INTO flashCards (userID, cardName, cardContent, cardAnswer) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $userID, $cardName, $cardContent, $cardAnswer);
                    $stmt->execute();
                    $cardID = $stmt->insert_id;

                    // Add flashcard to the selected collection
                    $stmt = $conn->prepare("INSERT INTO itemsInCollection (collectionID, itemID, positionID) VALUES (?, ?, ?)");
                    $positionID = 0; // Adjust as needed
                    $stmt->bind_param("iii", $collectionID, $cardID, $positionID);
                    $stmt->execute();

                    $stmt->close();
                    $successMessage = "Flashcard created and added to the collection.";
                } catch (Exception $e) {
                    $errorMessage = "Error adding flashcard: " . $e->getMessage();
                }
            } else {
                $errorMessage = "All fields are required.";
            }
        } elseif ($_POST['action'] === 'add_collection') {
            // Add a new collection
            $collectionName = $_POST['collection_name'];
            if ($collectionName) {
                try {
                    $stmt = $conn->prepare("INSERT INTO collections (collectionName, collectionType, userID) VALUES (?, 'flashcards', ?)");
                    $stmt->bind_param("si", $collectionName, $userID);
                    $stmt->execute();
                    $stmt->close();
                    $successMessage = "Collection added successfully.";
                } catch (Exception $e) {
                    $errorMessage = "Error adding collection: " . $e->getMessage();
                }
            } else {
                $errorMessage = "Collection name is required.";
            }
        } elseif ($_POST['action'] === 'delete_collection') {
            // Delete a collection
            $collectionID = $_POST['collection_id'];
            if ($collectionID) {
                try {
                    $stmt = $conn->prepare("DELETE FROM collections WHERE collectionID = ? AND userID = ?");
                    $stmt->bind_param("ii", $collectionID, $userID);
                    $stmt->execute();
                    $stmt->close();
                    $successMessage = "Collection deleted successfully.";
                } catch (Exception $e) {
                    $errorMessage = "Error deleting collection: " . $e->getMessage();
                }
            } else {
                $errorMessage = "Collection ID is required.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcard Manager</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea, select, button { width: 100%; padding: 10px; margin-bottom: 10px; }
        .message { margin-top: 20px; padding: 10px; border: 1px solid; }
        .success { color: green; border-color: green; }
        .error { color: red; border-color: red; }
    </style>
</head>
<body>
    <h1>Manage Flashcards</h1>
    
    <!-- Flashcard Creation Form -->
    <form method="POST">
        <h2>Create a Flashcard</h2>
        <div class="form-group">
            <label for="card_name">Flashcard Name:</label>
            <input type="text" id="card_name" name="card_name" required>
        </div>
        <div class="form-group">
            <label for="card_content">Flashcard Content:</label>
            <textarea id="card_content" name="card_content" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="card_answer">Flashcard Answer:</label>
            <textarea id="card_answer" name="card_answer" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="collection_id">Select Collection:</label>
            <select id="collection_id" name="collection_id" required>
                <option value="">-- Select Collection --</option>
                <?php foreach ($collections as $collection): ?>
                    <option value="<?= $collection['collectionID'] ?>"><?= $collection['collectionName'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="action" value="create_flashcard">
        <button type="submit">Create Flashcard</button>
    </form>

    <!-- Add Collection Form -->
    <form method="POST">
        <h2>Add a New Collection</h2>
        <div class="form-group">
            <label for="collection_name">Collection Name:</label>
            <input type="text" id="collection_name" name="collection_name" required>
        </div>
        <input type="hidden" name="action" value="add_collection">
        <button type="submit">Add Collection</button>
    </form>

    <!-- Delete Collection Form -->
    <form method="POST">
        <h2>Delete a Collection</h2>
        <div class="form-group">
            <label for="delete_collection_id">Select Collection to Delete:</label>
            <select id="delete_collection_id" name="collection_id" required>
                <option value="">-- Select Collection --</option>
                <?php foreach ($collections as $collection): ?>
                    <option value="<?= $collection['collectionID'] ?>"><?= $collection['collectionName'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="action" value="delete_collection">
        <button type="submit" style="background-color: red; color: white;">Delete Collection</button>
    </form>

    <!-- Feedback Messages -->
    <?php if (!empty($successMessage)): ?>
        <div class="message success"><?= $successMessage ?></div>
    <?php elseif (!empty($errorMessage)): ?>
        <div class="message error"><?= $errorMessage ?></div>
    <?php endif; ?>
</body>
</html>
