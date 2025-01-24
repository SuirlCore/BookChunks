<?php
include 'pdo.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // If there's no cookie, proceed with normal login process

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Check if the user exists
    $sql = "SELECT * FROM users WHERE userName='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $row['pass'])) {
            echo "Login successful. Welcome " . $username;
            // Start a session and store user info in session variables
            $_SESSION['user_id'] = $row['userID'];
            $_SESSION['username'] = $username;
            $_SESSION['fontSize'] = $row['fontSize'];
            $_SESSION['fontColor'] = $row['fontColor'];
            $_SESSION['fontSelect'] = $row['fontSelect'];
            $_SESSION['backgroundColor'] = $row['backgroundColor'];
            $_SESSION['lineHeight'] = $row['lineHeight'];
            $_SESSION['highlightColor'] = $row['highlightColor'];
            $_SESSION['highlightingToggle'] = $row['highlightingToggle'];
            $_SESSION['buttonColor'] = $row['buttonColor'];
            $_SESSION['buttonHoverColor'] = $row['buttonHoverColor'];
            $_SESSION['buttonTextColor'] = $row['buttonTextColor'];
            $_SESSION['userLevel'] = $row['userLevel'];
            $_SESSION['maxWordsPerChunk'] = $row['maxWordsPerChunk'];
            $_SESSION['textToVoice'] = $row['textToVoice'];
            
            // Optionally, create the auto_login cookie if they choose to be remembered
            if (isset($_POST['autoLogin']) && $_POST['autoLogin'] == 1) {
                setcookie('auto_login', $username, time() + (86400 * 30), "/"); // Set cookie for 30 days
            }

            header("Location: ../scrollView.php"); // Redirect to scrollView page after successful login
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }

    $conn->close();
}

?>
