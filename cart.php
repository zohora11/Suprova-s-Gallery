<?php
/**
 * Cart Page — Suprova's Gallery
 */
$pageTitle = "Shopping Cart — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Get cart items
if (isLoggedIn()) {
    $stmt = $db->prepare("
        SELECT c.*, p.name, p.image, p.stock, 
               COALESCE(p.sale_price, p.price) as unit_price,
               p.price as original_price
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $db->prepare("
        SELECT c.*, p.name, p.image, p.stock,
               COALESCE(p.sale_price, p.price) as unit_price,
               p.price as original_price
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([session_id()]);
}
$cartItems = $stmt->fetchAll();

$subtotal = array_sum(array_map(fn($i) => $i['unit_price'] * $i['quantity'], $cartItems));
$shipping = $subtotal >= 2000 ? 0 : 120;
$total    = $subtotal + $shipping;

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Shopping Cart</h1>
    </div>
</section>

<div class="cart-page">
    <div class="container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-shopping-bag"></i></div>
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added anything yet. Let's change that!</p>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div>
                    <div class="cart-items" id="cartItemsContainer">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" id="cartItem<?= $item['id'] ?>">
                                <div class="cart-item-img">
                                    <a href="<?= SITE_URL ?>/product.php?id=<?= $item['product_id'] ?>">
                                        <img src="<?= e(getProductImage($item['image'])) ?>" alt="<?= e($item['name']) ?>">
                                    </a>
                                </div>
                                <div>
                                    <h3 class="cart-item-name">
                                        <a href="<?= SITE_URL ?>/product.php?id=<?= $item['product_id'] ?>"><?= e($item['name']) ?></a>
                                    </h3>
                                    <div class="cart-item-meta">
                                        <?php if ($item['size']): ?>Size: <strong><?= e($item['size']) ?></strong><?php endif; ?>
                                        <?php if ($item['color']): ?> &nbsp;|&nbsp; Color: <strong><?= e($item['color']) ?></strong><?php endif; ?>
                                    </div>
                                    <div class="cart-item-price"><?= formatPrice($item['unit_price']) ?> each</div>
                                    <div class="quantity-selector" style="margin-bottom:0;">
                                        <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">−</button>
                                        <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" 
                                               min="1" max="<?= $item['stock'] ?>"
                                               onchange="updateCartQty(<?= $item['id'] ?>, parseInt(this.value))"
                                               id="qty<?= $item['id'] ?>">
                                        <button type="button" class="qty-btn" onclick="updateCartQty(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                    </div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-size:1.2rem;font-weight:800;color:var(--accent);margin-bottom:var(--gap-sm);">
                                        <?= formatPrice($item['unit_price'] * $item['quantity']) ?>
                                    </div>
                                    <button onclick="removeFromCart(<?= $item['id'] ?>)" 
                                            class="btn btn-ghost btn-sm" 
                                            style="color:var(--danger);" title="Remove">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:var(--gap-md);display:flex;gap:var(--gap);">
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="cart-summary">
                    <h2 class="cart-summary-title">Order Summary</h2>

                    <div class="summary-row">
                        <span>Subtotal (<?= count($cartItems) ?> items)</span>
                        <strong id="summarySubtotal"><?= formatPrice($subtotal) ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="summaryShipping">
                            <?php if ($shipping === 0): ?>
                                <span style="color:var(--success);">FREE</span>
                            <?php else: ?>
                                <?= formatPrice($shipping) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($shipping > 0): ?>
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:var(--gap-sm);">
                            Add <?= formatPrice(2000 - $subtotal) ?> more for free shipping!
                        </p>
                    <?php endif; ?>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <strong id="summaryTotal"><?= formatPrice($total) ?></strong>
                    </div>

                    <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-primary w-100 btn-lg" style="margin-top:var(--gap-md);">
                        <i class="fas fa-lock"></i> Secure Checkout
                    </a>

                    <div style="display:flex;justify-content:center;gap:var(--gap);margin-top:var(--gap);flex-wrap:wrap;">
                        <?php foreach (['fa-shield-alt'=>'Secure Payment','fa-undo'=>'Easy Returns'] as $i=>$l): ?>
                            <span style="font-size:0.75rem;color:var(--text-muted);display:flex;align-items:center;gap:4px;">
                                <i class="fas <?= $i ?>" style="color:var(--primary);"></i><?= $l ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
