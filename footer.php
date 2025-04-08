</main>
        <footer class="bg-dark text-white py-4 mt-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h5>About Us</h5>
                        <p>Delicious Recipes is a platform for food lovers to share and discover amazing recipes from around the world.</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo BASE_URL; ?>" class="text-white">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>recipes.php" class="text-white">All Recipes</a></li>
                            <li><a href="<?php echo BASE_URL; ?>categories.php" class="text-white">Categories</a></li>
                            <li><a href="<?php echo BASE_URL; ?>privacy.php" class="text-white">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>Connect With Us</h5>
                        <div class="social-links">
                            <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p>&copy; <?php echo date('Y'); ?> Delicious Recipes. All rights reserved.</p>
                </div>
            </div>
        </footer>
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    </body>
</html>