<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecipeHub - Share & Discover Recipes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/dbms/styles.css">
    <style>
        .navbar {
            padding: 0.8rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            background: rgba(255, 255, 255, 0.98);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: rgba(255, 107, 53, 0.1);
            color: #FF6B35;
        }
        .active-link {
            color: #FF6B35;
            font-weight: 600;
        }
        .btn-login {
            border: 2px solid #FF6B35;
            color: #FF6B35;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #FF6B35;
            color: white;
        }
        .btn-register {
            background: #FF6B35;
            font-weight: 600;
        }
        .btn-register:hover {
            background: #E55627;
        }
        .user-greeting {
            font-weight: 500;
            color: #495057;
            margin-right: 1rem;
        }
        .user-menu {
            display: flex;
            align-items: center;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 0.5rem;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/dbms/index.php">
            <i class="bi bi-egg-fried me-2 text-warning fs-3"></i>
            <span>RecipeHub</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active-link' : '' ?>" href="/dbms/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'recipes.php' ? 'active-link' : '' ?>" href="/dbms/recipes.php">Recipes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active-link' : '' ?>" href="/dbms/categories.php">Categories</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my_recipes.php' ? 'active-link' : '' ?>" href="/dbms/my_recipes.php">My Recipes</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <span class="user-greeting">
                            <img src="/dbms/images/avatars/<?= htmlspecialchars($_SESSION['username'] ?? 'default') ?>.jpg" 
                                 class="user-avatar" 
                                 alt="<?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>">
                            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
                        </span>
                        <a href="/dbms/profile.php" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="bi bi-gear"></i> Profile
                        </a>
                        <a href="/dbms/logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/dbms/login.php" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="/dbms/register.php" class="btn btn-register text-white">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Show session message -->
<?php if (isset($_SESSION['message'])): ?>
<div class="container mt-3">
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message']); ?>
</div>
<?php endif; ?>