<!-- navigation.php -->
 <?php
 session_start();
// Connect to the database
include 'scripts/pdo.php';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch userLevel from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT userLevel FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$userLevel = $user['userLevel'] ?? 0;

$stmt->close();
$conn->close();
?>


<div class="nav-container">
    <button class="menu-button" onclick="toggleMenu()">☰ Menu</button>
    <div class="nav-menu" id="navMenu">
        <a href="welcome.php">Welcome Page</a>
        <a href="uploadPage.php">Upload a Book</a>
        <a href="updateFeed.php">Feed Management</a>
        <a href="updateBooks.php">Book Management</a>
        <a href="devNotes.php">Development Notes</a>
        <a href="">Log Out</a>
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
