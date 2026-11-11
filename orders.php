<?php
/**
 * My Orders Page — Suprova's Gallery
 */
$pageTitle = "My Orders — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1 class="page-hero-title">My Orders</h1></div>
</section>

<div class="container" style="padding:var(--gap-xl) 0;">
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fas fa-box-open"></i></div>
            <h2>No Orders Yet</h2>
            <p>You haven't placed any orders yet. Start shopping!</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Shop Now</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card reveal">
                <div class="order-card-header">
                    <div>
                        <div class="order-number"><?= e($order['order_number']) ?></div>
                        <div class="order-date"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></div>
                    </div>
                    <span class="order-status <?= e($order['status']) ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
                <div class="order-card-body">
                    <?php
                    $items = $db->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                    $items->execute([$order['id']]);
                    $orderItems = $items->fetchAll();
                    ?>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <?php foreach (array_slice($orderItems, 0, 4) as $i => $item): ?>
                            <div style="position:relative;">
                                <img src="<?= e(getProductImage($item['image'])) ?>" alt="<?= e($item['product_name']) ?>"
                                     style="width:60px;height:75px;object-fit:cover;border-radius:var(--radius-sm);"
                                     title="<?= e($item['product_name']) ?> (x<?= $item['quantity'] ?>)">
                                <?php if ($i === 3 && count($orderItems) > 4): ?>
                                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.9rem;">
                                        +<?= count($orderItems) - 4 ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin-top:10px;font-size:0.85rem;color:var(--text-muted);">
                        <?= $order['item_count'] ?> item<?= $order['item_count'] > 1 ? 's' : '' ?> &nbsp;|&nbsp; 
                        Shipped to: <strong><?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?></strong>
                    </p>
                </div>
                <div class="order-card-footer">
                    <div>
                        <span style="font-size:0.8rem;color:var(--text-muted);">Order Total</span>
                        <div style="font-size:1.2rem;font-weight:800;color:var(--accent);"><?= formatPrice($order['total']) ?></div>
                    </div>
                    <div style="display:flex;gap:var(--gap-sm);">
                        <a href="<?= SITE_URL ?>/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.reveal').forEach(el => {
    new IntersectionObserver(e => { if(e[0].isIntersecting) el.classList.add('visible'); }).observe(el);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
