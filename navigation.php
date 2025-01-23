<!-- navigation.php -->
 <?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userLevel = $_SESSION['userLevel'] ?? 0;

$fontSizeChoiceNav = $_SESSION['fontSize'];
$fontColorChoiceNav = $_SESSION['fontColor'];
$backgroundColorChoiceNav = $_SESSION['backgroundColor'];

?>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;  
    color: <?= htmlspecialchars($fontColorChoiceNav); ?>; /* Dynamic font color */
    background-color: <?= htmlspecialchars($backgroundColorChoiceNav); ?>; /* Dynamic background color */

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
            Book Chunks - <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
        <img src="images/bookChunkSm.png" alt="Book Chunks Logo" style="width:50px;height:50px;"> 
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

