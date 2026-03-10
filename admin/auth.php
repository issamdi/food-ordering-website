<?php
/**
 * Admin authentication helper
 */

session_start();

require_once __DIR__ . '/../config/config.php';

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginAdmin($username, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, is_active FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        return false;
    }
    
    if (!$admin['is_active']) {
        return false;
    }
    
    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    // Set session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    
    return true;
}

function logoutAdmin() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

function getAdminName() {
    return $_SESSION['admin_username'] ?? 'Admin';
}

function getAdminRole() {
    return $_SESSION['admin_role'] ?? 'staff';
}
