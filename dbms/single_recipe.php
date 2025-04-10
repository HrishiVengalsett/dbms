<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/db_connect.php';

// Check if recipe ID is provided
if (!isset($_GET['id'])) {
    header("Location: recipes.php");
    exit();
}

$recipe_id = (int)$_GET['id'];

// Fetch recipe details
$stmt = $conn->prepare("SELECT r.*, u.username, c.name AS category_name 
                       FROM recipes r
                       JOIN users u ON r.user_id = u.user_id
                       LEFT JOIN categories c ON r.category_id = c.category_id
                       WHERE r.recipe_id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe = $stmt->get_result()->fetch_assoc();

if (!$recipe) {
    header("Location: recipes.php");
    exit();
}

// Format ingredients as array
$ingredients = array_map('trim', explode(',', $recipe['ingredients']));

// Format instructions with line breaks
$instructions = nl2br(htmlspecialchars($recipe['instructions']));

// Fetch reviews
$reviews = [];
$stmt = $conn->prepare("SELECT rr.*, u.username 
                       FROM reviews rr
                       JOIN users u ON rr.user_id = u.user_id
                       WHERE rr.recipe_id = ?
                       ORDER BY rr.created_at DESC");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total / count($reviews), 1);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO reviews (recipe_id, user_id, rating, comment) 
                               VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $recipe_id, $_SESSION['user_id'], $rating, $comment);
        $stmt->execute();
        
        // Refresh to show new review
        header("Location: single_recipe.php?id=" . $recipe_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title><?= htmlspecialchars($recipe['title']) ?> | RecipeHub</title>
    <style>
        .recipe-hero {
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), 
                        url('images/<?= htmlspecialchars($recipe['image_url'] ?: 'default-recipe.jpg') ?>');
            background-size: cover;
            background-position: center;
            height: 400px;
            border-radius: 10px;
            position: relative;
        }
        .recipe-meta {
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 1.5rem;
            border-radius: 0 0 10px 10px;
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
        .ingredient-list {
            list-style-type: none;
            padding-left: 0;
        }
        .ingredient-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .ingredient-list li:last-child {
            border-bottom: none;
        }
        .rating-stars {
            color: #FF6B35;
            font-size: 1.2rem;
        }
        .review-card {
            border-left: 3px solid #FF6B35;
        }
        .total-time {
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <!-- Recipe Header -->
        <div class="recipe-hero mb-4">
            <div class="recipe-meta">
                <h1 class="text-white"><?= htmlspecialchars($recipe['title']) ?></h1>
                <div class="d-flex flex-wrap gap-3 align-items-center text-white">
                    <span class="rating-stars">
                        <?= str_repeat('★', round($avg_rating)) . str_repeat('☆', 5 - round($avg_rating)) ?>
                        <span class="text-white ms-2">(<?= count($reviews) ?>)</span>
                    </span>
                    <span><i class="bi bi-person"></i> <?= htmlspecialchars($recipe['username']) ?></span>
                    <span><i class="bi bi-clock"></i> 
                        <span class="total-time">
                            <?= ($recipe['prep_time'] + $recipe['cook_time']) ?> mins total
                        </span>
                        (<?= $recipe['prep_time'] ?> prep, <?= $recipe['cook_time'] ?> cook)
                    </span>
                    <span><i class="bi bi-people"></i> Serves <?= $recipe['servings'] ?></span>
                    <?php if ($recipe['cuisine']): ?>
                        <span class="badge bg-warning"><?= htmlspecialchars($recipe['cuisine']) ?></span>
                    <?php endif; ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($recipe['difficulty']) ?></span>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Left Column - Ingredients -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-white">
                        <h3 class="mb-0"><i class="bi bi-list-check"></i> Ingredients</h3>
                    </div>
                    <div class="card-body">
                        <ul class="ingredient-list">
                            <?php foreach ($ingredients as $ingredient): ?>
                                <li><?= htmlspecialchars($ingredient) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted"><?= count($ingredients) ?> ingredients</small>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Instructions -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-white">
                        <h3 class="mb-0"><i class="bi bi-book"></i> Instructions</h3>
                    </div>
                    <div class="card-body">
                        <?= $instructions ?>
                    </div>
                </div>
                
                <!-- Nutrition/Cooking Info -->
                <div class="row g-3 mb-4">
                    <?php if ($recipe['calories']): ?>
                    <div class="col-md-3 col-6">
                        <div class="card text-center py-2">
                            <div class="text-muted small">Calories</div>
                            <div class="fw-bold"><?= $recipe['calories'] ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($recipe['protein']): ?>
                    <div class="col-md-3 col-6">
                        <div class="card text-center py-2">
                            <div class="text-muted small">Protein</div>
                            <div class="fw-bold"><?= $recipe['protein'] ?>g</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($recipe['carbohydrates']): ?>
                    <div class="col-md-3 col-6">
                        <div class="card text-center py-2">
                            <div class="text-muted small">Carbs</div>
                            <div class="fw-bold"><?= $recipe['carbohydrates'] ?>g</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($recipe['fat']): ?>
                    <div class="col-md-3 col-6">
                        <div class="card text-center py-2">
                            <div class="text-muted small">Fat</div>
                            <div class="fw-bold"><?= $recipe['fat'] ?>g</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews Section -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h3 class="mb-0"><i class="bi bi-chat-square-text"></i> Reviews</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label class="form-label">Your Rating</label>
                                    <div class="rating-stars mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" 
                                                   class="d-none" <?= $i == 5 ? 'checked' : '' ?>>
                                            <label for="star<?= $i ?>" class="bi bi-star-fill fs-4" 
                                                   style="cursor: pointer;"></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Your Review</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <a href="login.php" class="text-decoration-none">Login</a> to leave a review
                            </div>
                        <?php endif; ?>
                        
                        <div class="reviews-list">
                            <?php if (empty($reviews)): ?>
                                <p class="text-muted">No reviews yet. Be the first to review!</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-card bg-light p-3 mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong><?= htmlspecialchars($review['username']) ?></strong>
                                            <span class="rating-stars">
                                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                            </span>
                                        </div>
                                        <p class="mb-0"><?= htmlspecialchars($review['comment']) ?></p>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Star rating interaction
        document.querySelectorAll('.rating-stars input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const stars = this.parentElement.querySelectorAll('label');
                stars.forEach((star, index) => {
                    if (index < this.value) {
                        star.classList.add('bi-star-fill');
                        star.classList.remove('bi-star');
                    } else {
                        star.classList.add('bi-star');
                        star.classList.remove('bi-star-fill');
                    }
                });
            });
        });
    </script>
</body>
</html>