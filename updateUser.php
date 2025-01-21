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

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
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
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . $conn->error;
    }
}

// Handle settings update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateSettings'])) {
    $userID = intval($_POST['userID']);
    $fontSize = $conn->real_escape_string($_POST['fontSize']);
    $fontColor = $conn->real_escape_string($_POST['fontColor']);
    $backgroundColor = $conn->real_escape_string($_POST['backgroundColor']);

    $settingsQuery = "UPDATE users SET 
        fontSize = '$fontSize', 
        fontColor = '$fontColor', 
        backgroundColor = '$backgroundColor' 
        WHERE userID = $userID";

    if ($conn->query($settingsQuery) === TRUE) {
        $settingsMessage = "Settings updated successfully!";
    } else {
        $settingsMessage = "Error updating settings: " . $conn->error;
    }
}

// Fetch user details using prepared statements
$userID = $_SESSION['user_id'];

// Prepare the SQL statement
$stmt = $conn->prepare("SELECT userID, userName, pass, realFirstName, realLastName, email, fontSize, fontColor, backgroundColor FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);

// Execute the query
$stmt->execute();

// Fetch the result
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Close the statement
$stmt->close();

$userNameIn = $user['userName'];
$realFirstNameIn = $user['realFirstName'];
$realLastNameIn = $user['realLastName'];
$emailIn = $user['email'];
$fontSizeIn = $user['fontSize'];
$fontColorIn = $user['fontColor'];
$backgroundColorIn = $user['backgroundColor'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User and Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: <?= htmlspecialchars($backgroundColorIn); ?>;
            color: <?= htmlspecialchars($fontColorIn); ?>;
            font-size: <?= htmlspecialchars($fontSizeIn); ?>;
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
        input[type="text"], input[type="email"], input[type="password"], select {
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
    <?php include 'navigation.php'; ?>
    <div class="container">
        <h1>Update Your Profile</h1>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="userID" value="<?= htmlspecialchars($userID); ?>">
            <label for="userName">Username:</label>
            <input type="text" name="userName" id="userName" value="<?= htmlspecialchars($userNameIn); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            
            <label for="realFirstName">First Name:</label>
            <input type="text" name="realFirstName" id="realFirstName" value="<?= htmlspecialchars($realFirstNameIn); ?>" required>
            
            <label for="realLastName">Last Name:</label>
            <input type="text" name="realLastName" id="realLastName" value="<?= htmlspecialchars($realLastNameIn); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($emailIn); ?>" required>
            
            <button type="submit" name="updateProfile">Update Profile</button>
        </form>

        <h1>Customize Your Settings</h1>
        <?php if (isset($settingsMessage)): ?>
            <div class="message"><?= htmlspecialchars($settingsMessage); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="userID" value="<?= $userID; ?>">
            
            <label for="fontSize">Font Size:</label>
            <select name="fontSize" id="fontSize">
                <option value="12px" <?= $user['fontSize'] === '12px' ? 'selected' : ''; ?>>12px</option>
                <option value="14px" <?= $user['fontSize'] === '14px' ? 'selected' : ''; ?>>14px</option>
                <option value="16px" <?= $user['fontSize'] === '16px' ? 'selected' : ''; ?>>16px</option>
                <option value="18px" <?= $user['fontSize'] === '18px' ? 'selected' : ''; ?>>18px</option>
                <option value="20px" <?= $user['fontSize'] === '20px' ? 'selected' : ''; ?>>20px</option>
            </select>

            <label for="fontColor">Font Color:</label>
            <select name="fontColor" id="fontColor">
                <?php
                $colors = [
                    "#000000", "#FFFFFF", "#FF0000", "#00FF00", "#0000FF",
                    "#FFFF00", "#FF00FF", "#00FFFF", "#C0C0C0", "#808080",
                    "#800000", "#808000", "#008000", "#800080", "#008080", "#000080"
                ];
                foreach ($colors as $color) {
                    $selected = $fontColorIn === $color ? 'selected' : '';
                    echo "<option value='$color' $selected>$color</option>";
                }
                ?>
            </select>

            <label for="backgroundColor">Background Color:</label>
            <select name="backgroundColor" id="backgroundColor">
                <?php
                foreach ($colors as $color) {
                    $selected = $backgroundColorIn === $color ? 'selected' : '';
                    echo "<option value='$color' $selected>$color</option>";
                }
                ?>
            </select>
            
            <button type="submit" name="updateSettings">Update Settings</button>
        </form>
    </div>
</body>
</html>
