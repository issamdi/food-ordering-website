<?php
/**
 * Secure Payment Processing with Stripe
 * 
 * IMPORTANT SECURITY NOTES:
 * 1. Never store credit card information in your database
 * 2. Always use HTTPS in production
 * 3. Validate all inputs server-side
 * 4. Use environment variables for API keys
 * 5. Log all transactions for security auditing
 */

// Include configuration and dependencies
require_once '../config/config.php';

// Include Stripe PHP library (install via Composer: composer require stripe/stripe-php)
// For development, you can download the library manually
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    // Fallback for manual installation
    require_once '../lib/stripe-php/init.php';
}

// Enable error reporting for development (disable in production)
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS headers (adjust for your domain in production)
if (APP_ENV === 'development') {
    header('Access-Control-Allow-Origin: *');
} else {
    header('Access-Control-Allow-Origin: ' . APP_URL);
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Rate limiting
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($client_ip, 10, 3600)) { // 10 requests per hour
    errorResponse('Too many requests. Please try again later.', 429);
}

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required_fields = ['token', 'amount', 'customer', 'items'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Sanitize and validate input
    $token = sanitizeInput($data['token']);
    $amount = (float) $data['amount'];
    $customer_data = $data['customer'];
    $items = $data['items'];
    
    // Validate amount (minimum order amount)
    if ($amount < MIN_ORDER_AMOUNT) {
        throw new Exception('Amount must be at least $' . MIN_ORDER_AMOUNT);
    }
    
    // Validate customer data
    if (!validateEmail($customer_data['email'])) {
        throw new Exception('Invalid email address');
    }
    
    if (!validatePhone($customer_data['phone'])) {
        throw new Exception('Invalid phone number');
    }
    
    // Sanitize customer data
    foreach ($customer_data as $key => $value) {
        $customer_data[$key] = sanitizeInput($value);
    }
    
    // Convert amount to cents for Stripe
    $amount_cents = (int) ($amount * 100);
    
    // Generate order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    
    // Get database connection
    $pdo = getDBConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Create customer in Stripe
        $stripe_customer = \Stripe\Customer::create([
            'email' => $customer_data['email'],
            'name' => $customer_data['name'],
            'phone' => $customer_data['phone'],
            'address' => [
                'line1' => $customer_data['address'],
            ],
            'metadata' => [
                'order_type' => 'food_delivery',
                'restaurant' => APP_NAME,
                'order_number' => $order_number,
            ],
        ]);
        
        // Create payment intent
        $payment_intent = \Stripe\PaymentIntent::create([
            'amount' => $amount_cents,
            'currency' => strtolower(CURRENCY),
            'customer' => $stripe_customer->id,
            'payment_method_data' => [
                'type' => 'card',
                'card' => ['token' => $token],
            ],
            'confirmation_method' => 'manual',
            'confirm' => true,
            'description' => "Food order from " . APP_NAME,
            'metadata' => [
                'order_number' => $order_number,
                'customer_name' => $customer_data['name'],
                'customer_email' => $customer_data['email'],
                'items_count' => count($items),
            ],
            'receipt_email' => $customer_data['email'],
        ]);
        
        // Calculate order totals
        $subtotal = array_sum(array_map(function($item) {
            return $item['price'] * $item['quantity'];
        }, $items));
        
        $tax_amount = $subtotal * TAX_RATE;
        $delivery_fee = DELIVERY_FEE;
        $total = $subtotal + $tax_amount + $delivery_fee;
        
        // Insert order into database
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, payment_intent_id, customer_name, customer_email, 
                customer_phone, delivery_address, items, subtotal, tax_amount, 
                delivery_fee, total_amount, status, payment_status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', 'paid', NOW())
        ");
        
        $stmt->execute([
            $order_number,
            $payment_intent->id,
            $customer_data['name'],
            $customer_data['email'],
            $customer_data['phone'],
            $customer_data['address'],
            json_encode($items),
            $subtotal,
            $tax_amount,
            $delivery_fee,
            $total,
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insert order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, food_name, food_price, quantity, item_total) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $item_total = $item['price'] * $item['quantity'];
            $stmt->execute([
                $order_id,
                $item['title'],
                $item['price'],
                $item['quantity'],
                $item_total,
            ]);
        }
        
        // Log successful transaction
        $stmt = $pdo->prepare("
            INSERT INTO transaction_logs (
                payment_intent_id, customer_email, amount, currency, status, 
                ip_address, user_agent, created_at
            ) VALUES (?, ?, ?, ?, 'succeeded', ?, ?, NOW())
        ");
        
        $stmt->execute([
            $payment_intent->id,
            $customer_data['email'],
            $total,
            CURRENCY,
            $client_ip,
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        // Log successful payment
        logInfo('Payment processed successfully', [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'payment_intent_id' => $payment_intent->id,
            'amount' => $total,
            'customer_email' => $customer_data['email']
        ]);
        
        // Send confirmation email (implement your email system)
        // sendOrderConfirmationEmail($customer_data['email'], $order_number, $items);
        
        // Return success response
        successResponse([
            'order_id' => $order_id,
            'order_number' => $order_number,
            'payment_intent_id' => $payment_intent->id,
            'customer_id' => $stripe_customer->id,
            'amount' => $total,
        ], 'Payment processed successfully');
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollback();
        throw $e;
    }
    
} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    logError('Card declined', [
        'error' => $e->getError()->message,
        'customer_email' => $customer_data['email'] ?? 'unknown'
    ]);
    errorResponse('Card declined: ' . $e->getError()->message, 400);
    
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Invalid request
    logError('Invalid Stripe request', ['error' => $e->getError()->message]);
    errorResponse('Invalid request: ' . $e->getError()->message, 400);
    
} catch (\Stripe\Exception\AuthenticationException $e) {
    // Authentication failed
    logError('Stripe authentication failed', ['error' => $e->getMessage()]);
    errorResponse('Payment system unavailable', 401);
    
} catch (\Stripe\Exception\ApiConnectionException $e) {
    // Network problem
    logError('Stripe API connection failed', ['error' => $e->getMessage()]);
    errorResponse('Payment system unavailable. Please try again later.', 502);
    
} catch (Exception $e) {
    // General error
    logError('Payment processing error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (APP_ENV === 'development') {
        errorResponse('Error: ' . $e->getMessage(), 500);
    } else {
        errorResponse('Payment processing failed. Please try again.', 500);
    }
}
?>

try {
    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate required fields
    $required_fields = ['token', 'amount', 'customer', 'items'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Sanitize and validate input
    $token = sanitizeInput($data['token']);
    $amount = (int) ($data['amount'] * 100); // Convert to cents
    $customer_data = $data['customer'];
    $items = $data['items'];
    
    // Validate amount (minimum $1.00)
    if ($amount < 100) {
        throw new Exception('Amount must be at least $1.00');
    }
    
    // Validate customer data
    if (!filter_var($customer_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Create customer in Stripe
    $customer = \Stripe\Customer::create([
        'email' => $customer_data['email'],
        'name' => $customer_data['name'],
        'phone' => $customer_data['phone'],
        'address' => [
            'line1' => $customer_data['address'],
        ],
        'metadata' => [
            'order_type' => 'food_delivery',
            'restaurant' => $config['company_name'],
        ],
    ]);
    
    // Create payment intent
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $amount,
        'currency' => $config['currency'],
        'customer' => $customer->id,
        'payment_method_data' => [
            'type' => 'card',
            'card' => ['token' => $token],
        ],
        'confirmation_method' => 'manual',
        'confirm' => true,
        'description' => "Food order from {$config['company_name']}",
        'metadata' => [
            'customer_name' => $customer_data['name'],
            'customer_email' => $customer_data['email'],
            'order_items' => json_encode($items),
            'order_id' => generateOrderId(),
        ],
        'receipt_email' => $customer_data['email'],
    ]);
    
    // Log successful transaction (implement your logging system)
    logTransaction([
        'payment_intent_id' => $payment_intent->id,
        'customer_id' => $customer->id,
        'amount' => $amount,
        'status' => 'succeeded',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
    
    // Save order to database (implement your database logic)
    $order_id = saveOrderToDatabase([
        'payment_intent_id' => $payment_intent->id,
        'customer' => $customer_data,
        'items' => $items,
        'amount' => $amount / 100, // Convert back to dollars
        'status' => 'confirmed',
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    
    // Send confirmation email (implement your email system)
    sendOrderConfirmationEmail($customer_data['email'], $order_id, $items);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'payment_intent_id' => $payment_intent->id,
        'order_id' => $order_id,
        'customer_id' => $customer->id,
        'message' => 'Payment processed successfully',
    ]);
    
} catch (\Stripe\Exception\CardException $e) {
    // Card was declined
    http_response_code(400);
    echo json_encode([
        'error' => 'Card declined',
        'message' => $e->getError()->message,
    ]);
    
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Invalid request
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid request',
        'message' => $e->getError()->message,
    ]);
    
} catch (\Stripe\Exception\AuthenticationException $e) {
    // Authentication failed
    http_response_code(401);
    echo json_encode([
        'error' => 'Authentication failed',
        'message' => 'Invalid API key',
    ]);
    
} catch (\Stripe\Exception\ApiConnectionException $e) {
    // Network problem
    http_response_code(502);
    echo json_encode([
        'error' => 'Network error',
        'message' => 'Unable to connect to payment processor',
    ]);
    
} catch (Exception $e) {
    // General error
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
    ]);
}

/**
 * Security Functions
 */

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateOrderId() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function logTransaction($data) {
    // Implement your logging system
    // Example: write to file, database, or logging service
    $log_entry = date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL;
    file_put_contents('logs/transactions.log', $log_entry, FILE_APPEND | LOCK_EX);
}

function saveOrderToDatabase($order_data) {
    // Implement your database logic
    // This is a placeholder - use your actual database implementation
    
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=food_order', $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (payment_intent_id, customer_name, customer_email, 
                               customer_phone, delivery_address, items, total_amount, 
                               status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $order_data['payment_intent_id'],
            $order_data['customer']['name'],
            $order_data['customer']['email'],
            $order_data['customer']['phone'],
            $order_data['customer']['address'],
            json_encode($order_data['items']),
            $order_data['amount'],
            $order_data['status'],
            $order_data['created_at'],
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception("Failed to save order");
    }
}

function sendOrderConfirmationEmail($email, $order_id, $items) {
    // Implement your email system
    // Example: use PHPMailer, SendGrid, or similar service
    
    $subject = "Order Confirmation - Order #{$order_id}";
    $message = "Thank you for your order! Your order #{$order_id} has been confirmed.";
    
    // Use a proper email service in production
    mail($email, $subject, $message, [
        'From' => 'orders@yourrestaurant.com',
        'Content-Type' => 'text/html; charset=UTF-8',
    ]);
}

/**
 * Webhook endpoint for Stripe events
 * Place this in a separate file (webhook.php)
 */
function handleStripeWebhook() {
    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = $config['webhook_secret'];
    
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
        
        // Handle different event types
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $payment_intent = $event->data->object;
                // Update order status in database
                updateOrderStatus($payment_intent->id, 'paid');
                break;
                
            case 'payment_intent.payment_failed':
                $payment_intent = $event->data->object;
                // Update order status in database
                updateOrderStatus($payment_intent->id, 'failed');
                break;
                
            default:
                error_log('Unhandled event type: ' . $event->type);
        }
        
        http_response_code(200);
        
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        http_response_code(400);
        error_log('Webhook signature verification failed');
    }
}

function updateOrderStatus($payment_intent_id, $status) {
    // Update order status in your database
    // Implementation depends on your database structure
}
?>