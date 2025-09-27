<?php
/**
 * Database Configuration
 * 
 * SECURITY NOTES:
 * 1. Keep this file outside the web root in production
 * 2. Use environment variables for sensitive data
 * 3. Use strong passwords and limit database user permissions
 * 4. Enable SSL for database connections in production
 */

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'food_order');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Stripe configuration
// IMPORTANT: Replace these with YOUR actual Stripe keys to receive payments in YOUR account
// Get your keys from: https://dashboard.stripe.com/apikeys
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_YOUR_ACTUAL_PUBLISHABLE_KEY_HERE');
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_YOUR_ACTUAL_SECRET_KEY_HERE');
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_YOUR_ACTUAL_WEBHOOK_SECRET_HERE');

// Application configuration
define('APP_NAME', 'Your Restaurant Name');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/food-order');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Email configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('FROM_EMAIL', getenv('FROM_EMAIL') ?: 'orders@yourrestaurant.com');
define('FROM_NAME', getenv('FROM_NAME') ?: 'Your Restaurant');

// Security settings
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'your-32-character-secret-key-here');
define('SESSION_LIFETIME', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 1800); // 30 minutes

// Payment settings
define('CURRENCY', 'USD');
define('DELIVERY_FEE', 3.99);
define('TAX_RATE', 0.08);
define('MIN_ORDER_AMOUNT', 10.00);

// File upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

/**
 * Get database connection
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            if (APP_ENV === 'development') {
                throw new Exception("Database connection failed: " . $e->getMessage());
            } else {
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Security helper functions
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it's 10 digits (US format)
    return strlen($phone) === 10;
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Logging functions
 */
function logActivity($level, $message, $context = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $log_file = __DIR__ . '/logs/' . date('Y-m-d') . '.log';
    $log_line = json_encode($log_entry) . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
}

function logError($message, $context = []) {
    logActivity('ERROR', $message, $context);
}

function logInfo($message, $context = []) {
    logActivity('INFO', $message, $context);
}

function logWarning($message, $context = []) {
    logActivity('WARNING', $message, $context);
}

/**
 * Response helper functions
 */
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status_code = 400, $details = null) {
    $response = ['error' => $message];
    if ($details && APP_ENV === 'development') {
        $response['details'] = $details;
    }
    jsonResponse($response, $status_code);
}

function successResponse($data = [], $message = 'Success') {
    $response = ['success' => true, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    jsonResponse($response);
}

/**
 * Rate limiting
 */
function checkRateLimit($identifier, $max_attempts = 60, $window = 3600) {
    $cache_file = __DIR__ . '/cache/rate_limit_' . md5($identifier) . '.json';
    
    // Create cache directory if it doesn't exist
    $cache_dir = dirname($cache_file);
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $current_time = time();
    $attempts = [];
    
    // Load existing attempts
    if (file_exists($cache_file)) {
        $attempts = json_decode(file_get_contents($cache_file), true) ?: [];
    }
    
    // Remove old attempts outside the window
    $attempts = array_filter($attempts, function($timestamp) use ($current_time, $window) {
        return ($current_time - $timestamp) < $window;
    });
    
    // Check if limit exceeded
    if (count($attempts) >= $max_attempts) {
        return false;
    }
    
    // Add current attempt
    $attempts[] = $current_time;
    
    // Save attempts
    file_put_contents($cache_file, json_encode($attempts), LOCK_EX);
    
    return true;
}
?>