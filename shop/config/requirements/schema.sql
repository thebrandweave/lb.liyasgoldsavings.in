CREATE TABLE IF NOT EXISTS shop_users (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerUniqueID VARCHAR(50),
    Name VARCHAR(255),
    Contact VARCHAR(50),
    Email VARCHAR(255),
    PasswordHash VARCHAR(255) DEFAULT '$2y$10$f8RpDnV887jmqZKOTEm/oesy7nKRboD8HxH5yQMF0xdLO0aTGLnZm',
    Address TEXT
);


-- CREATE VIEW users AS
-- SELECT 
--     CustomerID,
--     CustomerUniqueID,
--     Name,
--     Contact,
--     Email,
--     PasswordHash,
--     Address,
--     'main_db' AS Source
-- FROM u229215627_goldenDreamSQL.Customers

-- UNION

-- SELECT 
--     CustomerID,
--     CustomerUniqueID,
--     Name,
--     Contact,
--     Email,
--     PasswordHash,
--     Address,
--     'shop_db' AS Source
-- FROM shop_users;



CREATE TABLE IF NOT EXISTS ShopAdmin (
    ShopAdminID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255),
    Email VARCHAR(255),
    PasswordHash VARCHAR(255),
    Status ENUM('Active', 'Inactive') DEFAULT 'Active',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255)
);


CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    description TEXT,
    price DECIMAL(10,2),
    stock INT,
    image_url VARCHAR(255),
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);


CREATE TABLE IF NOT EXISTS cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    CustomerUniqueID VARCHAR(50), 
    product_id INT,
    quantity INT DEFAULT 1,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);



CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    CustomerUniqueID VARCHAR(50), 
    total_amount DECIMAL(10,2),
    order_status ENUM('pending', 'successful', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price_at_time DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE IF NOT EXISTS product_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_url VARCHAR(255) NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS shopnotifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    CustomerUniqueID VARCHAR(50) NULL, 
    admin_id INT NULL,
    type VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES ShopAdmin(ShopAdminID) ON DELETE CASCADE
);
