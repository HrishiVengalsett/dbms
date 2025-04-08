
USE recipe_website;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(255)
);

-- Recipes table
CREATE TABLE recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    ingredients TEXT NOT NULL,
    preparation_steps TEXT NOT NULL,
    cooking_time INT NOT NULL, -- in minutes
    servings INT NOT NULL,
    category_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255),
    calories INT,
    protein DECIMAL(5,1),
    carbs DECIMAL(5,1),
    fat DECIMAL(5,1),
    dietary_tags VARCHAR(255), -- comma-separated values like 'vegetarian,gluten-free'
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Reviews table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert data into users table
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@recipe.com', '$2y$10$HvZ1T0xW5X5O5Z9X0X0X0uJ0X0X0X0X0X0X0X0X0X0X0X0X0X0X0', TRUE),
('chef_john', 'john@email.com', '$2y$10$HvZ1T0xW5X5O5Z9X0X0X0uJ0X0X0X0X0X0X0X0X0X0X0X0X0X0X0', FALSE),
('baking_lover', 'sarah@email.com', '$2y$10$HvZ1T0xW5X5O5Z9X0X0X0uJ0X0X0X0X0X0X0X0X0X0X0X0X0X0X0', FALSE),
('healthy_eater', 'mike@email.com', '$2y$10$HvZ1T0xW5X5O5Z9X0X0X0uJ0X0X0X0X0X0X0X0X0X0X0X0X0X0X0', FALSE),
('spice_master', 'priya@email.com', '$2y$10$HvZ1T0xW5X5O5Z9X0X0X0uJ0X0X0X0X0X0X0X0X0X0X0X0X0X0X0', FALSE);

-- Insert data into categories table
INSERT INTO categories (name, description, image_url) VALUES
('Breakfast', 'Start your day right with these delicious breakfast recipes', 'breakfast.jpg'),
('Lunch', 'Quick and satisfying meals for midday', 'lunch.jpg'),
('Dinner', 'Hearty meals to end your day', 'dinner.jpg'),
('Desserts', 'Sweet treats for every occasion', 'desserts.jpg'),
('Vegetarian', 'Meat-free dishes full of flavor', 'vegetarian.jpg'),
('Vegan', 'Plant-based recipes without animal products', 'vegan.jpg'),
('Gluten-Free', 'Delicious recipes without gluten', 'glutenfree.jpg');

-- Insert data into recipes table
INSERT INTO recipes (title, description, ingredients, preparation_steps, cooking_time, servings, category_id, user_id, image_url, calories, protein, carbs, fat, dietary_tags) VALUES
('Classic Pancakes', 'Fluffy and delicious traditional pancakes', '2 cups flour, 2 tbsp sugar, 1 tbsp baking powder, 1/2 tsp salt, 1 3/4 cups milk, 2 eggs, 2 tbsp melted butter', '1. Mix dry ingredients\n2. Whisk wet ingredients\n3. Combine and cook on griddle', 20, 4, 1, 2, 'pancakes.jpg', 250, 6, 38, 8, 'vegetarian'),
('Avocado Toast', 'Simple and healthy breakfast', '2 slices whole grain bread, 1 avocado, salt, pepper, red pepper flakes, 2 eggs (optional)', '1. Toast bread\n2. Mash avocado and season\n3. Top toast with avocado\n4. Add fried eggs if desired', 10, 2, 1, 3, 'avocado_toast.jpg', 320, 10, 30, 18, 'vegetarian,gluten-free'),
('Vegetable Stir Fry', 'Quick and healthy vegetable dish', '2 tbsp oil, 1 onion, 2 bell peppers, 2 carrots, 1 broccoli, 3 tbsp soy sauce, 1 tbsp ginger, 2 cloves garlic', '1. Chop vegetables\n2. Heat oil in wok\n3. Stir fry vegetables\n4. Add sauce and serve', 25, 4, 5, 4, 'stirfry.jpg', 180, 5, 20, 8, 'vegetarian,vegan'),
('Chocolate Chip Cookies', 'Classic homemade cookies', '2 1/4 cups flour, 1 tsp baking soda, 1 tsp salt, 1 cup butter, 3/4 cup sugar, 3/4 cup brown sugar, 2 eggs, 2 cups chocolate chips', '1. Cream butter and sugars\n2. Add eggs\n3. Mix dry ingredients\n4. Fold in chips\n5. Bake at 375°F for 9-11 minutes', 30, 24, 4, 3, 'cookies.jpg', 150, 2, 20, 7, 'vegetarian'),
('Beef Tacos', 'Flavorful Mexican-inspired tacos', '1 lb ground beef, 1 packet taco seasoning, 8 taco shells, lettuce, tomato, cheese, sour cream', '1. Brown beef\n2. Add seasoning\n3. Prepare toppings\n4. Assemble tacos', 25, 4, 3, 2, 'tacos.jpg', 350, 20, 25, 18, NULL),
('Quinoa Salad', 'Healthy and protein-packed salad', '1 cup quinoa, 2 cups water, 1 cucumber, 1 bell pepper, 1/4 cup feta, 2 tbsp olive oil, 1 lemon', '1. Cook quinoa\n2. Chop vegetables\n3. Combine ingredients\n4. Add dressing', 20, 4, 5, 4, 'quinoa_salad.jpg', 220, 8, 30, 7, 'vegetarian,gluten-free'),
('Chicken Curry', 'Spicy and aromatic Indian curry', '1 lb chicken, 1 onion, 2 tomatoes, 2 tbsp curry powder, 1 cup coconut milk, 2 tbsp oil', '1. Sauté onions\n2. Add spices\n3. Add chicken and tomatoes\n4. Simmer with coconut milk', 45, 4, 3, 5, 'curry.jpg', 380, 30, 15, 22, 'gluten-free'),
('Vegan Brownies', 'Rich chocolate brownies without dairy', '1 cup flour, 1 cup sugar, 1/3 cup cocoa, 1/2 tsp salt, 1/2 cup vegetable oil, 1/2 cup water, 1 tsp vanilla', '1. Mix dry ingredients\n2. Add wet ingredients\n3. Bake at 350°F for 20-25 minutes', 35, 9, 6, 3, 'brownies.jpg', 200, 2, 30, 8, 'vegetarian,vegan,gluten-free'),
('Zucchini Noodles with Pesto', 'A light gluten-free alternative to pasta', '2 zucchinis, 1/4 cup pesto, salt, pepper, olive oil', '1. Spiralize zucchini\n2. Sauté in olive oil for 2 minutes\n3. Toss with pesto and serve', 10, 2, 7, 2, 'zoodles_pesto.jpg', 150, 4, 10, 10, 'vegetarian,gluten-free'),
('Stuffed Bell Peppers', 'Bell peppers filled with a flavorful rice and veggie mix', '4 bell peppers, 1 cup cooked rice, 1/2 cup corn, 1/2 cup beans, cheese (optional)', '1. Cut and hollow peppers\n2. Mix stuffing\n3. Fill peppers and bake at 375°F for 25 mins', 30, 4, 7, 3, 'stuffed_peppers.jpg', 220, 6, 30, 8, 'vegetarian,gluten-free'),
('Banana Oatmeal', 'Nutritious and filling breakfast', '1 banana, 1/2 cup oats, 1 cup milk, honey, cinnamon', '1. Cook oats\n2. Add banana and cinnamon\n3. Sweeten with honey', 10, 1, 1, 4, 'banana_oatmeal.jpg', 200, 5, 30, 4, 'vegetarian'),
('Scrambled Eggs', 'Classic fluffy scrambled eggs', '2 eggs, 1 tbsp butter, salt, pepper', '1. Beat eggs\n2. Cook in buttered pan stirring constantly', 5, 1, 1, 2, 'scrambled_eggs.jpg', 180, 12, 2, 14, 'gluten-free'),
('Chia Pudding', 'Make-ahead creamy chia breakfast', '3 tbsp chia seeds, 1 cup almond milk, 1 tsp vanilla, fruits', '1. Mix chia and milk\n2. Refrigerate overnight\n3. Add toppings', 5, 1, 1, 3, 'chia_pudding.jpg', 160, 4, 15, 7, 'vegan,gluten-free'),
('Spinach Omelette', 'Protein-packed omelette with spinach', '2 eggs, 1/4 cup chopped spinach, salt, pepper', '1. Beat eggs\n2. Cook with spinach in skillet', 6, 1, 1, 5, 'spinach_omelette.jpg', 190, 14, 2, 14, 'gluten-free'),
('Granola Yogurt Parfait', 'Layered breakfast with yogurt and fruits', '1 cup yogurt, 1/4 cup granola, berries, honey', '1. Layer yogurt, granola, and fruits\n2. Drizzle honey', 3, 1, 1, 3, 'parfait.jpg', 250, 8, 30, 9, 'vegetarian'),
('Grilled Chicken Salad', 'Lean protein with fresh greens', '1 grilled chicken breast, lettuce, tomato, cucumber, vinaigrette', '1. Slice chicken\n2. Toss with salad ingredients', 15, 2, 2, 4, 'chicken_salad.jpg', 280, 25, 8, 12, 'gluten-free'),
('BLT Sandwich', 'Classic bacon, lettuce, and tomato sandwich', '2 bread slices, 3 bacon strips, lettuce, tomato, mayo', '1. Toast bread\n2. Assemble sandwich', 10, 2, 2, 2, 'blt.jpg', 330, 12, 25, 20, ''),
('Tuna Wrap', 'Tasty and portable tuna lunch', '1 tortilla, 1 can tuna, mayo, lettuce, celery', '1. Mix tuna and mayo\n2. Wrap with lettuce and celery', 10, 2, 2, 3, 'tuna_wrap.jpg', 280, 18, 20, 14, ''),
('Veggie Burrito Bowl', 'A deconstructed burrito in a bowl', '1 cup rice, beans, corn, salsa, guacamole', '1. Layer all ingredients in a bowl', 15, 2, 2, 5, 'burrito_bowl.jpg', 350, 12, 45, 10, 'vegan,gluten-free'),
('Turkey Panini', 'Warm and cheesy sandwich', '2 slices bread, turkey slices, cheese, mustard', '1. Assemble sandwich\n2. Grill in panini press', 10, 2, 2, 4, 'turkey_panini.jpg', 380, 22, 30, 18, ''),
('Grilled Salmon', 'Healthy and flavorful dinner option', '1 salmon fillet, lemon, herbs, olive oil', '1. Marinate salmon\n2. Grill for 10 mins', 15, 1, 3, 5, 'grilled_salmon.jpg', 400, 30, 2, 20, 'gluten-free'),
('Spaghetti Bolognese', 'Classic Italian meat sauce pasta', '200g spaghetti, 1 cup ground beef sauce', '1. Cook pasta\n2. Simmer sauce\n3. Combine and serve', 30, 2, 3, 2, 'bolognese.jpg', 450, 22, 55, 16, ''),
('Stuffed Zucchini Boats', 'Low-carb stuffed zucchini with cheese', '2 zucchinis, ground meat, cheese, spices', '1. Hollow zucchinis\n2. Fill and bake at 375°F for 20 mins', 30, 2, 3, 4, 'zucchini_boats.jpg', 280, 20, 8, 12, 'gluten-free'),
('Lentil Soup', 'Comforting and nutritious soup', '1 cup lentils, onion, garlic, carrot, spices, broth', '1. Sauté vegetables\n2. Add lentils and broth\n3. Simmer for 30 mins', 40, 4, 3, 1, 'lentil_soup.jpg', 190, 12, 25, 4, 'vegan,gluten-free'),
('Eggplant Parmesan', 'Breaded eggplant baked with marinara and cheese', '1 eggplant, breadcrumbs, cheese, marinara sauce', '1. Bread and bake eggplant\n2. Layer with sauce and cheese\n3. Bake again', 45, 4, 3, 3, 'eggplant_parmesan.jpg', 350, 10, 30, 20, 'vegetarian'),
('Fruit Salad', 'Refreshing mix of seasonal fruits', 'Apple, banana, orange, grapes, mint, honey', '1. Chop all fruits\n2. Toss with honey and mint', 10, 4, 4, 5, 'fruit_salad.jpg', 150, 2, 35, 1, 'vegan,gluten-free'),
('Lemon Bars', 'Tangy and sweet lemon dessert', 'Flour, sugar, butter, eggs, lemon juice, zest', '1. Make crust\n2. Pour lemon filling\n3. Bake and cool', 35, 12, 4, 2, 'lemon_bars.jpg', 220, 3, 28, 9, 'vegetarian'),
('Apple Crisp', 'Warm baked apples with crumble topping', '4 apples, oats, brown sugar, cinnamon, butter', '1. Slice apples\n2. Mix topping\n3. Bake at 350°F for 40 mins', 50, 6, 4, 3, 'apple_crisp.jpg', 270, 2, 40, 10, 'vegetarian,gluten-free'),
('Chocolate Mousse', 'Rich and creamy chocolate dessert', 'Dark chocolate, eggs, cream, sugar', '1. Melt chocolate\n2. Fold into whipped cream and egg whites\n3. Chill', 20, 4, 4, 1, 'chocolate_mousse.jpg', 300, 5, 25, 22, 'gluten-free'),
('Rice Pudding', 'Creamy dessert with rice and milk', '1/2 cup rice, 2 cups milk, sugar, cinnamon', '1. Cook rice in milk\n2. Stir in sugar and cinnamon\n3. Chill', 30, 3, 4, 2, 'rice_pudding.jpg', 200, 4, 30, 6, 'vegetarian,gluten-free'),
('Caprese Salad', 'Fresh tomato and mozzarella salad', 'Tomato, mozzarella, basil, olive oil, balsamic', '1. Slice tomato and mozzarella\n2. Arrange and drizzle dressing', 5, 2, 5, 2, 'caprese_salad.jpg', 220, 6, 5, 18, 'vegetarian,gluten-free'),
('Mushroom Risotto', 'Creamy risotto with mushrooms', 'Arborio rice, mushrooms, broth, cheese, onion', '1. Sauté onion\n2. Add rice and broth gradually\n3. Stir in mushrooms and cheese', 40, 3, 5, 3, 'mushroom_risotto.jpg', 400, 9, 45, 15, 'vegetarian'),
('Grilled Veggie Skewers', 'Colorful skewers with assorted veggies', 'Bell peppers, zucchini, mushrooms, olive oil, herbs', '1. Skewer veggies\n2. Grill for 10 mins turning occasionally', 15, 3, 5, 4, 'veggie_skewers.jpg', 160, 4, 12, 10, 'vegan,gluten-free'),
('Stuffed Mushrooms', 'Bite-sized mushrooms filled with cheesy mix', 'Mushroom caps, cheese, herbs, breadcrumbs', '1. Stuff mushrooms\n2. Bake at 375°F for 15 mins', 20, 4, 5, 1, 'stuffed_mushrooms.jpg', 180, 5, 10, 12, 'vegetarian'),
('Sweet Potato Tacos', 'Tacos filled with roasted sweet potato and toppings', 'Sweet potato, tortillas, black beans, avocado, slaw', '1. Roast sweet potato\n2. Assemble tacos', 25, 3, 5, 3, 'sweet_potato_tacos.jpg', 320, 6, 35, 14, 'vegetarian');

-- Insert data into reviews table
INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES
(1, 3, 5, 'Perfect pancakes every time!'),
(1, 4, 4, 'My family loves this recipe'),
(2, 2, 5, 'So simple yet so delicious'),
(3, 5, 4, 'Great way to eat more vegetables'),
(4, 2, 5, 'Best cookies I ever made'),
(4, 3, 5, 'Always a crowd pleaser'),
(5, 4, 3, 'Good but could use more spice'),
(6, 5, 4, 'Healthy and filling'),
(7, 2, 5, 'Authentic flavor, loved it'),
(8, 4, 4, 'Can believe these are vegan!');