<?php
include 'includes/config.php';
include 'includes/header.php';

$keyword = $_GET['keyword'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT r.*, c.name AS category_name, u.username 
          FROM recipes r 
          JOIN categories c ON r.category_id = c.category_id 
          JOIN users u ON r.user_id = u.user_id 
          WHERE 1";

$params = [];
$types = "";

if (!empty($keyword)) {
    $query .= " AND (r.title LIKE ? OR r.ingredients LIKE ?)";
    $params[] = "%" . $keyword . "%";
    $params[] = "%" . $keyword . "%";
    $types .= "ss";
}

if (!empty($difficulty)) {
    $query .= " AND r.difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

if (!empty($category)) {
    $query .= " AND c.name = ?";
    $params[] = $category;
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4">🔍 Search Results</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($recipe = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="img/<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                            <p class="card-text text-muted">By <?= htmlspecialchars($recipe['username']) ?> | <?= htmlspecialchars($recipe['category_name']) ?></p>
                            <a href="single_recipe.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-warning">View Recipe</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No recipes found for your search.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
