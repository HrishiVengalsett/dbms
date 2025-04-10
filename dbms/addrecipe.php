<?php
require_once __DIR__ . '/includes/auth_functions.php';
require_once __DIR__ . '/includes/db_connect.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Initialize variables with default values
$title = $description = $ingredients = $instructions = $cuisine = '';
$prep_time = $cook_time = 0;
$servings = 1;
$category_id = 0;
$difficulty = 'Medium';
$dietary_tags_selected = []; // Initialize as empty array
$error = '';
$success = '';

// Get all categories
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

// Get all dietary tags
$dietary_tags = $conn->query("SELECT * FROM dietary_tags ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $prep_time = intval($_POST['prep_time'] ?? 0);
    $cook_time = intval($_POST['cook_time'] ?? 0);
    $servings = intval($_POST['servings'] ?? 1);
    $category_id = intval($_POST['category_id'] ?? 0);
    $cuisine = trim($_POST['cuisine'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'Medium';
    $dietary_tags_selected = $_POST['dietary_tags'] ?? [];
    
    // Basic validation
    if (empty($title) || empty($ingredients) || empty($instructions)) {
        $error = "Title, ingredients and instructions are required";
    } else {
        // Handle image upload
        $image_url = 'default-recipe.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/images/recipes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
            $image_url = 'recipes/' . $filename;
        }
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert recipe
            $stmt = $conn->prepare("INSERT INTO recipes 
                (title, description, ingredients, instructions, prep_time, cook_time, servings, 
                 image_url, category_id, user_id, cuisine, difficulty, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->bind_param("ssssiiisiss", 
                $title, $description, $ingredients, $instructions, 
                $prep_time, $cook_time, $servings, $image_url, 
                $category_id, $_SESSION['user_id'], $cuisine, $difficulty);
            
            if ($stmt->execute()) {
                $recipe_id = $conn->insert_id;
                
                // Insert dietary tags if any
                if (!empty($dietary_tags_selected)) {
                    $tag_stmt = $conn->prepare("INSERT INTO recipe_dietary_tags (recipe_id, tag_id) VALUES (?, ?)");
                    foreach ($dietary_tags_selected as $tag_id) {
                        $tag_id = (int)$tag_id;
                        $tag_stmt->bind_param("ii", $recipe_id, $tag_id);
                        $tag_stmt->execute();
                    }
                }
                
                $conn->commit();
                $success = "Recipe added successfully!";
                
                // Clear form
                $title = $description = $ingredients = $instructions = $cuisine = '';
                $prep_time = $cook_time = 0;
                $servings = 1;
                $category_id = 0;
                $dietary_tags_selected = [];
            } else {
                throw new Exception("Error adding recipe: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
            
            // Delete uploaded image if transaction failed
            if (isset($filename) && file_exists($uploadDir . $filename)) {
                unlink($uploadDir . $filename);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Add Recipe | RecipeHub</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 600;
        }
        .recipe-image-preview {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            display: none;
            margin-top: 10px;
            border-radius: 8px;
        }
        .dietary-tag-checkbox {
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
        }
        .dietary-tag-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        .dietary-tag-checkbox .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 4px;
        }
        .dietary-tag-checkbox:hover input ~ .checkmark {
            background-color: #e9ecef;
        }
        .dietary-tag-checkbox input:checked ~ .checkmark {
            background-color: #FF6B35;
            border-color: #FF6B35;
        }
        .dietary-tag-checkbox .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        .dietary-tag-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        .dietary-tag-checkbox .checkmark:after {
            left: 9px;
            top: 5px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            transform: rotate(45deg);
        }
    </style>
</head>
<body>
    
    <div class="container py-5">
        <div class="form-container">
            <h1 class="text-center mb-4 text-warning">Add New Recipe</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <a href="myrecipes.php" class="alert-link">View your recipes</a> or 
                    <a href="addrecipe.php" class="alert-link">add another recipe</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Recipe Title*</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($title) ?>" required>
                            <div class="invalid-feedback">Please provide a recipe title</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?= htmlspecialchars($description) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ingredients" class="form-label">Ingredients*</label>
                            <textarea class="form-control" id="ingredients" name="ingredients" 
                                      rows="5" required><?= htmlspecialchars($ingredients) ?></textarea>
                            <small class="text-muted">Separate ingredients with commas</small>
                            <div class="invalid-feedback">Please list the ingredients</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="image" class="form-label">Recipe Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <img id="imagePreview" class="recipe-image-preview" src="#" alt="Preview">
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prep_time" class="form-label">Prep Time (mins)</label>
                                    <input type="number" class="form-control" id="prep_time" 
                                           name="prep_time" min="0" value="<?= $prep_time ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cook_time" class="form-label">Cook Time (mins)</label>
                                    <input type="number" class="form-control" id="cook_time" 
                                           name="cook_time" min="0" value="<?= $cook_time ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="servings" class="form-label">Servings</label>
                            <input type="number" class="form-control" id="servings" 
                                   name="servings" min="1" value="<?= $servings ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category*</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>" 
                                        <?= $category_id == $category['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cuisine" class="form-label">Cuisine</label>
                            <input type="text" class="form-control" id="cuisine" name="cuisine" 
                                   value="<?= htmlspecialchars($cuisine) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="difficulty" class="form-label">Difficulty</label>
                            <select class="form-select" id="difficulty" name="difficulty">
                                <option value="Easy" <?= $difficulty == 'Easy' ? 'selected' : '' ?>>Easy</option>
                                <option value="Medium" <?= $difficulty == 'Medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="Hard" <?= $difficulty == 'Hard' ? 'selected' : '' ?>>Hard</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Dietary Tags Section -->
                    <div class="col-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-white">
                                <h4 class="mb-0"><i class="bi bi-tags"></i> Dietary Tags</h4>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Select all applicable dietary tags for this recipe: 
                                    <span id="tag-counter" class="fw-bold"><?= count($dietary_tags_selected) ?>/5 tags selected</span>
                                </p>
                                <div class="row">
                                    <?php foreach ($dietary_tags as $tag): ?>
                                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                                        <label class="dietary-tag-checkbox d-flex align-items-center">
                                            <input type="checkbox" name="dietary_tags[]" 
                                                   value="<?= $tag['tag_id'] ?>"
                                                   <?= in_array($tag['tag_id'], $dietary_tags_selected) ? 'checked' : '' ?>>
                                            <span class="checkmark me-2"></span>
                                            <i class="bi <?= $tag['icon'] ?> me-1"></i>
                                            <?= htmlspecialchars($tag['name']) ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions*</label>
                            <textarea class="form-control" id="instructions" name="instructions" 
                                      rows="8" required><?= htmlspecialchars($instructions) ?></textarea>
                            <div class="invalid-feedback">Please provide cooking instructions</div>
                        </div>
                    </div>
                    
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-warning btn-lg px-5">
                            <i class="bi bi-save"></i> Save Recipe
                        </button>
                        <a href="myrecipes.php" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Initialize textarea autoresize
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
        
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Dietary tag counter and limit
        const tagCheckboxes = document.querySelectorAll('input[name="dietary_tags[]"]');
        const tagCounter = document.getElementById('tag-counter');
        
        tagCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checked = document.querySelectorAll('input[name="dietary_tags[]"]:checked');
                tagCounter.textContent = `${checked.length}/5 tags selected`;
                
                // Enforce maximum of 5 tags
                if (checked.length > 5) {
                    this.checked = false;
                    tagCounter.textContent = `5/5 tags selected (maximum reached)`;
                    alert('You can select a maximum of 5 dietary tags');
                }
            });
        });
    </script>
</body>
</html>