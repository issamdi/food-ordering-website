<?php
/**
 * Database Setup Script
 * Visit this page once to create the database and tables.
 * Delete or restrict access to this file after setup.
 */

$host = 'localhost';
$user = 'root';
$pass = 'idaoudi';
$dbname = 'food_order';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // Connect without database
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $messages[] = "Database '$dbname' created.";
        
        $pdo->exec("USE `$dbname`");
        
        // Customers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            stripe_customer_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $messages[] = "Table 'customers' ready.";
        
        // Orders table
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
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
            status ENUM('pending','confirmed','preparing','ready','delivered','cancelled','failed') DEFAULT 'pending',
            payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
            notes TEXT,
            estimated_delivery_time DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer_email (customer_email),
            INDEX idx_payment_intent (payment_intent_id),
            INDEX idx_status (status),
            INDEX idx_payment_status (payment_status),
            INDEX idx_created_at (created_at)
        )");
        $messages[] = "Table 'orders' ready.";
        
        // Order items
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            food_name VARCHAR(255) NOT NULL,
            food_price DECIMAL(8,2) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            item_total DECIMAL(8,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id)
        )");
        $messages[] = "Table 'order_items' ready.";
        
        // Transaction logs
        $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_logs (
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
        )");
        $messages[] = "Table 'transaction_logs' ready.";
        
        // Admin users
        $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','manager','staff') DEFAULT 'staff',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $messages[] = "Table 'admin_users' ready.";
        
        // Menu items
        $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(8,2) NOT NULL,
            category VARCHAR(100),
            image_url VARCHAR(500),
            is_available BOOLEAN DEFAULT TRUE,
            preparation_time INT DEFAULT 15,
            ingredients TEXT,
            allergens VARCHAR(255),
            calories INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_is_available (is_available),
            INDEX idx_price (price)
        )");
        $messages[] = "Table 'menu_items' ready.";
        
        // Insert sample admin (only if not exists)
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')")
                ->execute(['admin', 'admin@restaurant.com', $hash]);
            $messages[] = "Admin user created (username: admin, password: admin123).";
        } else {
            $messages[] = "Admin user already exists.";
        }
        
        // Insert sample menu items (only if empty)
        $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO menu_items (name, description, price, category, image_url, preparation_time, ingredients) VALUES 
                ('Margherita Pizza', 'Classic pizza with tomato sauce, mozzarella cheese, and fresh basil', 12.99, 'Pizza', 'images/menu-pizza.jpg', 20, 'Dough, Tomato Sauce, Mozzarella, Basil, Olive Oil'),
                ('Chicken Burger', 'Grilled chicken breast with lettuce, tomato, and mayo on brioche bun', 9.99, 'Burgers', 'images/menu-burger.jpg', 15, 'Chicken Breast, Brioche Bun, Lettuce, Tomato, Mayo'),
                ('Vegetable Momo', 'Steamed dumplings filled with fresh vegetables and spices', 8.99, 'Appetizers', 'images/menu-momo.jpg', 25, 'Flour, Cabbage, Carrot, Onion, Ginger, Garlic, Spices'),
                ('Hawaiian Chicken Pizza', 'Chicken, pineapple, and mozzarella with BBQ sauce', 14.99, 'Pizza', 'images/menu-pizza.jpg', 20, 'Dough, Chicken, Pineapple, Mozzarella, BBQ Sauce'),
                ('Chocolate Brownie', 'Rich dark chocolate brownie with vanilla ice cream', 6.99, 'Desserts', 'images/menu-dessert.jpg', 10, 'Dark Chocolate, Butter, Sugar, Eggs, Flour'),
                ('Caesar Salad', 'Crisp romaine lettuce with Caesar dressing and croutons', 7.99, 'Salads', 'images/menu-salad.jpg', 10, 'Romaine, Parmesan, Croutons, Caesar Dressing'),
                ('Fresh Orange Juice', 'Freshly squeezed orange juice', 4.99, 'Drinks', 'images/menu-juice.jpg', 5, 'Fresh Oranges')
            ");
            $messages[] = "Sample menu items inserted (7 items).";
        } else {
            $messages[] = "Menu items already exist.";
        }
        
        $messages[] = "✅ Setup complete! You can now use the admin panel.";
        
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Food Order</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            color: #e0e0e0;
            padding: 2rem;
        }
        .setup-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }
        .setup-card h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .setup-card p { color: rgba(255,255,255,0.6); margin-bottom: 1.5rem; font-size: 0.9rem; }
        .info-box { background: rgba(102,126,234,0.1); border: 1px solid rgba(102,126,234,0.2); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; }
        .info-box strong { color: #667eea; }
        .btn-setup {
            width: 100%; padding: 0.85rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none; border-radius: 10px; color: #fff;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }
        .btn-setup:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(102,126,234,0.3); }
        .msg { padding: 0.6rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; font-size: 0.8rem; }
        .msg-success { background: rgba(72,199,142,0.15); border: 1px solid rgba(72,199,142,0.3); color: #48c78e; }
        .msg-error { background: rgba(255,77,77,0.15); border: 1px solid rgba(255,77,77,0.3); color: #ff6b6b; }
        .links { margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; }
        .links a { color: #667eea; text-decoration: none; font-size: 0.85rem; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1><i class="fas fa-database"></i> Database Setup</h1>
        <p>Click the button below to create the database, tables, and sample data for the food ordering system.</p>
        
        <div class="info-box">
            <strong>Connection:</strong> localhost / root / (no password)<br>
            <strong>Database:</strong> food_order<br>
            <strong>Admin Login:</strong> admin / admin123
        </div>
        
        <?php foreach ($messages as $m): ?>
            <div class="msg msg-success"><i class="fas fa-check"></i> <?php echo htmlspecialchars($m); ?></div>
        <?php endforeach; ?>
        
        <?php foreach ($errors as $e): ?>
            <div class="msg msg-error"><i class="fas fa-times"></i> <?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
        
        <?php if (empty($messages)): ?>
            <form method="POST">
                <button type="submit" name="setup" class="btn-setup">
                    <i class="fas fa-play"></i> Run Setup
                </button>
            </form>
        <?php else: ?>
            <div class="links">
                <a href="admin/login.php"><i class="fas fa-lock"></i> Admin Login</a>
                <a href="index.html"><i class="fas fa-home"></i> Website</a>
                <a href="api/menu.php"><i class="fas fa-utensils"></i> Menu API</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
