<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = false;

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $cooking_time = intval($_POST['cooking_time'] ?? 0);
    $servings = intval($_POST['servings'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $calories = intval($_POST['calories'] ?? 0);
    $protein = floatval($_POST['protein'] ?? 0);
    $carbs = floatval($_POST['carbs'] ?? 0);
    $fat = floatval($_POST['fat'] ?? 0);
}