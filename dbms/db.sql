-- Create database
CREATE DATABASE IF NOT EXISTS recipe_website_2;
USE recipe_website_2;

-- Drop tables if they already exist
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS recipes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Create recipes table with added cuisine and difficulty
CREATE TABLE recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    ingredients TEXT,
    instructions TEXT,
    prep_time INT,
    cook_time INT,
    servings INT,
    image_url VARCHAR(255),
    category_id INT,
    user_id INT,
    cuisine VARCHAR(100), -- NEW
    difficulty ENUM('Easy', 'Medium', 'Hard'), -- NEW
    calories INT,
    protein FLOAT,
    fat FLOAT,
    carbohydrates FLOAT,
    tags VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Create reviews table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Insert sample users
INSERT INTO users (username, email, password) VALUES
('alice', 'alice@example.com', 'hashed_password1'),
('bob', 'bob@example.com', 'hashed_password2');

-- Insert sample categories
INSERT INTO categories (name) VALUES 
('Breakfast'), 
('Lunch'), 
('Dinner'), 
('Dessert');

-- Insert sample recipes with cuisine and difficulty
INSERT INTO recipes (
    title, description, ingredients, instructions, prep_time, cook_time, servings, image_url,
    category_id, user_id, cuisine, difficulty, calories, protein, fat, carbohydrates, tags
) VALUES
(
    'Spaghetti Carbonara',
    'Classic Italian pasta dish.',
    'Spaghetti, eggs, pancetta, parmesan, pepper',
    'Cook pasta. Fry pancetta. Mix eggs and cheese. Combine all.',
    10, 20, 2, 'images/spaghetti.jpg',
    3, 1, 'Italian', 'Medium', 500, 20, 18, 60, 'pasta,quick'
),
(
    'Vegetable Stir Fry',
    'Healthy mix of vegetables in Asian-style sauce.',
    'Broccoli, bell peppers, soy sauce, garlic, oil',
    'Chop veggies. Stir fry with sauce.',
    15, 10, 2, 'images/stirfry.jpg',
    2, 2, 'Chinese', 'Easy', 300, 8, 10, 35, 'vegetarian,quick'
),
(
    'Gulab Jamun',
    'Popular Indian dessert soaked in syrup.',
    'Milk powder, flour, sugar, cardamom, ghee',
    'Make dough, shape balls, fry, soak in syrup.',
    20, 30, 4, 'images/gulabjamun.jpg',
    4, 1, 'Indian', 'Hard', 450, 6, 20, 60, 'dessert,sweet'
),
(
    'Avocado Toast',
    'Simple and healthy breakfast toast.',
    'Bread, avocado, salt, pepper, lemon juice',
    'Toast bread. Mash avocado with lemon, salt, and pepper. Spread and serve.',
    5, 5, 1, 'images/avocadotoast.jpg',
    1, 2, 'American', 'Easy', 250, 5, 12, 22, 'breakfast,healthy,vegetarian'
),
(
    'Chicken Biryani',
    'Aromatic and flavorful Indian rice dish.',
    'Chicken, basmati rice, spices, yogurt, onions',
    'Marinate chicken. Cook rice separately. Layer and cook on low heat.',
    30, 45, 4, 'images/biryani.jpg',
    3, 1, 'Indian', 'Hard', 700, 35, 25, 70, 'rice,spicy,non-veg'
),
(
    'French Omelette',
    'Soft and fluffy omelette with butter.',
    'Eggs, butter, salt, pepper',
    'Beat eggs. Cook gently in butter, fold and serve.',
    5, 5, 1, 'images/omelette.jpg',
    1, 1, 'French', 'Easy', 200, 12, 16, 2, 'breakfast,eggs,quick'
),
(
    'Chocolate Mousse',
    'Light and airy dessert for chocolate lovers.',
    'Dark chocolate, eggs, sugar, cream',
    'Melt chocolate. Whisk eggs and cream. Fold and chill.',
    15, 0, 4, 'images/mousse.jpg',
    4, 2, 'French', 'Medium', 350, 5, 25, 30, 'dessert,chocolate'
),
(
    'Tacos al Pastor',
    'Mexican tacos with marinated pork and pineapple.',
    'Pork, pineapple, chili, corn tortillas, onions, cilantro',
    'Marinate pork, grill, slice. Serve on tortillas with toppings.',
    25, 30, 3, 'images/tacos.jpg',
    2, 1, 'Mexican', 'Medium', 500, 28, 20, 40, 'tacos,streetfood'
),
(
    'Paneer Butter Masala',
    'Creamy North Indian curry made with paneer and tomato gravy.',
    'Paneer, tomatoes, cream, butter, spices',
    'Sauté spices, blend tomatoes, cook with cream and paneer.',
    15, 25, 3, 'images/paneer.jpg',
    3, 2, 'Indian', 'Medium', 600, 20, 35, 25, 'vegetarian,curry,spicy'
),
(
    'Grilled Cheese Sandwich',
    'Crispy sandwich with melted cheese.',
    'Bread, butter, cheddar cheese',
    'Butter bread slices, add cheese, grill until golden brown.',
    5, 5, 1, 'images/grilledcheese.jpg',
    1, 1, 'American', 'Easy', 300, 10, 18, 25, 'snack,quick,cheese'
),
(
    'Sushi Rolls',
    'Traditional Japanese rolls with rice, seaweed, and fillings.',
    'Sushi rice, nori, cucumber, crab stick, soy sauce',
    'Spread rice on nori, add filling, roll and slice.',
    30, 0, 4, 'images/sushi.jpg',
    2, 2, 'Japanese', 'Hard', 400, 15, 5, 60, 'seafood,japanese,healthy'
),
(
    'Banana Pancakes',
    'Fluffy pancakes with mashed banana.',
    'Flour, eggs, banana, baking powder, milk',
    'Mix ingredients, pour batter on skillet, flip and cook.',
    10, 10, 2, 'images/bananapancakes.jpg',
    1, 1, 'American', 'Easy', 350, 8, 10, 50, 'breakfast,sweet,kids'
),
(
    'Falafel Wrap',
    'Middle Eastern wrap with crispy falafel and veggies.',
    'Chickpeas, garlic, parsley, tahini, flatbread',
    'Blend ingredients, form balls, fry, wrap in bread with sauce.',
    20, 15, 2, 'images/falafel.jpg',
    2, 2, 'Middle Eastern', 'Medium', 450, 12, 15, 55, 'vegetarian,wrap,spicy'
),
(
    'Tom Yum Soup',
    'Spicy and sour Thai soup with shrimp.',
    'Shrimp, lemongrass, lime juice, chili, mushrooms',
    'Boil broth, add herbs, add shrimp and mushrooms.',
    15, 20, 2, 'images/tomyum.jpg',
    2, 1, 'Thai', 'Medium', 200, 15, 8, 10, 'soup,spicy,thai'
),
(
    'Choco Lava Cake',
    'Soft cake with a gooey chocolate center.',
    'Dark chocolate, butter, eggs, flour, sugar',
    'Mix, bake till edges set but center is gooey.',
    10, 12, 2, 'images/lavacake.jpg',
    4, 2, 'French', 'Medium', 450, 6, 28, 40, 'dessert,chocolate,party'
),
(
    'Caesar Salad',
    'Classic salad with romaine, croutons, and dressing.',
    'Romaine, parmesan, croutons, Caesar dressing',
    'Toss ingredients and serve chilled.',
    10, 0, 2, 'images/caesarsalad.jpg',
    2, 1, 'Italian', 'Easy', 320, 10, 22, 18, 'salad,healthy'
),
(
    'Egg Fried Rice',
    'Quick rice stir-fried with eggs and veggies.',
    'Cooked rice, eggs, soy sauce, vegetables',
    'Scramble eggs, stir-fry with rice and veggies.',
    10, 10, 2, 'images/eggfriedrice.jpg',
    2, 2, 'Chinese', 'Easy', 400, 12, 10, 50, 'quick,rice,lunch'
),
(
    'French Onion Soup',
    'Caramelized onion soup with cheese toast.',
    'Onions, broth, butter, cheese, bread',
    'Caramelize onions, simmer broth, bake with cheese toast.',
    20, 30, 3, 'images/onionsoup.jpg',
    3, 1, 'French', 'Medium', 380, 8, 18, 30, 'soup,winter,comfort'
),
(
    'Chicken Shawarma Wrap',
    'Middle Eastern wrap with grilled chicken and sauce.',
    'Chicken, spices, pita bread, garlic sauce',
    'Marinate, grill chicken, wrap with fillings.',
    25, 15, 2, 'images/shawarma.jpg',
    2, 2, 'Middle Eastern', 'Medium', 550, 25, 20, 45, 'wrap,spicy,streetfood'
),
(
    'Pesto Pasta',
    'Pasta tossed with basil pesto sauce.',
    'Pasta, basil, pine nuts, garlic, parmesan',
    'Blend pesto ingredients, cook pasta, mix and serve.',
    10, 15, 2, 'images/pestopasta.jpg',
    3, 1, 'Italian', 'Easy', 480, 10, 16, 60, 'vegetarian,pasta'
),
(
    'Mango Lassi',
    'Refreshing yogurt drink with mango pulp.',
    'Mango, yogurt, sugar, cardamom',
    'Blend all ingredients until smooth and serve cold.',
    5, 0, 2, 'images/mangolassi.jpg',
    4, 2, 'Indian', 'Easy', 180, 5, 5, 30, 'drink,sweet,refreshing'
);
INSERT INTO reviews (recipe_id, user_id, rating, comment) VALUES
(1, 2, 5, 'Absolutely delicious! Tasted just like in Rome.'),
(1, 1, 4, 'Great recipe, though I added some garlic for extra flavor.'),

(2, 1, 5, 'Perfect weeknight meal! So quick and healthy.'),
(2, 2, 3, 'Good but needed more sauce for my taste.'),

(3, 2, 5, 'My favorite Indian dessert! Turned out perfectly.'),

(4, 1, 4, 'Simple but delicious. Added some chili flakes for kick.'),
(4, 2, 5, 'My go-to breakfast now! So creamy and satisfying.'),

(5, 1, 5, 'Worth the effort - restaurant quality biryani!'),
(5, 2, 4, 'Amazing flavors, though mine was a bit dry.'),

(6, 2, 5, 'Perfect technique - so fluffy and buttery!'),

(7, 1, 5, 'Light as air and so chocolatey. Dinner party hit!'),
(7, 2, 4, 'Great texture, though I prefer it less sweet.'),

(8, 1, 5, 'The pineapple makes these tacos extraordinary!'),

(9, 2, 5, 'Better than my local Indian restaurant! So creamy.'),

(10, 1, 4, 'Classic comfort food. Used aged cheddar - amazing!'),

(11, 2, 3, 'Tasty but rolling was harder than I expected.'),

(12, 1, 5, 'Kids loved them! Will make again this weekend.'),

(13, 2, 5, 'Crispy outside, fluffy inside - perfect falafel!'),

(16, 1, 4, 'Great classic recipe. Added extra anchovies.'),

(17, 2, 5, 'Better than takeout! So quick and flavorful.'),

(18, 1, 5, 'Worth the long cook time - incredible depth of flavor!');

