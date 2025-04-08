<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

// Fetch featured recipes
$featured_query = "SELECT r.*, c.name AS category_name 
                   FROM recipes r 
                   JOIN categories c ON r.category_id = c.category_id 
                   ORDER BY r.created_at DESC LIMIT 6";
$featured_result = $conn->query($featured_query);

// Fetch popular categories
$categories_query = "SELECT c.*, COUNT(r.recipe_id) AS recipe_count 
                    FROM categories c 
                    LEFT JOIN recipes r ON c.category_id = r.category_id 
                    GROUP BY c.category_id 
                    ORDER BY recipe_count DESC LIMIT 4";
$categories_result = $conn->query($categories_query);

require_once 'includes/header.php';
?>

<div class="hero-section mb-5">
    <div class="hero-content text-center text-white">
        <h1 class="display-4">Discover Delicious Recipes</h1>
        <p class="lead">Find and share cooking inspiration with our community of food lovers</p>
        <a href="recipes.php" class="btn btn-primary btn-lg">Browse Recipes</a>
    </div>
</div>

<section class="featured-recipes mb-5">
    <h2 class="section-title mb-4">Featured Recipes</h2>
    <div class="row">
        <?php while ($recipe = $featured_result->fetch_assoc()): ?>
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
                <div class="card-footer bg-white">
                    <a href="single-recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-sm btn-primary">View Recipe</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div class="text-center mt-3">
        <a href="recipes.php" class="btn btn-outline-primary">View All Recipes</a>
    </div>
</section>

<section class="categories-section mb-5">
    <h2 class="section-title mb-4">Popular Categories</h2>
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
</section>

<?php
require_once 'includes/footer.php';
?>