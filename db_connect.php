<?php
$host = 'localhost';
$db   = 'accommodation_rental';
$user = 'root';
$pass = '';

// Create MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
