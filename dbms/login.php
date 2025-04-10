<?php
// Load authentication functions and check login status
require_once __DIR__ . '/includes/auth_functions.php';

// Redirect logged-in users
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$error = "";
$username = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } elseif (loginUser($username, $password)) {
        // Security measures
        session_regenerate_id(true);
        
        // Redirect to intended page or homepage
        $redirect = $_SESSION['redirect_url'] ?? 'index.php';
        unset($_SESSION['redirect_url']);
        header("Location: " . $redirect);
        exit();
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}

// Store requested URL for redirect after login
if (!isset($_SESSION['redirect_url']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['redirect_url'] = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Login | RecipeHub</title>
    <style>
        .login-hero {
            background: linear-gradient(rgba(0,0,0,0.6), url('images/food-bg.jpg') center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            max-width: 500px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.95);
            overflow: hidden;
        }
        .login-header {
            background-color: #FF6B35;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #FF6B35;
            box-shadow: 0 0 0 0.25rem rgba(255,107,53,0.25);
        }
        .btn-login {
        background-color: #FF6B35 !important;
        color: white !important;
        border: none;
        padding: 10px;
        transition: all 0.3s;
    }
    .btn-login:hover {
        background-color: #E55627 !important;
        color: white !important;
        transform: translateY(-2px);
    }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="login-hero">
        <div class="container">
            <div class="login-card">
                <div class="login-header">
                    <h2><i class="bi bi-egg-fried"></i> RecipeHub</h2>
                    <p class="mb-0">Share and discover delicious recipes</p>
                </div>
                
                <div class="login-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" autocomplete="off">
                        <div class="mb-3 position-relative">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control py-2" id="username" name="username" 
                                   value="<?= htmlspecialchars($username) ?>" required
                                   placeholder="Enter your username">
                        </div>
                        
                        <div class="mb-4 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control py-2" id="password" name="password" 
                                   required placeholder="••••••••">
                            <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                            <div class="text-end mt-2">
                                <a href="forgot-password.php" class="text-decoration-none small text-muted">Forgot password?</a>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2" style="background-color: #FF6B35; color: white !important;">
    <i class="bi bi-box-arrow-in-right"></i> Login
</button>
                        
                        <div class="text-center pt-3 border-top">
                            <p class="text-muted mb-0">New to RecipeHub?</p>
                            <a href="register.php" class="text-decoration-none fw-semibold">Create an account</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
        
        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>