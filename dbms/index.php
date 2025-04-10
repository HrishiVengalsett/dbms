<?php
include 'includes/header.php';
include 'includes/db_connect.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-light">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Discover & Share Amazing Recipes</h1>
    <p class="lead mb-4">Your personal portal to a world of delicious possibilities.</p>
    <a href="recipes.php" class="btn btn-warning btn-lg">Explore Recipes</a>
  </div>
</section>

<!-- Popular Categories Section -->
<section id="popular-categories" class="py-5">
  <div class="container">
    <h2 class="mb-4 text-center fw-bold">Popular Categories</h2>
    <div class="row g-4 justify-content-center">
      <?php
      // Fetch only 3 categories (change ORDER BY logic if needed)
      $query = "SELECT * FROM categories ORDER BY category_id ASC LIMIT 3";
      $result = $conn->query($query);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          // Set default values if fields don't exist
          $image_url = isset($row['image_url']) ? $row['image_url'] : 'default-category.jpg';
          $description = isset($row['description']) ? $row['description'] : 'Explore delicious recipes in this category';
          
          // Get the correct image path
          $imgPath = getImagePath($image_url);
          
          echo '
            <div class="col-md-4">
              <div class="card h-100 border-0 shadow">
                <a href="recipes.php?category_id=' . $row['category_id'] . '" style="text-decoration: none; color: inherit;">
                  <img src="' . $imgPath . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '" style="height: 200px; object-fit: cover;">
                  <div class="card-body text-center">
                    <h5 class="card-title fw-bold">' . htmlspecialchars($row['name']) . '</h5>
                    <p class="card-text">' . htmlspecialchars($description) . '</p>
                    <span class="btn btn-warning mt-2">View Recipes</span>
                  </div>
                </a>
              </div>
            </div>';
        }
      } else {
        echo '<p class="text-center">No categories available.</p>';
      }
      ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>