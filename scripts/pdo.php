<?php

// Connection to the database
$servername = "localhost";
$username = "root";
$password = "letmeinnow";
$dbname = "bookChunk";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>