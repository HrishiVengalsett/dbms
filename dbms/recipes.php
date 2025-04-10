<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$category_id = $_GET['category_id'] ?? null;
$search = $_GET['search'] ?? null;
$page_title = "All Recipes";

// Build the query dynamically
$params = [];
$types = '';
$conditions = '';
$joins = "LEFT JOIN categories c ON r.category_id = c.category_id
          LEFT JOIN users u ON r.user_id = u.user_id";

if ($category_id) {
    $conditions .= "r.category_id = ?";
    $types .= 'i';
    $params[] = $category_id;
}

if ($search) {
    if (!empty($conditions)) $conditions .= " AND ";
    $conditions .= "(r.title LIKE ? OR r.ingredients LIKE ?)";
    $types .= 'ss';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Modified SQL query with calculated total_time
$sql = "SELECT r.*, c.name AS category_name, u.username,
        (r.prep_time + r.cook_time) AS total_time
        FROM recipes r
        $joins";

if (!empty($conditions)) {
    $sql .= " WHERE $conditions";
}

$sql .= " ORDER BY r.created_at DESC";

// Prepare and bind
$stmt = $conn->prepare($sql);
if ($types && $params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Page title if category set
if ($category_id) {
    $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE category_id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat = $cat_result->fetch_assoc()) {
        $page_title = "Recipes in " . htmlspecialchars($cat['name']);
    }
    $cat_stmt->close();
}
?>

<!-- HTML Content Starts Here -->
<div class="container mt-5">
    <h2 class="text-center mb-4 text-warning"><?= htmlspecialchars($page_title) ?></h2>

    <!-- Search Bar -->
    <form method="GET" action="recipes.php" class="mb-4">
        <div class="input-group">
            <?php if ($category_id): ?>
                <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control" placeholder="Search by title or ingredients..." value="<?= htmlspecialchars($search ?? '') ?>">
            <button type="submit" class="btn btn-warning">Search</button>
        </div>
    </form>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                    <img src="<?= getImagePath($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']) ?>">                        <div class="card-body">
                            <h5 class="card-title text-warning"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($row['description'], 0, 100)) ?>...</p>
                            <span class="badge bg-secondary"><?= htmlspecialchars($row['category_name']) ?></span>
                            <p class="mt-2 mb-0">
                                <small class="text-muted">
                                    By <?= htmlspecialchars($row['username']) ?> | 
                                    Total: <?= $row['total_time'] ?> mins
                                </small>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 text-center">
                            <a href="single_recipe.php?id=<?= $row['recipe_id'] ?>" class="btn btn-outline-warning btn-sm">View Recipe</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No recipes found<?= $category_id ? " in this category" : "" ?>.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>