<?php
// Error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL (adjust based on your folder structure)
define('BASE_URL', 'http://localhost/dbms/'); // Change 'dbms' if your folder name is different

// Database constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'recipe_website_2'); // ✅ Correct database name
?>
