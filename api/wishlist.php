<?php
/**
 * Wishlist API — Toggle
 * Suprova's Gallery
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login first', 'redirect' => SITE_URL . '/login.php']);
}

$product_id = (int)($_POST['product_id'] ?? 0);
if (!$product_id) jsonResponse(['success' => false, 'message' => 'Invalid product']);

$db = getDB();
$user_id = $_SESSION['user_id'];

// Check if exists
$stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$exists = $stmt->fetch();

if ($exists) {
    $db->prepare("DELETE FROM wishlist WHERE id = ?")->execute([$exists['id']]);
    $action = 'removed';
    $message = 'Removed from wishlist';
} else {
    $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)")->execute([$user_id, $product_id]);
    $action = 'added';
    $message = 'Added to wishlist! ❤️';
}

$stmt2 = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt2->execute([$user_id]);
$count = (int)$stmt2->fetchColumn();

jsonResponse(['success' => true, 'action' => $action, 'message' => $message, 'wishlist_count' => $count]);
