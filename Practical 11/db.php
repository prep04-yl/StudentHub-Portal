<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "studenthub_db";

// Create MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>
