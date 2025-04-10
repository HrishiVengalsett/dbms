<?php
require_once 'config.php';
function getImagePath($filename) {
    $base_path = 'images/';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Check if filename already contains path
    if (strpos($filename, 'images/') === 0) {
        return $filename;
    }
    
    // Check for different extensions
    $name_without_ext = pathinfo($filename, PATHINFO_FILENAME);
    
    foreach ($allowed_extensions as $ext) {
        $possible_path = $base_path . $name_without_ext . '.' . $ext;
        if (file_exists($possible_path)) {
            return $possible_path;
        }
    }
    
    // Fallback to original path
    return $base_path . $filename;
}
// Create a new MySQLi connection using constants from config.php
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set the charset to UTF-8 for proper encoding
$conn->set_charset("utf8mb4");
?>
