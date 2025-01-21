<!-- navigation.php -->
 <?php
 sleep(1);
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
$user_id = $_SESSION['user_id'];
$sql = "SELECT userLevel FROM users WHERE userID = ?";
$stmt = $navigationDbConn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$navResult = $stmt->get_result();
$user = $navResult->fetch_assoc();
$userLevel = $user['userLevel'] ?? 0;
$stmt->close();

$navigationDbConn->close();

?>
<link rel="stylesheet" href="css/navigation.css">

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

