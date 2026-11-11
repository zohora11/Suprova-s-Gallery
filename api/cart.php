<?php
/**
 * Cart API — Add, Update, Remove
 * Suprova's Gallery
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action     = $_POST['action'] ?? $_GET['action'] ?? 'add';
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity   = max(1, (int)($_POST['quantity'] ?? 1));
$size       = trim($_POST['size'] ?? '');
$color      = trim($_POST['color'] ?? '');
$cart_id    = (int)($_POST['cart_id'] ?? 0);

$db = getDB();

// Identify cart owner
$user_id    = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

function getCartTotal($db, $user_id, $session_id): array {
    if ($user_id) {
        $stmt = $db->prepare("SELECT SUM(quantity) as cnt, SUM(COALESCE(p.sale_price,p.price)*c.quantity) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $db->prepare("SELECT SUM(quantity) as cnt, SUM(COALESCE(p.sale_price,p.price)*c.quantity) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ?");
        $stmt->execute([$session_id]);
    }
    $row = $stmt->fetch();
    return ['count' => (int)($row['cnt'] ?? 0), 'total' => (float)($row['total'] ?? 0)];
}

switch ($action) {

    case 'add':
        if (!$product_id) jsonResponse(['success' => false, 'message' => 'Invalid product'], 400);

        // Check stock
        $prod = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
        $prod->execute([$product_id]);
        $product = $prod->fetch();
        if (!$product) jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        if ($product['stock'] < 1) jsonResponse(['success' => false, 'message' => 'Product is out of stock'], 400);

        // Check if already in cart
        if ($user_id) {
            $existing = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?");
            $existing->execute([$user_id, $product_id, $size, $color]);
        } else {
            $existing = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND size = ? AND color = ?");
            $existing->execute([$session_id, $product_id, $size, $color]);
        }
        $existingItem = $existing->fetch();

        if ($existingItem) {
            $newQty = min($existingItem['quantity'] + $quantity, $product['stock']);
            $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?")->execute([$newQty, $existingItem['id']]);
        } else {
            if ($user_id) {
                $db->prepare("INSERT INTO cart (user_id, product_id, size, color, quantity) VALUES (?,?,?,?,?)")->execute([$user_id, $product_id, $size, $color, $quantity]);
            } else {
                $db->prepare("INSERT INTO cart (session_id, product_id, size, color, quantity) VALUES (?,?,?,?,?)")->execute([$session_id, $product_id, $size, $color, $quantity]);
            }
        }

        $totals = getCartTotal($db, $user_id, $session_id);
        jsonResponse(['success' => true, 'message' => 'Added to cart! 🛍️', 'cart_count' => $totals['count'], 'cart_total' => '৳' . number_format($totals['total'], 2)]);

    case 'update':
        if (!$cart_id) jsonResponse(['success' => false, 'message' => 'Invalid cart item'], 400);
        if ($quantity < 1) {
            // Remove
            $db->prepare("DELETE FROM cart WHERE id = ?")->execute([$cart_id]);
        } else {
            $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?")->execute([$quantity, $cart_id]);
        }
        $totals = getCartTotal($db, $user_id, $session_id);
        jsonResponse(['success' => true, 'message' => 'Cart updated', 'cart_count' => $totals['count'], 'cart_total' => '৳' . number_format($totals['total'], 2)]);

    case 'remove':
        if (!$cart_id) jsonResponse(['success' => false, 'message' => 'Invalid'], 400);
        $db->prepare("DELETE FROM cart WHERE id = ?")->execute([$cart_id]);
        $totals = getCartTotal($db, $user_id, $session_id);
        jsonResponse(['success' => true, 'message' => 'Item removed', 'cart_count' => $totals['count']]);

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
