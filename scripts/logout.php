<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page or another appropriate page
header("Location: ../index.html");
exit();
?>