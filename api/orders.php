<?php
/**
 * Orders API — handles order placement from the frontend
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // GET — retrieve order by order number
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $order_number = isset($_GET['order_number']) ? trim($_GET['order_number']) : '';
        
        if (empty($order_number)) {
            http_response_code(400);
            echo json_encode(['error' => 'Order number is required']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$order_number]);
        $order = $stmt->fetch();
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }
        
        // Get order items
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['order_items'] = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $order]);
        exit;
    }
    
    // POST — create a new order
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }
    
    // Validate required fields
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $items = $data['items'] ?? [];
    
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email, phone, and address are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    
    if (empty($items) || !is_array($items)) {
        http_response_code(400);
        echo json_encode(['error' => 'At least one item is required']);
        exit;
    }
    
    // Sanitize
    $name = htmlspecialchars(strip_tags($name), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(strip_tags($phone), ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars(strip_tags($address), ENT_QUOTES, 'UTF-8');
    
    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
    }
    
    $tax_amount = round($subtotal * TAX_RATE, 2);
    $delivery_fee = DELIVERY_FEE;
    $total = round($subtotal + $tax_amount + $delivery_fee, 2);
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, delivery_address, items, subtotal, tax_amount, delivery_fee, total_amount, status, payment_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', NOW())
        ");
        $stmt->execute([
            $order_number, $name, $email, $phone, $address,
            json_encode($items), $subtotal, $tax_amount, $delivery_fee, $total
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, food_name, food_price, quantity, item_total) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $qty = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $item_total = round($price * $qty, 2);
            $food_name = htmlspecialchars(strip_tags($item['title'] ?? $item['name'] ?? 'Item'), ENT_QUOTES, 'UTF-8');
            
            $stmt->execute([$order_id, $food_name, $price, $qty, $item_total]);
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'data' => [
                'order_id' => $order_id,
                'order_number' => $order_number,
                'total' => $total,
                'subtotal' => $subtotal,
                'tax' => $tax_amount,
                'delivery_fee' => $delivery_fee,
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Failed to process order'];
    if (APP_ENV === 'development') {
        $response['details'] = $e->getMessage();
    }
    echo json_encode($response);
}
