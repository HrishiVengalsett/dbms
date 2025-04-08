<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

$recipes_query = "SELECT r.*, c.name AS category_name 
                  FROM recipes r 
                  JOIN categories c ON r.category_id = c.category_id 
                  ORDER BY r.created_at DESC";
$recipes_result = $conn->query($recipes_query);

require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="section-title mb-4 text-center">All Recipes</h2>
    <div class="row">
        <?php if ($recipes_result->num_rows > 0): ?>
            <?php while ($recipe = $recipes_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($recipe['image_url'] ?? 'assets/images/default-recipe.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($recipe['category_name']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted"><i class="fas fa-clock"></i> <?php echo htmlspecialchars($recipe['cooking_time']); ?> mins</small>
                                <small class="text-muted"><i class="fas fa-utensils"></i> <?php echo htmlspecialchars($recipe['servings']); ?> servings</small>
                            </div>
                            <p class="card-text"><?php echo substr(htmlspecialchars($recipe['description']), 0, 100); ?>...</p>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="single-recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-sm btn-primary">View Recipe</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p>No recipes found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
