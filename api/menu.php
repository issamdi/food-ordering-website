<?php
/**
 * Menu API — returns all available menu items as JSON
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $pdo = getDBConnection();
    
    $category = isset($_GET['category']) ? trim($_GET['category']) : null;
    
    if ($category) {
        $stmt = $pdo->prepare("SELECT id, name, description, price, category, image_url, preparation_time, ingredients FROM menu_items WHERE is_available = 1 AND category = ? ORDER BY name");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT id, name, description, price, category, image_url, preparation_time, ingredients FROM menu_items WHERE is_available = 1 ORDER BY category, name");
    }
    
    $items = $stmt->fetchAll();
    
    // Get categories
    $cat_stmt = $pdo->query("SELECT DISTINCT category FROM menu_items WHERE is_available = 1 ORDER BY category");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'categories' => $categories
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load menu']);
}
