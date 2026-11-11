<?php
/**
 * Wishlist Page — Suprova's Gallery
 */
$pageTitle = "My Wishlist — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();
$stmt = $db->prepare("
    SELECT p.*, c.name as cat_name, w.id as wish_id 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    JOIN categories c ON p.category_id = c.id 
    WHERE w.user_id = ? 
    ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$wishlistItems = $stmt->fetchAll();

// All wishlist IDs (for product card component)
$wishlistIds = array_column($wishlistItems, 'id');

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1 class="page-hero-title">My Wishlist ❤️</h1></div>
</section>

<div class="wishlist-page">
    <div class="container">
        <?php if (empty($wishlistItems)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-heart-broken"></i></div>
                <h2>Your Wishlist is Empty</h2>
                <p>Save items you love to your wishlist and find them here.</p>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">Explore Products</a>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted);margin-bottom:var(--gap-md);"><?= count($wishlistItems) ?> item<?= count($wishlistItems) > 1 ? 's' : '' ?> saved</p>
            <div class="products-grid">
                <?php foreach ($wishlistItems as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
