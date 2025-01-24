<?php
include 'pdo.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            session_start();
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
            $_SESSION['autoLogin'] = $row['autoLogin'];
            
            
            header("Location: ../welcome.php"); // Redirect to a welcome page after successful login
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found.";
    }

    $conn->close();
}
?>
