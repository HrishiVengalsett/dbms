<?php
require_once 'db_connect.php';
session_start(); // Ensure session is started

// Function to register a new user
function registerUser($username, $email, $password) {
    global $conn;
    
    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Function to login a user
function loginUser($username, $password) {
    global $conn;
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    
    return false;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin (only if 'is_admin' column exists)
function isAdmin() {
    if (isLoggedIn()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            return isset($user['is_admin']) ? $user['is_admin'] == 1 : false;
        }
    }
    return false;
}

// Function to logout user
function logoutUser() {
    session_unset();
    session_destroy();
}
?>
