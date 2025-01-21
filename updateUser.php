<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection
include 'scripts/pdo.php'; // Include your database connection file

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = intval($_POST['userID']);
    $userName = $conn->real_escape_string($_POST['userName']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $realFirstName = $conn->real_escape_string($_POST['realFirstName']);
    $realLastName = $conn->real_escape_string($_POST['realLastName']);
    $email = $conn->real_escape_string($_POST['email']);

    $updateQuery = "UPDATE users SET 
        userName = '$userName', 
        pass = '$password', 
        realFirstName = '$realFirstName', 
        realLastName = '$realLastName', 
        email = '$email' 
        WHERE userID = $userID";

    if ($conn->query($updateQuery) === TRUE) {
        $message = "User updated successfully!";
    } else {
        $message = "Error updating user: " . $conn->error;
    }
}

// Fetch user details (assuming `userID` is passed as a GET parameter)
$userID = $_SESSION['user_id'];
$userQuery = "SELECT * FROM users WHERE userID = $userID";
$result = $conn->query($userQuery);
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 10px;
            text-align: center;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Your Profile</h1>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST" id="updateForm">
            <input type="hidden" name="userID" value="<?= htmlspecialchars($user['userID']); ?>">
            <label for="userName">Username:</label>
            <input type="text" name="userName" id="userName" value="<?= htmlspecialchars($user['userName']); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            
            <label for="realFirstName">First Name:</label>
            <input type="text" name="realFirstName" id="realFirstName" value="<?= htmlspecialchars($user['realFirstName']); ?>" required>
            
            <label for="realLastName">Last Name:</label>
            <input type="text" name="realLastName" id="realLastName" value="<?= htmlspecialchars($user['realLastName']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            
            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
