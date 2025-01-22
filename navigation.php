<!-- navigation.php -->
 <?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Connect to the database
include_once 'scripts/pdo.php';

if (!isset($navigationDbConn)) {
    $navigationDbConn = new mysqli($servername, $username, $password, $dbname);
    if ($navigationDbConn->connect_error) {
        die("Connection failed: " . $navigationDbConn->connect_error);
    }
}

// Fetch userLevel from the database using $navigationDbConn
$userID = $_SESSION['user_id'];
$sql = "SELECT userLevel FROM users WHERE userID = ?";
$stmt = $navigationDbConn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$navResult = $stmt->get_result();
$user = $navResult->fetch_assoc();
$userLevel = $user['userLevel'] ?? 0;
$stmt->close();


// Fetch user preferences for text and background
$sql = "SELECT fontSize, fontColor, backgroundColor FROM users WHERE userID = ?";
$stmt = $navigationDbConn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$choices = $result->fetch_assoc();
$fontSizeChoiceNav = $choices['fontSize'];
$fontColorChoiceNav = $choices['fontColor'];
$backgroundColorChoiceNav = $choices['backgroundColor'];
$stmt->close();


$navigationDbConn->close();

?>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    height: 100vh; 
    overflow: hidden;  
    color: <?= htmlspecialchars($fontColorChoice); ?>; /* Dynamic font color */
    background-color: <?= htmlspecialchars($backgroundColorChoice); ?>; /* Dynamic background color */

    }

.nav-container {
    display: flex;
    align-items: center;
    padding: 10px;
    background-color: <?= htmlspecialchars($backgroundColorChoiceNav); ?>; /* Dynamic background color */
    border-bottom: 1px solid #ddd;
}

.menu-button {
    background-color: #A9A9A9;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    flex-shrink: 0;
}

.menu-button:hover {
    background-color: 	#696969;
}

.user-info {
    margin-left: auto; /* Pushes the user-info section to the far right */
    font-size: 16px;
    color: <?= htmlspecialchars($fontColorNav); ?>;
}

.user-name {
    font-weight: bold;
}

.nav-menu {
    display: none;
    position: absolute;
    top: 50px;
    left: 0;
    background-color: white;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    width: 200px;
    z-index: 1000;
}

.nav-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: black;
}

.nav-menu a:hover {
    background-color: #f0f0f0;
}

</style>

<div class="nav-container">
    <button class="menu-button" onclick="toggleMenu()">☰ Menu</button>
    <div class="user-info">
        Logged in as: <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
    </div>
    <div class="nav-menu" id="navMenu">
        <a href="welcome.php">Welcome Page</a>
        <a href="scrollView.php">Scroll Feed</a>
        <a href="setupFeed.php">Setup Feeds</a>
        <a href="devNotes.php">Development Notes</a>
        <a href="about.php">About</a>
        <a href="updateUser.php">User Settings</a>
        <a href="scripts/logout.php">Log Out</a>
        <?php if ($userLevel == 1): ?>
            <a href="systemData.php">System Usage</a>
        <?php endif; ?>
    </div>
</div>
<script>
    function toggleMenu() {
        const menu = document.getElementById('navMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    // Close the menu if clicked outside
    window.addEventListener('click', function(event) {
        const menu = document.getElementById('navMenu');
        const button = document.querySelector('.menu-button');
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.style.display = 'none';
        }
    });
</script>

