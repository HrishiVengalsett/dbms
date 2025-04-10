<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/db_connect.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

// Handle messages/errors from preferences update
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Get all available dietary tags
$tags = $conn->query("SELECT * FROM dietary_tags ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get user's current preferences
$user_tags = [];
$result = $conn->query("SELECT tag_id FROM user_dietary_preferences WHERE user_id = $user_id");
if ($result) {
    $user_tags = array_column($result->fetch_all(MYSQLI_ASSOC), 'tag_id');
}

// Handle preferences form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Clear existing preferences
        $conn->query("DELETE FROM user_dietary_preferences WHERE user_id = $user_id");
        
        // Add new selections if any
        if (!empty($_POST['dietary_tags'])) {
            $stmt = $conn->prepare("INSERT INTO user_dietary_preferences (user_id, tag_id) VALUES (?, ?)");
            foreach ($_POST['dietary_tags'] as $tag_id) {
                $tag_id = (int)$tag_id;
                $stmt->bind_param("ii", $user_id, $tag_id);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        $_SESSION['message'] = "Dietary preferences updated successfully!";
        header("Location: profile.php?tab=preferences");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating preferences: " . $e->getMessage();
        header("Location: profile.php?tab=preferences");
        exit();
    }
}

// Get recommended recipes if on recommendations tab
$recommendations = [];
$debug_info = ''; // For debugging

if (isset($_GET['tab']) && $_GET['tab'] === 'recommendations') {
    if (!empty($user_tags)) {
        // Sanitize tag IDs
        $tag_ids = array_map('intval', $user_tags);
        $tag_ids_string = implode(',', $tag_ids);
        
        // Get the names of selected tags for debug info
        $selected_tags = $conn->query("SELECT name FROM dietary_tags WHERE tag_id IN ($tag_ids_string)")->fetch_all(MYSQLI_ASSOC);
        $debug_info .= "<div class='alert alert-info mb-3'><strong>Selected Preferences:</strong> ";
        foreach ($selected_tags as $tag) {
            $debug_info .= "<span class='badge bg-info me-1'>".htmlspecialchars($tag['name'])."</span>";
        }
        $debug_info .= "</div>";
        
        // Main recommendation query - finds recipes that match ANY selected tag with scoring
        $query = "SELECT r.*, 
         (SELECT GROUP_CONCAT(dt.name SEPARATOR ', ') 
          FROM recipe_dietary_tags rdt
          JOIN dietary_tags dt ON rdt.tag_id = dt.tag_id
          WHERE rdt.recipe_id = r.recipe_id AND rdt.tag_id IN ($tag_ids_string)) as matched_tags,
         (SELECT COUNT(*) FROM recipe_dietary_tags rdt 
          WHERE rdt.recipe_id = r.recipe_id AND rdt.tag_id IN ($tag_ids_string)) as match_count,
         (SELECT AVG(rating) FROM reviews WHERE recipe_id = r.recipe_id) as avg_rating,
         (SELECT COUNT(*) FROM reviews WHERE recipe_id = r.recipe_id) as review_count,
         (
            (SELECT COUNT(*) FROM recipe_dietary_tags rdt 
             WHERE rdt.recipe_id = r.recipe_id AND rdt.tag_id IN ($tag_ids_string)) * 0.6 +
            COALESCE((SELECT AVG(rating) FROM reviews WHERE recipe_id = r.recipe_id), 3) * 0.3 +
            LOG(COALESCE((SELECT COUNT(*) FROM reviews WHERE recipe_id = r.recipe_id), 10) * 0.1)
         ) as recommendation_score
         FROM recipes r
         WHERE EXISTS (
             SELECT 1 FROM recipe_dietary_tags rdt 
             WHERE rdt.recipe_id = r.recipe_id AND rdt.tag_id IN ($tag_ids_string)
         )
         ORDER BY recommendation_score DESC
         LIMIT 12";
        
        $recommendations = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
        
        $debug_info .= "<div class='alert alert-info mb-3'>";
        if (!empty($recommendations)) {
            $debug_info .= "<strong>Found ".count($recommendations)." matching recipes:</strong><br>";
            foreach ($recommendations as $recipe) {
                $debug_info .= "- ".htmlspecialchars($recipe['title'])." (score: ".number_format($recipe['recommendation_score'], 2).", matches: ".$recipe['matched_tags'].")<br>";
            }
        } else {
            $debug_info .= "<strong>No recipes found matching selected tags.</strong>";
        }
        $debug_info .= "</div>";
    }
    
    // Fallback to popular recipes if no matches or to add diversity
    $fallback_needed = empty($recommendations) || count($recommendations) < 6;
    if ($fallback_needed) {
        $fallback_limit = empty($recommendations) ? 12 : (12 - count($recommendations));
        $fallback_query = "SELECT r.*, 
                          (SELECT AVG(rating) FROM reviews WHERE recipe_id = r.recipe_id) as avg_rating,
                          (SELECT COUNT(*) FROM reviews WHERE recipe_id = r.recipe_id) as review_count
                          FROM recipes r
                          WHERE (SELECT AVG(rating) FROM reviews WHERE recipe_id = r.recipe_id) IS NOT NULL
                          ORDER BY avg_rating DESC, review_count DESC
                          LIMIT $fallback_limit";
        
        $fallback_recipes = $conn->query($fallback_query)->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($fallback_recipes)) {
            // Add recommendation score to fallback recipes
            foreach ($fallback_recipes as &$recipe) {
                $recipe['recommendation_score'] = 3.0; // Base score for popular recipes
                $recipe['matched_tags'] = '';
            }
            
            // Merge with existing recommendations
            $recommendations = array_merge($recommendations, $fallback_recipes);
            
            $debug_info .= "<div class='alert alert-warning'>Added ".count($fallback_recipes)." popular recipes for diversity</div>";
        }
    }
}

// Get user's recipes count
$recipes_count = $conn->query("SELECT COUNT(*) as count FROM recipes WHERE user_id = $user_id")->fetch_assoc()['count'];

// Get user's reviews count
$reviews_count = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE user_id = $user_id")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>My Profile | RecipeHub</title>
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #FF6B35;
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            color: #FF6B35;
            border-color: #FF6B35 #FF6B35 #fff;
        }
        .nav-tabs .nav-link:hover:not(.active) {
            border-color: transparent;
            color: #FF6B35;
        }
        .recipe-card {
            transition: transform 0.2s;
            position: relative;
        }
        .recipe-card:hover {
            transform: translateY(-5px);
        }
        .badge-match {
            background-color: #FF6B35;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
        }
        .empty-state-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        .stats-card {
            border-left: 4px solid #FF6B35;
        }
        .matched-tags {
            font-size: 0.8rem;
        }
        .recommendation-score {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 107, 53, 0.9);
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
            z-index: 1;
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <img src="<?= htmlspecialchars($user['profile_image'] ?? 'images/default-profile.jpg') ?>" 
                     class="profile-img mb-3" alt="Profile Image">
                <h3><?= htmlspecialchars($user['username']) ?></h3>
                <p class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <div class="stats-card p-3 bg-light rounded">
                            <h5 class="mb-0"><?= $recipes_count ?></h5>
                            <small class="text-muted">Recipes</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card p-3 bg-light rounded">
                            <h5 class="mb-0"><?= $reviews_count ?></h5>
                            <small class="text-muted">Reviews</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="editprofile.php" class="btn btn-outline-warning">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
            
            <div class="col-md-8">
                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= (!isset($_GET['tab']) || $_GET['tab'] === 'details') ? 'active' : '' ?>" 
                                id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                            <i class="bi bi-person"></i> My Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= (isset($_GET['tab']) && $_GET['tab'] === 'preferences') ? 'active' : '' ?>" 
                                id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                            <i class="bi bi-heart"></i> Preferences
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= (isset($_GET['tab']) && $_GET['tab'] === 'recommendations') ? 'active' : '' ?>" 
                                id="recommendations-tab" data-bs-toggle="tab" data-bs-target="#recommendations" type="button" role="tab">
                            <i class="bi bi-stars"></i> Recommendations
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-4 border border-top-0 rounded-bottom" id="profileTabsContent">
                    <!-- Details Tab -->
                    <div class="tab-pane fade <?= (!isset($_GET['tab']) || $_GET['tab'] === 'details') ? 'show active' : '' ?>" 
                         id="details" role="tabpanel">
                        <h4 class="mb-4 text-warning">About Me</h4>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Basic Information</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></li>
                                    <li class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></li>
                                    <li class="mb-2"><strong>Member Since:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Activity</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Recipes Shared:</strong> <?= $recipes_count ?></li>
                                    <li class="mb-2"><strong>Reviews Written:</strong> <?= $reviews_count ?></li>
                                    <li class="mb-2"><strong>Dietary Preferences:</strong> 
                                        <?php if (!empty($user_tags)): ?>
                                            <?php foreach ($user_tags as $tag_id): 
                                                $tag = $conn->query("SELECT name FROM dietary_tags WHERE tag_id = $tag_id")->fetch_assoc(); ?>
                                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($tag['name']) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            Not set
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Recent Activity</h5>
                            <?php
                            $recent_activity = $conn->query("
                                (SELECT 'recipe' as type, recipe_id as id, title as name, created_at 
                                 FROM recipes WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 3)
                                UNION
                                (SELECT 'review' as type, review_id as id, 
                                 CONCAT('Review for recipe #', recipe_id) as name, created_at 
                                 FROM reviews WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 3)
                                ORDER BY created_at DESC LIMIT 5
                            ")->fetch_all(MYSQLI_ASSOC);
                            
                            if (!empty($recent_activity)): ?>
                                <div class="list-group">
                                    <?php foreach ($recent_activity as $activity): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <i class="bi bi-<?= $activity['type'] === 'recipe' ? 'journal-text' : 'chat-square-text' ?> me-2"></i>
                                                    <?= htmlspecialchars($activity['name']) ?>
                                                </span>
                                                <small class="text-muted"><?= date('M j, Y', strtotime($activity['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">No recent activity to show</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Preferences Tab -->
                    <div class="tab-pane fade <?= (isset($_GET['tab']) && $_GET['tab'] === 'preferences') ? 'show active' : '' ?>" 
                         id="preferences" role="tabpanel">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-white">
                                <h4 class="mb-0"><i class="bi bi-heart"></i> Dietary Preferences</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <p class="text-muted">Select your dietary preferences to get personalized recipe recommendations:</p>
                                    </div>
                                    
                                    <div class="row">
                                        <?php foreach ($tags as $tag): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="dietary_tags[]" id="tag_<?= $tag['tag_id'] ?>" 
                                                       value="<?= $tag['tag_id'] ?>"
                                                       <?= in_array($tag['tag_id'], $user_tags) ? 'checked' : '' ?>>
                                                <label class="form-check-label d-flex align-items-center" for="tag_<?= $tag['tag_id'] ?>">
                                                    <i class="bi <?= $tag['icon'] ?> me-2 fs-5"></i>
                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($tag['name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($tag['description']) ?></small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <button type="submit" name="update_preferences" class="btn btn-warning mt-3">
                                        <i class="bi bi-save"></i> Save Preferences
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recommendations Tab -->
                    <div class="tab-pane fade <?= (isset($_GET['tab']) && $_GET['tab'] === 'recommendations') ? 'show active' : '' ?>" 
                         id="recommendations" role="tabpanel">
                        <h4 class="mb-4 text-warning">
                            <i class="bi bi-stars"></i> Personalized Recommendations
                        </h4>
                        
                        <?php 
                        // Display debug info
                        echo $debug_info;
                        
                        if (empty($user_tags)): ?>
                            <div class="alert alert-warning">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div>
                                        You haven't set any dietary preferences yet. 
                                        <a href="profile.php?tab=preferences" class="alert-link">Set your preferences</a> 
                                        to get personalized recommendations.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($recommendations)): ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <?php foreach ($recommendations as $recipe): ?>
                                    <div class="col">
                                        <div class="card h-100 recipe-card">
                                            <span class="recommendation-score" title="Recommendation score">
                                                <?= number_format($recipe['recommendation_score'], 1) ?>
                                            </span>
                                            <img src="<?= htmlspecialchars($recipe['image_url'] ?? 'images/default-recipe.jpg') ?>" 
                                                 class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>" 
                                                 style="height: 180px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-clock"></i> 
                                                        <?= $recipe['prep_time'] + $recipe['cook_time'] ?> mins
                                                    </span>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-people"></i> 
                                                        <?= $recipe['servings'] ?> servings
                                                    </span>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($recipe['difficulty']) ?>
                                                    </span>
                                                </div>
                                                <?php if ($recipe['avg_rating']): ?>
                                                    <div class="mb-2">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi bi-star<?= $i <= round($recipe['avg_rating']) ? '-fill' : '' ?> text-warning"></i>
                                                        <?php endfor; ?>
                                                        <small class="text-muted">(<?= $recipe['review_count'] ?> reviews)</small>
                                                    </div>
                                                <?php endif; ?>
                                                <p class="card-text text-muted"><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
                                                <?php if (!empty($recipe['matched_tags'])): ?>
                                                    <div class="matched-tags mt-2">
                                                        <small class="text-muted">Matches: </small>
                                                        <?php 
                                                        $matched_tags = explode(', ', $recipe['matched_tags']);
                                                        foreach ($matched_tags as $tag): ?>
                                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars(trim($tag)) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <a href="recipe.php?id=<?= $recipe['recipe_id'] ?>" class="btn btn-warning btn-sm">
                                                    View Recipe
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="recipes.php?recommended=1" class="btn btn-warning">
                                    <i class="bi bi-arrow-right"></i> View More Recommendations
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-emoji-frown empty-state-icon"></i>
                                <h5>No recommendations available</h5>
                                <p class="text-muted">Try adjusting your preferences or check back later</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Activate tab from URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                const tab = new bootstrap.Tab(document.getElementById(tabParam + '-tab'));
                tab.show();
            }
        });
    </script>
</body>
</html>