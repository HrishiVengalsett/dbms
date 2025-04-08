<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: recipes.php");
    exit();
}

$recipe_id = $_GET['id'];

// Fetch recipe details
$recipe_query = "SELECT r.*, c.name AS category_name, u.username 
                FROM recipes r 
                JOIN categories c ON r.category_id = c.category_id 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.recipe_id = ?";
$stmt = $conn->prepare($recipe_query);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$recipe_result = $stmt->get_result();

if ($recipe_result->num_rows === 0) {
    header("Location: recipes.php");
    exit();
}

$recipe = $recipe_result->fetch_assoc();

// Fetch reviews for this recipe
$reviews_query = "SELECT rev.*, u.username 
                 FROM reviews rev 
                 JOIN users u ON rev.user_id = u.user_id 
                 WHERE rev.recipe_id = ? 
                 ORDER BY rev.created_at DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Calculate average rating
$avg_rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE recipe_id = ?";
$stmt = $conn->prepare($avg_rating_query);
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$avg_rating_result = $stmt->get_result();
$rating_data = $avg_rating_result->fetch_assoc();

require_once 'includes/header.php';
?>

<div class="recipe-header mb-5">
    <div class="row">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($recipe['image_url'] ?? 'assets/images/default-recipe.jpg'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <div class="d-flex align-items-center mb-3">
                <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                <span class="text-muted">Posted by <?php echo htmlspecialchars($recipe['username']); ?></span>
            </div>
            
            <div class="recipe-meta mb-4">
                <div class="row">
                    <div class="col-4">
                        <div class="meta-item text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p><?php echo htmlspecialchars($recipe['cooking_time']); ?> mins</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="meta-item text-center">
                            <i class="fas fa-utensils fa-2x mb-2"></i>
                            <p><?php echo htmlspecialchars($recipe['servings']); ?> servings</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="meta-item text-center">
                            <i class="fas fa-fire fa-2x mb-2"></i>
                            <p><?php echo htmlspecialchars($recipe['calories']); ?> kcal</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rating mb-3">
                <h5>Rating: 
                    <?php if ($rating_data['review_count'] > 0): ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= round($rating_data['avg_rating']) ? '' : '-alt'; ?>"></i>
                        <?php endfor; ?>
                        <span>(<?php echo round($rating_data['avg_rating'], 1); ?> from <?php echo $rating_data['review_count']; ?> reviews)</span>
                    <?php else: ?>
                        <span>No reviews yet</span>
                    <?php endif; ?>
                </h5>
            </div>
            
            <div class="dietary-tags mb-4">
                <?php if (!empty($recipe['dietary_tags'])): ?>
                    <?php $tags = explode(',', $recipe['dietary_tags']); ?>
                    <?php foreach ($tags as $tag): ?>
                        <span class="badge bg-success me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="nutrition-info mb-4">
                <h5>Nutritional Information (per serving):</h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($recipe['calories']/2500)*100; ?>%" 
                         aria-valuenow="<?php echo $recipe['calories']; ?>" aria-valuemin="0" aria-valuemax="2500">
                        Calories: <?php echo $recipe['calories']; ?> kcal
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="nutrient">
                            <small>Protein</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo ($recipe['protein']/100)*100; ?>%"></div>
                            </div>
                            <small><?php echo $recipe['protein']; ?>g</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="nutrient">
                            <small>Carbs</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo ($recipe['carbs']/100)*100; ?>%"></div>
                            </div>
                            <small><?php echo $recipe['carbs']; ?>g</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="nutrient">
                            <small>Fat</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo ($recipe['fat']/100)*100; ?>%"></div>
                            </div>
                            <small><?php echo $recipe['fat']; ?>g</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="recipe-content mb-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3>Ingredients</h3>
                </div>
                <div class="card-body">
                    <ul>
                        <?php 
                        $ingredients = explode("\n", $recipe['ingredients']);
                        foreach ($ingredients as $ingredient): 
                            if (!empty(trim($ingredient))):
                        ?>
                            <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h3>Preparation Steps</h3>
                </div>
                <div class="card-body">
                    <ol>
                        <?php 
                        $steps = explode("\n", $recipe['preparation_steps']);
                        foreach ($steps as $step): 
                            if (!empty(trim($step))):
                        ?>
                            <li><?php echo htmlspecialchars(trim($step)); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="reviews-section mb-5">
    <h2 class="mb-4">Reviews</h2>
    
    <?php if (isLoggedIn()): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h4>Leave a Review</h4>
        </div>
        <div class="card-body">
            <form action="submit-review.php" method="POST">
                <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select class="form-select" id="rating" name="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <a href="login.php">Login</a> to leave a review.
    </div>
    <?php endif; ?>
    
    <div class="reviews-list">
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title"><?php echo htmlspecialchars($review['username']); ?></h5>
                        <div class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-alt'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <small class="text-muted">Posted on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                No reviews yet. Be the first to review!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>