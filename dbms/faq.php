<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-4 text-warning">Frequently Asked Questions</h1>
            
            <div class="accordion" id="faqAccordion">
                <!-- FAQ Item 1 -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            How do I create a recipe?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            To create a recipe, click on "Add Recipe" in the navigation menu after logging in. 
                            Fill out all the required fields including title, ingredients, and instructions, 
                            then click "Save Recipe" to publish it.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            Can I edit my recipes after publishing?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes, you can edit your recipes at any time. Go to "My Recipes" in your account, 
                            find the recipe you want to edit, and click the edit button. Make your changes 
                            and save to update the recipe.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            How do I search for recipes?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can search for recipes using the search bar at the top of any page. 
                            You can search by recipe name, ingredient, or category. Advanced filters 
                            are available on the Recipes page.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                            Can I save recipes to make later?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! When viewing a recipe, click the "Save" button to add it to your favorites. 
                            You can access all your saved recipes from your account dashboard.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="accordion-item mb-3 border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                            How do I report an inappropriate recipe?
                        </button>
                    </h2>
                    <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            If you find a recipe that violates our community guidelines, please click the 
                            "Report" button on the recipe page or contact us directly through our 
                            <a href="contact.php">Contact page</a>.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <p>Still have questions?</p>
                <a href="contact.php" class="btn btn-warning">Contact Us</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>