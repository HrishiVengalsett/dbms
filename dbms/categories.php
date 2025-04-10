<?php
include 'includes/header.php';
include 'includes/db_connect.php';

// Get category ID from URL
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Get all categories
$categories = [];
$cat_query = "SELECT * FROM categories ORDER BY name";
$cat_result = $conn->query($cat_query);
if ($cat_result && $cat_result->num_rows > 0) {
    while ($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// If a category is selected, get its details and related filters
$category_name = '';
$ingredients = [];
$cuisines = [];
$difficulties = [];
$recipes = [];

if ($category_id > 0) {
    // Get category name
    $stmt = $conn->prepare("SELECT name FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $category_name = htmlspecialchars($row['name']);
    }
    $stmt->close();

    // Get ingredients
    $stmt = $conn->prepare("SELECT DISTINCT ingredients FROM recipes WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ingredient_list = explode(',', $row['ingredients']);
        foreach ($ingredient_list as $ingredient) {
            $trimmed = trim($ingredient);
            if (!empty($trimmed) && !in_array($trimmed, $ingredients)) {
                $ingredients[] = $trimmed;
            }
        }
    }
    $stmt->close();

    // Get cuisines
    $stmt = $conn->prepare("SELECT DISTINCT cuisine FROM recipes WHERE category_id = ? AND cuisine IS NOT NULL");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['cuisine'])) {
            $cuisines[] = htmlspecialchars($row['cuisine']);
        }
    }
    $stmt->close();

    // Get difficulties
    $stmt = $conn->prepare("SELECT DISTINCT difficulty FROM recipes WHERE category_id = ? AND difficulty IS NOT NULL");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['difficulty'])) {
            $difficulties[] = htmlspecialchars($row['difficulty']);
        }
    }
    $stmt->close();

    // Get recipes - modified to handle potential filters
    $sql = "SELECT r.*, u.username 
           FROM recipes r
           JOIN users u ON r.user_id = u.user_id
           WHERE r.category_id = ?";
    
    // Check if cuisine filter is applied
    if (isset($_GET['cuisine']) && !empty($_GET['cuisine'])) {
        $sql .= " AND r.cuisine = ?";
    }
    
    // Check if difficulty filter is applied
    if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
        $sql .= " AND r.difficulty = ?";
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters based on filters
    if (isset($_GET['cuisine']) && isset($_GET['difficulty'])) {
        $cuisine_filter = $_GET['cuisine'];
        $difficulty_filter = $_GET['difficulty'];
        $stmt->bind_param("iss", $category_id, $cuisine_filter, $difficulty_filter);
    } elseif (isset($_GET['cuisine'])) {
        $cuisine_filter = $_GET['cuisine'];
        $stmt->bind_param("is", $category_id, $cuisine_filter);
    } elseif (isset($_GET['difficulty'])) {
        $difficulty_filter = $_GET['difficulty'];
        $stmt->bind_param("is", $category_id, $difficulty_filter);
    } else {
        $stmt->bind_param("i", $category_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
    $stmt->close();
}
?>

<div class="container mt-5">
    <?php if ($category_id == 0): ?>
        <!-- Show only categories when none is selected -->
        <h1 class="text-center mb-4">Browse Categories</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <a href="categories.php?category_id=<?php echo $category['category_id']; ?>" class="text-decoration-none">
                            <img src="images/<?php echo isset($category['image_url']) ? htmlspecialchars($category['image_url']) : 'default-category.jpg'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($category['name']); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title text-warning"><?php echo htmlspecialchars($category['name']); ?></h5>
                                <p class="card-text"><?php echo isset($category['description']) ? htmlspecialchars($category['description']) : 'Explore delicious recipes'; ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Show category name, filters, and recipes when a category is selected -->
        <h1 class="text-center mb-4"><?php echo $category_name; ?></h1>
        
        <!-- Back button -->
        <a href="categories.php" class="btn btn-outline-secondary mb-4">&laquo; Back to Categories</a>
        
        <!-- Active filters -->
        <?php if (isset($_GET['cuisine']) || isset($_GET['difficulty'])): ?>
            <div class="alert alert-info mb-4">
                <strong>Active Filters:</strong>
                <?php if (isset($_GET['cuisine'])): ?>
                    <span class="badge bg-warning text-dark me-2">Cuisine: <?php echo htmlspecialchars($_GET['cuisine']); ?></span>
                <?php endif; ?>
                <?php if (isset($_GET['difficulty'])): ?>
                    <span class="badge bg-warning text-dark">Difficulty: <?php echo htmlspecialchars($_GET['difficulty']); ?></span>
                <?php endif; ?>
                <a href="categories.php?category_id=<?php echo $category_id; ?>" class="btn btn-sm btn-outline-danger ms-3">Clear Filters</a>
            </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="row mb-4">
            <!-- Ingredients Filter Box -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Ingredients</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ingredients)): ?>
                            <div class="scrollable-list" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($ingredients as $ingredient): ?>
                                    <a href="recipes.php?search=<?php echo urlencode($ingredient); ?>" 
                                       class="d-block p-2"><?php echo htmlspecialchars($ingredient); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No ingredients found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Cuisine Filter Box -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Cuisines</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($cuisines)): ?>
                            <div class="scrollable-list" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($cuisines as $cuisine): ?>
                                    <a href="categories.php?category_id=<?php echo $category_id; ?>&cuisine=<?php echo urlencode($cuisine); ?>" 
                                       class="d-block p-2"><?php echo htmlspecialchars($cuisine); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No cuisines found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Difficulty Filter Box -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Difficulty</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($difficulties)): ?>
                            <div class="scrollable-list" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($difficulties as $difficulty): ?>
                                    <a href="categories.php?category_id=<?php echo $category_id; ?>&difficulty=<?php echo urlencode($difficulty); ?>" 
                                       class="d-block p-2"><?php echo htmlspecialchars($difficulty); ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No difficulty levels found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recipes Section -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (!empty($recipes)): ?>
                <?php foreach ($recipes as $recipe): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="images/<?php echo htmlspecialchars($recipe['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title text-warning"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($recipe['username']); ?>
                                    </small>
                                    <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($recipe['difficulty']); ?></span>
                                </div>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <?php echo ($recipe['prep_time'] + $recipe['cook_time']); ?> mins | 
                                        <?php echo htmlspecialchars($recipe['cuisine']); ?>
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0 text-center">
                                <a href="single_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-outline-warning btn-sm">View Recipe</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No recipes found in this category.</p>
                    <?php if (isset($_GET['cuisine']) || isset($_GET['difficulty'])): ?>
                        <p class="text-center">Try adjusting your filters.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>