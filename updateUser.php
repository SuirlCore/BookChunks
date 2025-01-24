<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
include 'scripts/pdo.php';

// Fetch user details
$userID = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT userName, realFirstName, realLastName, pass, email, fontSize, fontSelect, fontColor, backgroundColor, lineHeight, buttonColor, buttonHoverColor, buttonTextColor, highlightColor, highlightingToggle, maxWordsPerChunk, textToVoice, autoLogin FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

/* $user variables are not global, changing to variables that track with the rest of the page */
$userNameIn = $user['userName'];
$firstNameIn = $user['realFirstName'];
$lastNameIn = $user['realLastName'];
$emailIn = $user['email'];
$fontSizeIn = $user['fontSize'];
$fontColorIn = $user['fontColor'];
$currentFontIn = $user['fontSelect'];
$backgroundColorIn = $user['backgroundColor'];
$lineHeightIn = $user['lineHeight'];
$buttonColorIn = $user['buttonColor'];
$buttonHoverColorIn = $user['buttonHoverColor'];
$highlightColorIn = $user['highlightColor'];
$buttonTextColorIn = $user['buttonTextColor'];
$maxWordsPerChunkIn = $user['maxWordsPerChunk'];
$textToVoiceIn = $user['textToVoice'];
$autoLoginIn = $user['autoLogin'];
$highlightingToggleIn = $user['highlightingToggle'];

$conn->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <style>
        body {
            font-family: <?= htmlspecialchars($_SESSION['fontSelect']); ?>;
            margin: 0;
            padding: 0;
            color: <?= htmlspecialchars($_SESSION['fontColor']); ?>; /* Dynamic font color */
            background-color: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
            overflow-y: auto; /* Ensure vertical scrolling is enabled */
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: <?= htmlspecialchars($_SESSION['backgroundColor']); ?>; /* Dynamic background color */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #444;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: <?= htmlspecialchars($_SESSION['buttonColor']); ?>;
            color: <?= htmlspecialchars($_SESSION['buttonTextColor']); ?>;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: <?= htmlspecialchars($_SESSION['buttonHoverColor']); ?>;
        }
        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-left: 10px;
            border: 1px solid #ccc;
        }
        .arial {
            font-family: Arial, sans-serif;
        }
        .times {
            font-family: "Times New Roman", serif;
        }
        .courier {
            font-family: Courier New, monospace;
        }
        .georgia{
            font-family: Georgia, serif;
        }
        .verdana{
            font-family: Verdana, sans-serif;
        }
        .tahoma{
            font-family: Tahoma, sans-serif;
        }
        .trebuchet{
            font-family: Trebuchet MS, sans-serif;
        }
        .comicSans{
            font-family: Comic Sans MS, cursive;
        }
        .impact{
            font-family: Impact, sans-serif;
        }
        .palatino{
            font-family: Palatino Linotype, serif;
        }
        .lucidaSans{
            font-family: Lucida Sans Unicode, sans-serif;
        }
        .gill {
            font-family: Gill Sans, sans-serif;
        }
        .franklin{
            font-family: Franklin Gothic Medium, sans-serif;
        }
        .garamond{
            font-family: Garamond, serif;
        }
        .bush{
            font-family: Brush Script MT, cursive;
        }
        .lucidaConsole{
            font-family: Lucida Console, monospace;
        }
</style>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="container">
        <h1>Update Your Profile</h1>
        <form action="scripts/updateUserBackend.php" method="POST">
            <input type="hidden" name="userID" value="<?= htmlspecialchars($userID); ?>">

            <label for="userName">Username:</label>
            <input type="text" name="userName" id="userName" value="<?= htmlspecialchars($userNameIn); ?>" required>

            <label for="realFirstName">First Name:</label>
            <input type="text" name="realFirstName" id="realFirstName" value="<?= htmlspecialchars($firstNameIn); ?>" required>

            <label for="realLastName">Last Name:</label>
            <input type="text" name="realLastName" id="realLastName" value="<?= htmlspecialchars($lastNameIn); ?>" required>

            <label for="pass">Password:</label>
            <input type="password" name="pass" id="pass" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($emailIn); ?>" required>

            <!-- Font Size -->
            <label for="fontSize">Font Size:</label>
            <select name="fontSize" id="fontSize">
                <?php
                $fontSizes = ["10px", "12px", "14px", "16px", "18px", "20px", "24px", "26px", "28px", "30px"];
                foreach ($fontSizes as $size) {
                    $selected = $fontSizeIn === $size ? 'selected' : '';
                    echo "<option value='$size' $selected>$size</option>";
                }
                ?>
            </select>
            
            <!-- Font Select -->
            <label for="fontSelect">Select Font</label>
            <select name="fontSelect" id="fontSelect">
                <option class="arial" value="Arial, sans-serif" <?= $currentFontIn == "Arial, sans-serif" ? 'selected' : '' ?>>Arial</option>
                <option class="times" value="Times New Roman, serif" <?= $currentFontIn == "Times New Roman, serif" ? 'selected' : '' ?>>Times New Roman</option>
                <option class="courier" value="Courier New, monospace" <?= $currentFontIn == "Courier New, monospace" ? 'selected' : '' ?>>Courier New</option>
                <option class="georgia" value="Georgia, serif" <?= $currentFontIn == "Georgia, serif" ? 'selected' : '' ?>>Georgia</option>
                <option class="verdana" value="Verdana, sans-serif" <?= $currentFontIn == "Verdana, sans-serif" ? 'selected' : '' ?>>Verdana</option>
                <option class="tahoma" value="Tahoma, sans-serif" <?= $currentFontIn == "Tahoma, sans-serif" ? 'selected' : '' ?>>Tahoma</option>
                <option class="trebuchet" value="Trebuchet MS, sans-serif" <?= $currentFontIn == "Trebuchet MS, sans-serif" ? 'selected' : '' ?>>Trebuchet MS</option>
                <option class="comicSans" value="Comic Sans MS, cursive" <?= $currentFontIn == "Comic Sans MS, cursive" ? 'selected' : '' ?>>Comic Sans MS</option>
                <option class="imapct" value="Impact, sans-serif" <?= $currentFontIn == "Impact, sans-serif" ? 'selected' : '' ?>>Impact</option>
                <option class="palatino" value="Palatino Linotype, serif" <?= $currentFontIn == "Palatino Linotype, serif" ? 'selected' : '' ?>>Palatino Linotype</option>
                <option class="lucidaSans" value="Lucida Sans Unicode, sans-serif" <?= $currentFontIn == "Lucida Sans Unicode, sans-serif" ? 'selected' : '' ?>>Lucida Sans Unicode</option>
                <option class="gill" value="Gill Sans, sans-serif" <?= $currentFontIn == "Gill Sans, sans-serif" ? 'selected' : '' ?>>Gill Sans</option>
                <option class="franklin" value="Franklin Gothic Medium, sans-serif" <?= $currentFontIn == "Franklin Gothic Medium, sans-serif" ? 'selected' : '' ?>>Franklin Gothic Medium</option>
                <option class="garamond" value="Garamond, serif" <?= $currentFontIn == "Garamond, serif" ? 'selected' : '' ?>>Garamond</option>
                <option class="bush" value="Brush Script MT, cursive" <?= $currentFontIn == "Brush Script MT, cursive" ? 'selected' : '' ?>>Brush Script MT</option>
                <option class="lucidaConsole" value="Lucida Console, monospace" <?= $currentFontIn == "Lucida Console, monospace" ? 'selected' : '' ?>>Lucida Console</option>
            </select>

            <!-- Font Color -->
            <label for="fontColor">Font Color:</label>
            <select name="fontColor" id="fontColor">
                <?php
                $colors = [
                    "#000000" => "Black", "#FFFFFF" => "White", "#FF0000" => "Red", "#00FF00" => "Green", 
                    "#0000FF" => "Blue", "#FFFF00" => "Yellow", "#FF00FF" => "Magenta", "#00FFFF" => "Cyan", 
                    "#C0C0C0" => "Silver", "#808080" => "Gray", "#800000" => "Maroon", "#808000" => "Olive", 
                    "#008000" => "Dark Green", "#800080" => "Purple", "#008080" => "Teal", "#000080" => "Navy"
                ];
                foreach ($colors as $hex => $name) {
                    $selected = $fontColorIn === $hex ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name <div class='color-preview' style='background-color: $hex;'></div></option>";
                }
                ?>
            </select>

            <!-- Background Color -->
            <label for="backgroundColor">Background Color:</label>
            <select name="backgroundColor" id="backgroundColor">
                <?php
                foreach ($colors as $hex => $name) {
                    $selected = $backgroundColorIn === $hex ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name <div class='color-preview' style='background-color: $hex;'></div></option>";
                }
                ?>
            </select>

            <!-- Line Height -->
            <label for="lineHeight">Line Height:</label>
            <select name="lineHeight" id="lineHeight">
                <?php
                $lineHeights = ["1", "1.5", "2", "2.5", "3", "3.5"];
                foreach ($lineHeights as $height) {
                    $selected = $lineHeightIn === $height ? 'selected' : '';
                    echo "<option value='$height' $selected>$height</option>";
                }
                ?>
            </select>

            <!-- Button Color -->
            <label for="buttonColor">Button Color:</label>
            <select name="buttonColor" id="buttonColor">
                <?php
                foreach ($colors as $hex => $name) {
                    $selected = $buttonColorIn === $hex ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name</option>";
                }
                ?>
            </select>

            <!-- Button Hover Color -->
            <label for="buttonHoverColor">Button Hover Color:</label>
            <select name="buttonHoverColor" id="buttonHoverColor">
                <?php
                foreach ($colors as $hex => $name) {
                    $selected = $buttonHoverColorIn === $hex ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name</option>";
                }
                ?>
            </select>

            <!-- Button Text Color -->
            <label for="buttonTextColor">Button Text Color:</label>
            <select name="buttonTextColor" id="buttonTextColor">
                <?php
                foreach ($colors as $hex => $name) {
                    $selected = $buttonTextColorIn === $hex ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name</option>";
                }
                ?>
            </select>

            <!-- Highlight Color -->
            <label for="highlightColor">Highlight Color:</label>
            <select name="highlightColor" id="highlightColor">
                <?php
                foreach ($colors as $hex => $name) {
                    $selected = (isset($highlightColorIn) && $highlightColorIn === $hex) ? 'selected' : '';
                    echo "<option value='$hex' $selected>$name</option>";
                }
                ?>
            </select>

            <!-- Highlighting Toggle -->
            <label for="highlightingToggle">Toggle Highlighting:</label>
            <select name="highlightingToggle" id="highlightingToggle">
                <option value="1" <?= (isset($highlightingToggleIn) && $highlightingToggleIn == '1') ? 'selected' : ''; ?>>On</option>
                <option value="0" <?= (isset($highlightingToggleIn) && $highlightingToggleIn == '0') ? 'selected' : ''; ?>>Off</option>
            </select>

            <!-- Max Words Per Chunk -->
            <label for="maxWordsPerChunk">Max Words Per Chunk:</label>
            <input type="number" name="maxWordsPerChunk" id="maxWordsPerChunk" value="<?= htmlspecialchars($user['maxWordsPerChunk']); ?>">

            <!-- Text to Voice -->
            <label for="textToVoice">Text to Voice:</label>
            <select name="textToVoice" id="textToVoice">
                <option value="1" <?= (isset($textToVoiceIn) && $textToVoiceIn == '1') ? 'selected' : ''; ?>>On</option>
                <option value="0" <?= (isset($textToVoiceIn) && $textToVoiceIn == '0') ? 'selected' : ''; ?>>Off</option>
            </select>

            <!-- Auto Login -->
            <label for="autoLogin">Auto Login:</label>
            <select name="autoLogin" id="autoLogin">
                <option value="1" <?= (isset($autoLoginIn) && $autoLoginIn == '1') ? 'selected' : ''; ?>>On</option>
                <option value="0" <?= (isset($autoLoginIn) && $autoLoginIn == '0') ? 'selected' : ''; ?>>Off</option>
            </select>

            <button type="submit" name="updateProfileAndSettings">Update</button>
        </form>
    </div>
</body>
</html>
