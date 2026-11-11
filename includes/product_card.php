<?php
/**
 * Reusable Product Card Component
 * Variable: $product (array), $wishlistIds (array)
 */
$isWishlisted = in_array($product['id'], $wishlistIds ?? []);
$hasSale = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$discount = $hasSale ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
$currentPrice = $hasSale ? $product['sale_price'] : $product['price'];
?>
<div class="product-card reveal" data-product-id="<?= $product['id'] ?>">
    <div class="product-card-img">
        <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>">
            <img src="<?= e(getProductImage($product['image'])) ?>" 
                 alt="<?= e($product['name']) ?>" 
                 loading="lazy">
        </a>

        <!-- Badges -->
        <div class="product-card-badges">
            <?php if ($hasSale): ?>
                <span class="badge-tag badge-sale">-<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if ($product['is_new_arrival']): ?>
                <span class="badge-tag badge-new">New</span>
            <?php endif; ?>
            <?php if ($product['is_bestseller']): ?>
                <span class="badge-tag badge-hot">Hot</span>
            <?php endif; ?>
            <?php if ($product['stock'] == 0): ?>
                <span class="badge-tag" style="background:#666;color:#fff;">Sold Out</span>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="product-card-actions">
            <button class="card-action-btn wishlist-btn <?= $isWishlisted ? 'wishlisted' : '' ?>" 
                    data-id="<?= $product['id'] ?>"
                    title="<?= $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                <i class="fa<?= $isWishlisted ? 's' : 'r' ?> fa-heart"></i>
            </button>
            <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>" 
               class="card-action-btn" title="Quick View">
                <i class="fas fa-eye"></i>
            </a>
        </div>
    </div>

    <div class="product-card-body">
        <div class="product-category"><?= e($product['cat_name'] ?? '') ?></div>
        <h3 class="product-name">
            <a href="<?= SITE_URL ?>/product.php?id=<?= $product['id'] ?>">
                <?= e($product['name']) ?>
            </a>
        </h3>
        <div class="product-pricing">
            <span class="price-current"><?= formatPrice($currentPrice) ?></span>
            <?php if ($hasSale): ?>
                <span class="price-original"><?= formatPrice($product['price']) ?></span>
                <span class="price-discount"><?= $discount ?>% off</span>
            <?php endif; ?>
        </div>
        <?php if ($product['stock'] > 0): ?>
            <button class="add-to-cart-btn" 
                    onclick="addToCart(<?= $product['id'] ?>, 1)">
                <i class="fas fa-shopping-bag"></i> Add to Cart
            </button>
        <?php else: ?>
            <button class="add-to-cart-btn" disabled style="opacity:0.5;cursor:not-allowed;">
                Out of Stock
            </button>
        <?php endif; ?>
    </div>
</div>
