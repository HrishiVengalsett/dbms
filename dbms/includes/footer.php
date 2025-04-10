<!-- Enhanced Footer -->
<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-egg-fried fs-3 text-warning me-2"></i>
                    <h4 class="mb-0">RecipeHub</h4>
                </div>
                <p class="text-muted">
                    Share and discover delicious recipes from around the world. Join our community of food lovers today!
                </p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-pinterest fs-5"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-youtube fs-5"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4">
                <h5 class="text-warning mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/dbms/index.php" class="text-white text-decoration-none">Home</a></li>
                    <li class="mb-2"><a href="/dbms/recipes.php" class="text-white text-decoration-none">Recipes</a></li>
                    <li class="mb-2"><a href="/dbms/categories.php" class="text-white text-decoration-none">Categories</a></li>
                    <li class="mb-2"><a href="/dbms/myrecipes.php" class="text-white text-decoration-none">My Recipes</a></li>
                    <li><a href="/dbms/addrecipe.php" class="text-white text-decoration-none">Add Recipe</a></li>
                </ul>
            </div>

            <!-- Help Section -->
            <div class="col-lg-2 col-md-4">
                <h5 class="text-warning mb-3">Help</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/dbms/faq.php" class="text-white text-decoration-none">FAQs</a></li>
                    <li class="mb-2"><a href="/dbms/contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                    <li class="mb-2"><a href="/dbms/privacy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                    <li><a href="/dbms/terms.php" class="text-white text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-4 col-md-4">
                <h5 class="text-warning mb-3">Newsletter</h5>
                <p class="text-muted">Subscribe to get weekly recipe inspiration</p>
                <form class="mb-3">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" required>
                        <button class="btn btn-warning" type="submit">Subscribe</button>
                    </div>
                </form>
                <small class="text-muted">We'll never share your email with anyone else.</small>
            </div>
        </div>

        <hr class="my-4 bg-secondary">

        <!-- Copyright -->
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted">&copy; <?= date('Y') ?> RecipeHub. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 text-muted">
                    Made with <i class="bi bi-heart-fill text-danger"></i> for food lovers
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/dbms/js/main.js"></script>
</body>
</html>