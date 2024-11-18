<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "software"; // Database name updated to "software"

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Add this line right after checking the connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} 


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
