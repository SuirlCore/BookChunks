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

        .custom-select {
            position: relative;
            width: 300px;
        }

        .custom-select select {
            display: none; /* Hide the default select element */
        }

        .select-selected {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            cursor: pointer;
            user-select: none;
            font-size: 16px;
        }

        .select-items {
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            width: 100%;
            z-index: 99;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }

        .select-items div {
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
        }

        .select-items div:hover {
            background-color: #ddd;
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
            Current Choice: <?= htmlspecialchars($_SESSION['fontSelect']); ?><br>
            <input type="hidden" name="fontSelect" id="fontSelectInput" value="">

            <div class="custom-select">
                <div class="select-selected">Select a Font &#10549;</div>
                <div class="select-items">
                    <div class="arial" data-value="Arial, sans-serif">Arial: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="times" data-value="'Times New Roman', serif">Times New Roman: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="courier" data-value="'Courier New', monospace">Courier New: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="georgia" data-value="Georgia, serif">Georgia: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="verdana" data-value="Verdana, sans-serif">Verdana: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="tahoma" data-value="Tahoma, sans-serif">Tahoma: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="trebuchet" data-value="Trebuchet MS, sans-serif">Trebuchet: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="comicSans" data-value="Comic Sans MS, cursive">Comic Sans: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="impact" data-value="Impact, sans-serif">Impact: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="palatino" data-value="Palatino Linotype, serif">Palatino: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="lucidaSans" data-value="Lucida Sans Unicode, sans-serif">Lucida Sans: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="gill" data-value="Gill Sans, sans-serif">Gill Sans: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="franklin" data-value="Franklin Gothic Medium, sans-serif">Franklin Gothic: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="garamond" data-value="Garamond, serif">Garamond: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="bush" data-value="Brush Script MT, cursive">Bush Script: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                    <div class="lucidaConsole" data-value="Lucida Console, monospace">Lucida Console: ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz</div>
                </div>
            </div>

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

    <script>
    // JavaScript to handle the custom dropdown
    document.addEventListener('DOMContentLoaded', () => {
        const selected = document.querySelector('.select-selected');
        const items = document.querySelector('.select-items');
        const hiddenInput = document.getElementById('fontSelectInput'); // Hidden input to store selected font value

        // Toggle dropdown visibility
        selected.addEventListener('click', () => {
            items.style.display = items.style.display === 'block' ? 'none' : 'block';
        });

        // Handle font selection
        items.addEventListener('click', (e) => {
            if (e.target && e.target.matches('div')) {
                const font = e.target.dataset.value;
                selected.textContent = e.target.textContent + ' ⬇'; // Update displayed text
                selected.style.fontFamily = font; // Update font style
                items.style.display = 'none'; // Close the dropdown
                hiddenInput.value = font; // Set the hidden input value
                console.log(`Selected font: ${font}`); // Log selected font
            }
        });

        // Close dropdown if clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-select')) {
                items.style.display = 'none';
            }
        });
    });
</script>


</body>
</html>
