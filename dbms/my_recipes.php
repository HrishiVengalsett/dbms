<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/db_connect.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user's recipes
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>My Recipes | RecipeHub</title>
    <style>
        .recipe-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .empty-state {
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .btn-add-recipe {
            background-color: #FF6B35;
            color: white;
            font-weight: 600;
        }
        .btn-add-recipe:hover {
            background-color: #E55627;
            color: white;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-warning">My Recipes</h1>
            <a href="addrecipe.php" class="btn btn-add-recipe">
                <i class="bi bi-plus-lg"></i> Add New Recipe
            </a>
        </div>
        
        <?php if (empty($recipes)): ?>
            <div class="empty-state">
                <img src="images/empty-recipe.svg" alt="No recipes" width="200" class="mb-4">
                <h3 class="mb-3">You haven't added any recipes yet</h3>
                <p class="text-muted mb-4">Share your culinary creations with the community</p>
                <a href="addrecipe.php" class="btn btn-add-recipe btn-lg">
                    <i class="bi bi-plus-lg"></i> Create Your First Recipe
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col">
                        <div class="card recipe-card h-100 shadow-sm">
                            <img src="images/<?= htmlspecialchars($recipe['image_url'] ?: 'default-recipe.jpg') ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($recipe['title']) ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                <p class="card-text text-muted">
                                    <?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($recipe['created_at'])) ?>
                                    </small>
                                    <div class="btn-group">
                                        <a href="editrecipe.php?id=<?= $recipe['recipe_id'] ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="deleterecipe.php?id=<?= $recipe['recipe_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this recipe?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="recipe.php?id=<?= $recipe['recipe_id'] ?>" 
                                   class="btn btn-outline-warning w-100">
                                    View Recipe
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>