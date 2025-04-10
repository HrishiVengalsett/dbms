-- 1. Create dietary tags table
CREATE TABLE IF NOT EXISTS dietary_tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    icon VARCHAR(50),
    description TEXT
);

-- 2. Insert common dietary tags
INSERT INTO dietary_tags (name, icon, description) VALUES 
('Vegetarian', 'bi-flower1', 'No meat or fish, may include dairy/eggs'),
('Vegan', 'bi-leaf', 'No animal products'),
('Gluten-Free', 'bi-basket', 'No gluten-containing ingredients'),
('Dairy-Free', 'bi-droplet', 'No milk or dairy products'),
('Keto', 'bi-egg-fried', 'Low-carb, high-fat'),
('Low-Carb', 'bi-bread-slice', 'Reduced carbohydrate content'),
('Nut-Free', 'bi-tree', 'No tree nuts or peanuts'),
('Halal', 'bi-moon', 'Prepared according to Islamic law'),
('Kosher', 'bi-star', 'Prepared according to Jewish law');

-- 3. Create user preferences junction table
CREATE TABLE IF NOT EXISTS user_dietary_preferences (
    user_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (user_id, tag_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES dietary_tags(tag_id) ON DELETE CASCADE
);

-- 4. Create recipe tags junction table
CREATE TABLE IF NOT EXISTS recipe_dietary_tags (
    recipe_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (recipe_id, tag_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES dietary_tags(tag_id) ON DELETE CASCADE
);