<?php
require_once 'config.php';

// Create database connection
$conn = new mysqli('localhost','root', '','recipe_website');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>