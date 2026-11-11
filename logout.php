<?php
/**
 * Logout — Suprova's Gallery
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    // Clear remember token
    $db = getDB();
    $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")->execute([$_SESSION['user_id']]);
    setcookie('remember_token', '', time() - 3600, '/');
    session_destroy();
}

session_start();
setFlash('info', 'You have been logged out. See you again soon! 👋');
header('Location: ' . SITE_URL . '/login.php');
exit;
