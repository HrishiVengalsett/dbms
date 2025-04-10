<?php
require_once __DIR__ . '/includes/auth_functions.php';

// Redirect logged-in users
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formData = [
        'username' => trim($_POST["username"] ?? ''),
        'email' => trim($_POST["email"] ?? ''),
        'password' => $_POST["password"] ?? '',
        'confirm_password' => $_POST["confirm_password"] ?? ''
    ];

    // Validate inputs
    if (empty($formData['username'])) {
        $errors['username'] = "Username is required";
    } elseif (strlen($formData['username']) < 4) {
        $errors['username'] = "Username must be at least 4 characters";
    }

    if (empty($formData['email'])) {
        $errors['email'] = "Email is required";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($formData['password'])) {
        $errors['password'] = "Password is required";
    } elseif (strlen($formData['password']) < 8) {
        $errors['password'] = "Password must be at least 8 characters";
    }

    if ($formData['password'] !== $formData['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    // If no errors, try registration
    if (empty($errors)) {
        if (registerUser($formData['username'], $formData['email'], $formData['password'])) {
            $_SESSION['message'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $errors['general'] = "Registration failed. Username or email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Register | RecipeHub</title>
    <style>
        .register-hero {
            background: linear-gradient(rgba(0,0,0,0.6), url('images/food-bg.jpg') center/cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            max-width: 550px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.95);
            overflow: hidden;
        }
        .register-header {
            background-color: #FF6B35;
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        .register-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #FF6B35;
            box-shadow: 0 0 0 0.25rem rgba(255,107,53,0.25);
        }
        .btn-register {
            background-color: #FF6B35;
            color: white;
            border: none;
            padding: 10px;
            transition: all 0.3s;
        }
        .btn-register:hover {
            background-color: #E55627;
            color: white;
            transform: translateY(-2px);
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
        }
    </style>
</head>
<body>
    <div class="register-hero">
        <div class="container">
            <div class="register-card">
                <div class="register-header">
                    <h2><i class="bi bi-egg-fried"></i> Create Account</h2>
                    <p class="mb-0">Join our community of food lovers</p>
                </div>
                
                <div class="register-body">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($errors['general']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control py-2 <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   id="username" name="username" 
                                   value="<?= htmlspecialchars($formData['username']) ?>" 
                                   required placeholder="Choose a username">
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['username']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control py-2 <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= htmlspecialchars($formData['email']) ?>" 
                                   required placeholder="Your email address">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control py-2 <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" 
                                   required placeholder="At least 8 characters">
                            <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4 position-relative">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control py-2 <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password" 
                                   required placeholder="Re-enter your password">
                            <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-register w-100 fw-bold mb-3">
                            <i class="bi bi-person-plus"></i> Register
                        </button>
                        
                        <div class="text-center pt-3 border-top">
                            <p class="text-muted mb-0">Already have an account?</p>
                            <a href="login.php" class="text-decoration-none fw-semibold">Sign in here</a>
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
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmInput = document.getElementById('confirm_password');
            const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmInput.setAttribute('type', type);
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