<?php
/**
 * Checkout Page — Suprova's Gallery
 */
$pageTitle = "Checkout — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();

// Get cart items
$stmt = $db->prepare("
    SELECT c.*, p.name, p.image, COALESCE(p.sale_price, p.price) as unit_price, p.stock
    FROM cart c JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) { header('Location: ' . SITE_URL . '/cart.php'); exit; }

$subtotal = array_sum(array_map(fn($i) => $i['unit_price'] * $i['quantity'], $cartItems));
$shipping = $subtotal >= 2000 ? 0 : 120;
$total    = $subtotal + $shipping;

$user = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['shipping_name','shipping_email','shipping_phone','shipping_address','shipping_city','shipping_state','shipping_zip'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');

    // Validation
    if (!$data['shipping_name'])                   $errors['shipping_name']    = 'Full name is required';
    if (!isValidEmail($data['shipping_email']))     $errors['shipping_email']   = 'Valid email required';
    if (!isValidPhone($data['shipping_phone']))     $errors['shipping_phone']   = 'Valid phone required (01XXXXXXXXX)';
    if (!$data['shipping_address'])                 $errors['shipping_address'] = 'Address is required';
    if (!$data['shipping_city'])                    $errors['shipping_city']    = 'City is required';
    if (!$data['shipping_state'])                   $errors['shipping_state']   = 'State/Division required';
    if (!$data['shipping_zip'])                     $errors['shipping_zip']     = 'Zip/Area code required';

    if (empty($errors)) {
        try {
            $db->beginTransaction();
            $orderNum = generateOrderNumber();
            $ins = $db->prepare("INSERT INTO orders (user_id, order_number, status, subtotal, shipping_cost, total, payment_method, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_zip, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ins->execute([
                $_SESSION['user_id'], $orderNum, 'pending',
                $subtotal, $shipping, $total, 'cod',
                $data['shipping_name'], $data['shipping_email'], $data['shipping_phone'],
                $data['shipping_address'], $data['shipping_city'], $data['shipping_state'], $data['shipping_zip'],
                trim($_POST['notes'] ?? '')
            ]);
            $orderId = $db->lastInsertId();

            // Insert items & update stock
            $insItem = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, size, color, price, quantity, subtotal) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($cartItems as $item) {
                $insItem->execute([$orderId, $item['product_id'], $item['name'], $item['image'], $item['size'], $item['color'], $item['unit_price'], $item['quantity'], $item['unit_price'] * $item['quantity']]);
                $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?")->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            }

            // Clear cart
            $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $db->commit();
            header('Location: ' . SITE_URL . '/order_success.php?order=' . urlencode($orderNum));
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $errors['general'] = 'Order failed. Please try again.';
        }
    }
}

$extraJs = '<script src="' . SITE_URL . '/assets/js/checkout.js"></script>';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Checkout</h1>
    </div>
</section>

<div class="container" style="padding:var(--gap-xl) 0;">
    <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
        <div class="cart-layout">
            <!-- Left: Shipping Form -->
            <div>
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xl);box-shadow:var(--shadow-card);margin-bottom:var(--gap-md);">
                    <h2 style="font-family:var(--font-heading);font-size:1.3rem;font-weight:700;margin-bottom:var(--gap-md);">
                        <i class="fas fa-map-marker-alt" style="color:var(--accent);margin-right:8px;"></i>Shipping Information
                    </h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name <span>*</span></label>
                            <input class="form-control <?= isset($errors['shipping_name']) ? 'is-invalid' : '' ?>"
                                   type="text" name="shipping_name" 
                                   value="<?= e($_POST['shipping_name'] ?? $user['name']) ?>" placeholder="Full name">
                            <?php if (isset($errors['shipping_name'])): ?><div class="form-error"><?= e($errors['shipping_name']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone <span>*</span></label>
                            <input class="form-control <?= isset($errors['shipping_phone']) ? 'is-invalid' : '' ?>"
                                   type="tel" name="shipping_phone" 
                                   value="<?= e($_POST['shipping_phone'] ?? $user['phone']) ?>" placeholder="01XXXXXXXXX">
                            <?php if (isset($errors['shipping_phone'])): ?><div class="form-error"><?= e($errors['shipping_phone']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span>*</span></label>
                        <input class="form-control <?= isset($errors['shipping_email']) ? 'is-invalid' : '' ?>"
                               type="email" name="shipping_email" 
                               value="<?= e($_POST['shipping_email'] ?? $user['email']) ?>" placeholder="email@example.com">
                        <?php if (isset($errors['shipping_email'])): ?><div class="form-error"><?= e($errors['shipping_email']) ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Address <span>*</span></label>
                        <textarea class="form-control <?= isset($errors['shipping_address']) ? 'is-invalid' : '' ?>"
                                  name="shipping_address" rows="2" placeholder="House, Road, Area..."><?= e($_POST['shipping_address'] ?? $user['address']) ?></textarea>
                        <?php if (isset($errors['shipping_address'])): ?><div class="form-error"><?= e($errors['shipping_address']) ?></div><?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City <span>*</span></label>
                            <input class="form-control" type="text" name="shipping_city" 
                                   value="<?= e($_POST['shipping_city'] ?? $user['city']) ?>" placeholder="e.g. Dhaka">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Division/State <span>*</span></label>
                            <select class="form-control" name="shipping_state">
                                <?php foreach (['Dhaka','Chittagong','Rajshahi','Khulna','Sylhet','Barisal','Rangpur','Mymensingh'] as $div): ?>
                                    <option <?= ($_POST['shipping_state'] ?? '') === $div ? 'selected' : '' ?>><?= $div ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code <span>*</span></label>
                            <input class="form-control" type="text" name="shipping_zip" 
                                   value="<?= e($_POST['shipping_zip'] ?? $user['zip']) ?>" placeholder="e.g. 1200">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Order Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Any special instructions..."><?= e($_POST['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Payment Method -->
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xl);box-shadow:var(--shadow-card);">
                    <h2 style="font-family:var(--font-heading);font-size:1.3rem;font-weight:700;margin-bottom:var(--gap-md);">
                        <i class="fas fa-credit-card" style="color:var(--accent);margin-right:8px;"></i>Payment Method
                    </h2>
                    <label style="display:flex;align-items:center;gap:12px;padding:14px;border:2px solid var(--primary);border-radius:var(--radius);cursor:pointer;background:rgba(255,126,182,0.05);">
                        <input type="radio" name="payment_method" value="cod" checked style="accent-color:var(--accent);">
                        <div>
                            <strong>Cash on Delivery</strong>
                            <p style="font-size:0.8rem;color:var(--text-muted);">Pay when your order arrives</p>
                        </div>
                        <i class="fas fa-money-bill-wave" style="margin-left:auto;color:var(--success);font-size:1.5rem;"></i>
                    </label>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="cart-summary">
                <h2 class="cart-summary-title">Your Order</h2>

                <?php foreach ($cartItems as $item): ?>
                    <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--bg-alt);font-size:0.85rem;">
                        <img src="<?= e(getProductImage($item['image'])) ?>" alt="" 
                             style="width:50px;height:60px;object-fit:cover;border-radius:var(--radius-sm);">
                        <div style="flex:1;">
                            <p style="font-weight:600;"><?= e(truncate($item['name'], 30)) ?></p>
                            <p style="color:var(--text-muted);">Qty: <?= $item['quantity'] ?></p>
                        </div>
                        <strong><?= formatPrice($item['unit_price'] * $item['quantity']) ?></strong>
                    </div>
                <?php endforeach; ?>

                <div class="summary-row" style="margin-top:10px;"><span>Subtotal</span><strong><?= formatPrice($subtotal) ?></strong></div>
                <div class="summary-row"><span>Shipping</span><span><?= $shipping === 0 ? '<span style="color:var(--success)">FREE</span>' : formatPrice($shipping) ?></span></div>
                <div class="summary-row summary-total"><span>Total</span><strong><?= formatPrice($total) ?></strong></div>

                <button type="submit" class="btn btn-primary w-100 btn-lg" style="margin-top:var(--gap-md);" id="placeOrderBtn">
                    <i class="fas fa-check-circle"></i> Place Order
                </button>

                <p style="text-align:center;font-size:0.75rem;color:var(--text-muted);margin-top:10px;">
                    <i class="fas fa-lock"></i> Secure & Encrypted
                </p>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
