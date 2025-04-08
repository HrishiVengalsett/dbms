<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// If a specific category is selected
if ($category_id) {
    // Fetch selected category name
    $category_query = "SELECT name FROM categories WHERE category_id = ?";
    $stmt = $conn->prepare($category_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $category_result = $stmt->get_result();
    $category = $category_result->fetch_assoc();

    // Fetch recipes in this category
    $recipes_query = "SELECT r.*, c.name AS category_name 
                      FROM recipes r 
                      JOIN categories c ON r.category_id = c.category_id 
                      WHERE r.category_id = ?
                      ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($recipes_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $recipes_result = $stmt->get_result();
} else {
    // Fetch all categories with recipe counts
    $categories_query = "SELECT c.*, COUNT(r.recipe_id) AS recipe_count 
                         FROM categories c 
                         LEFT JOIN recipes r ON c.category_id = r.category_id 
                         GROUP BY c.category_id 
                         ORDER BY recipe_count DESC";
    $categories_result = $conn->query($categories_query);
}

require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <?php if ($category_id && $category): ?>
        <h2 class="section-title mb-4 text-center"><?php echo htmlspecialchars($category['name']); ?> Recipes</h2>
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
                    <p>No recipes found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="categories.php" class="btn btn-outline-secondary">Back to All Categories</a>
        </div>
    <?php else: ?>
        <h2 class="section-title mb-4 text-center">All Categories</h2>
        <div class="row">
            <?php while ($category = $categories_result->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($category['image_url'] ?? 'assets/images/default-category.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($category['name']); ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($category['recipe_count']); ?> recipes</span>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="categories.php?id=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-outline-primary">Explore</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
