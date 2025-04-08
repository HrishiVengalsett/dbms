<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/auth_functions.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$dietary_filter = isset($_GET['dietary']) ? $_GET['dietary'] : '';
$time_filter = isset($_GET['time']) ? intval($_GET['time']) : 0;

// Base query
$query = "SELECT r.*, c.name AS category_name 
          FROM recipes r 
          JOIN categories c ON r.category_id = c.category_id 
          WHERE 1=1";

$params = [];
$types = '';

// Add search term condition
if (!empty($search_query)) {
    $query .= " AND (r.title LIKE ? OR r.ingredients LIKE ? OR r.description LIKE ?)";
    $search_param = "%$search_query%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= 'sss';
}

// Add category filter
if ($category_filter > 0) {
    $query .= " AND r.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

// Add dietary filter
if (!empty($dietary_filter)) {
    $query .= " AND FIND_IN_SET(?, r.dietary_tags) > 0";
    $params[] = $dietary_filter;
    $types .= 's';
}

// Add time filter
if ($time_filter > 0) {
    $query .= " AND r.cooking_time <= ?";
    $params[] = $time_filter;
    $types .= 'i';
}

$query .= " ORDER BY r.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Dietary options
$dietary_options = [
    'vegetarian' => 'Vegetarian',
    'vegan' => 'Vegan',
    'gluten-free' => 'Gluten-Free',
    'dairy-free' => 'Dairy-Free',
    'nut-free' => 'Nut-Free',
    'low-carb' => 'Low-Carb',
    'keto' => 'Keto'
];

require_once 'includes/header.php';
?>

<div class="search-page mb-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Filter Recipes</h4>
                </div>
                <div class="card-body">
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">All Categories</option>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dietary" class="form-label">Dietary Preference</label>
                            <select class="form-select" id="dietary" name="dietary">
                                <option value="">Any</option>
                                <?php foreach ($dietary_options as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $dietary_filter == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="time" class="form-label">Max Cooking Time (minutes)</label>
                            <select class="form-select" id="time" name="time">
                                <option value="0">Any</option>
                                <option value="15" <?php echo $time_filter == 15 ? 'selected' : ''; ?>>15 mins or less</option>
                                <option value="30" <?php echo $time_filter == 30 ? 'selected' : ''; ?>>30 mins or less</option>
                                <option value="45" <?php echo $time_filter == 45 ? 'selected' : ''; ?>>45 mins or less</option>
                                <option value="60" <?php echo $time_filter == 60 ? 'selected' : ''; ?>>60 mins or less</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="search-results">
                <h2 class="mb-4">
                    <?php if (!empty($search_query)): ?>
                        Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                    <?php else: ?>
                        Browse Recipes
                    <?php endif; ?>
                </h2>
                
                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($recipe = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
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
                <?php else: ?>
                    <div class="alert alert-warning">
                        No recipes found matching your criteria. Try adjusting your filters.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>