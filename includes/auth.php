<?php
/**
 * Authentication Helpers
 * Suprova's Gallery
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    if (isset($_SESSION['user_id'])) return true;
    // Check remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        if ($user) {
            loginUser($user);
            return true;
        }
    }
    return false;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Require login — redirect if not authenticated
 */
function requireLogin(string $redirect = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . $redirect);
        exit;
    }
}

/**
 * Require admin — redirect if not admin
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/login.php?error=unauthorized');
        exit;
    }
}

/**
 * Set session data after successful login
 */
function loginUser(array $user): void {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_avatar']= $user['avatar'] ?? null;
}

/**
 * Get current logged-in user data from DB
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Get cart item count for header badge
 */
function getCartCount(): int {
    $db = getDB();
    if (isLoggedIn()) {
        $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $sid = session_id();
        $stmt = $db->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ?");
        $stmt->execute([$sid]);
    }
    return (int)($stmt->fetchColumn() ?: 0);
}

/**
 * Get wishlist count
 */
function getWishlistCount(): int {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}
