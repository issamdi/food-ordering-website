-- Food Order Database Schema
-- Run this script to create the necessary tables for your food ordering system

CREATE DATABASE IF NOT EXISTS food_order;
USE food_order;

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    stripe_customer_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    payment_intent_id VARCHAR(255),
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20),
    delivery_address TEXT,
    items JSON NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    delivery_fee DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled', 'failed') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    notes TEXT,
    estimated_delivery_time DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_customer_email (customer_email),
    INDEX idx_payment_intent (payment_intent_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
);

-- Order items table (normalized structure)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    food_price DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    item_total DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- Transaction logs table for security auditing
CREATE TABLE IF NOT EXISTS transaction_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_intent_id VARCHAR(255),
    customer_email VARCHAR(255),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'USD',
    status VARCHAR(50),
    stripe_response JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_payment_intent (payment_intent_id),
    INDEX idx_customer_email (customer_email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Food menu table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(500),
    is_available BOOLEAN DEFAULT TRUE,
    preparation_time INT DEFAULT 15, -- in minutes
    ingredients TEXT,
    allergens VARCHAR(255),
    calories INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_is_available (is_available),
    INDEX idx_price (price)
);

-- Insert sample admin user (password: admin123 - change this!)
INSERT INTO admin_users (username, email, password_hash, role) VALUES 
('admin', 'admin@yourrestaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, image_url, preparation_time, ingredients) VALUES 
('Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella cheese, and fresh basil', 12.99, 'Pizza', 'images/menu-pizza.jpg', 20, 'Dough, Tomato Sauce, Mozzarella, Basil, Olive Oil'),
('Chicken Burger', 'Grilled chicken breast with lettuce, tomato, and mayo on brioche bun', 9.99, 'Burgers', 'images/menu-burger.jpg', 15, 'Chicken Breast, Brioche Bun, Lettuce, Tomato, Mayo'),
('Vegetable Momo', 'Steamed dumplings filled with fresh vegetables and spices', 8.99, 'Appetizers', 'images/menu-momo.jpg', 25, 'Flour, Cabbage, Carrot, Onion, Ginger, Garlic, Spices');

-- Create views for reporting
CREATE VIEW order_summary AS
SELECT 
    DATE(created_at) as order_date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_order_value,
    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders
FROM orders 
GROUP BY DATE(created_at)
ORDER BY order_date DESC;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE GetOrderDetails(IN order_id INT)
BEGIN
    SELECT 
        o.*,
        GROUP_CONCAT(
            CONCAT(oi.food_name, ' (', oi.quantity, 'x $', oi.food_price, ')')
            SEPARATOR ', '
        ) as order_items_summary
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = order_id
    GROUP BY o.id;
END //

CREATE PROCEDURE UpdateOrderStatus(
    IN order_id INT, 
    IN new_status VARCHAR(20),
    IN estimated_time DATETIME
)
BEGIN
    UPDATE orders 
    SET status = new_status, 
        estimated_delivery_time = estimated_time,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = order_id;
END //

DELIMITER ;

-- Grant appropriate permissions (adjust as needed)
-- CREATE USER 'food_order_user'@'localhost' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE ON food_order.* TO 'food_order_user'@'localhost';
-- FLUSH PRIVILEGES;