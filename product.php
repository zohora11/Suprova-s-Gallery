<?php
/**
 * Product Detail Page
 * Suprova's Gallery
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . SITE_URL . '/shop.php'); exit; }

$stmt = $db->prepare("SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: ' . SITE_URL . '/shop.php'); exit; }

// Increment view count
$db->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);

// Related products
$related = $db->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 ORDER BY RAND() LIMIT 4");
$related->execute([$product['category_id'], $id]);
$relatedProducts = $related->fetchAll();

// Wishlist
$wishlistIds = [];
$isWishlisted = false;
if (isLoggedIn()) {
    $ws = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->execute([$_SESSION['user_id']]);
    $wishlistIds = array_column($ws->fetchAll(), 'product_id');
    $isWishlisted = in_array($id, $wishlistIds);
}

$sizes   = safeJsonDecode($product['sizes'], []);
$colors  = safeJsonDecode($product['colors'], []);
$gallery = safeJsonDecode($product['gallery'], []);
$mainImg = getProductImage($product['image']);

$hasSale = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
$currentPrice = $hasSale ? $product['sale_price'] : $product['price'];
$discount = $hasSale ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;

$pageTitle = e($product['name']) . " — Suprova's Gallery";
$metaDesc  = e($product['short_description'] ?? $product['name']);
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:var(--gap-lg);">
    <!-- Breadcrumb -->
    <div class="breadcrumb" style="margin-bottom:var(--gap-md);">
        <a href="<?= SITE_URL ?>/">Home</a>
        <span>/</span>
        <a href="<?= SITE_URL ?>/shop.php">Shop</a>
        <span>/</span>
        <a href="<?= SITE_URL ?>/shop.php?category=<?= e($product['cat_slug']) ?>"><?= e($product['cat_name']) ?></a>
        <span>/</span>
        <span><?= e(truncate($product['name'], 40)) ?></span>
    </div>

    <div class="product-detail">
        <div class="product-detail-grid">

            <!-- GALLERY -->
            <div class="product-gallery">
                <div class="gallery-main" id="galleryMain">
                    <img src="<?= e($mainImg) ?>" alt="<?= e($product['name']) ?>" id="mainImage">
                </div>
                <?php if (!empty($gallery)): ?>
                <div class="gallery-thumbs">
                    <div class="gallery-thumb active" onclick="changeImage('<?= e($mainImg) ?>', this)">
                        <img src="<?= e($mainImg) ?>" alt="Main">
                    </div>
                    <?php foreach ($gallery as $gImg): ?>
                        <div class="gallery-thumb" onclick="changeImage('<?= e(UPLOAD_URL . $gImg) ?>', this)">
                            <img src="<?= e(UPLOAD_URL . $gImg) ?>" alt="Gallery">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- PRODUCT INFO -->
            <div class="product-info">
                <div class="product-category-tag" style="font-size:0.75rem;font-weight:700;color:var(--accent);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px;">
                    <?= e($product['cat_name']) ?>
                </div>

                <h1 class="product-title-detail"><?= e($product['name']) ?></h1>

                <!-- Pricing -->
                <div class="product-price-detail">
                    <span class="price-main"><?= formatPrice($currentPrice) ?></span>
                    <?php if ($hasSale): ?>
                        <span class="price-original" style="font-size:1.2rem;"><?= formatPrice($product['price']) ?></span>
                        <span class="price-discount"><?= $discount ?>% OFF</span>
                    <?php endif; ?>
                </div>

                <!-- Stock -->
                <p style="font-size:0.85rem;margin-bottom:var(--gap-md);">
                    <?php if ($product['stock'] > 0): ?>
                        <i class="fas fa-check-circle" style="color:var(--success);"></i>
                        <span style="color:var(--success);font-weight:600;">In Stock</span>
                        (<?= $product['stock'] ?> available)
                    <?php else: ?>
                        <i class="fas fa-times-circle" style="color:var(--danger);"></i>
                        <span style="color:var(--danger);font-weight:600;">Out of Stock</span>
                    <?php endif; ?>
                </p>

                <!-- Short Description -->
                <?php if ($product['short_description']): ?>
                    <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:var(--gap-md);line-height:1.7;">
                        <?= e($product['short_description']) ?>
                    </p>
                <?php endif; ?>

                <!-- Size Selector -->
                <?php if (!empty($sizes)): ?>
                <div class="size-selector">
                    <div class="selector-label">
                        <span>Size</span>
                        <a href="#" style="font-size:0.8rem;color:var(--accent);">Size Guide</a>
                    </div>
                    <div class="size-options">
                        <?php foreach ($sizes as $size): ?>
                            <button type="button" class="size-btn" onclick="selectSize(this, '<?= e($size) ?>')">
                                <?= e($size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Color Selector -->
                <?php if (!empty($colors)): ?>
                <div class="color-selector">
                    <div class="selector-label">
                        <span>Color: <span id="selectedColorLabel" style="color:var(--accent);">Select</span></span>
                    </div>
                    <div class="color-options">
                        <?php
                        $colorMap = [
                            'Red'=>'#e53e3e','Maroon'=>'#7b2222','Royal Blue'=>'#2b4bc4',
                            'Emerald Green'=>'#276749','Blush Pink'=>'#FFB6C1','Pink'=>'#FF7EB6',
                            'Lavender'=>'#c4b5f5','Mint'=>'#98d8c8','Mint Green'=>'#98d8c8',
                            'Peach'=>'#ffcba4','Purple'=>'#9b59b6','Teal'=>'#2d9f9f',
                            'Rose Gold'=>'#d4a091','Navy'=>'#2c3e72','Yellow'=>'#f6c90e',
                            'Orange'=>'#f97316','Aqua'=>'#14b8a6','White'=>'#f5f5f5',
                            'Sky Blue'=>'#87ceeb','Ivory'=>'#fffff0','Mustard'=>'#d4a017',
                            'Coral'=>'#ff7f7f','Charcoal'=>'#36454f','Gold'=>'#ffd700',
                            'Silver'=>'#c0c0c0','Magenta'=>'#ff00ff','Black'=>'#222',
                            'Nude'=>'#e8c9a0','Olive'=>'#6b7c3c','Blue Floral'=>'#87ceeb',
                            'Pink Floral'=>'#FFB6C1','Multicolor'=>'linear-gradient(135deg,red,blue,green)',
                            'Pastel'=>'linear-gradient(135deg,#FFB6C1,#B0E0E6)','Bold'=>'linear-gradient(135deg,#ff0000,#0000ff)',
                        ];
                        foreach ($colors as $color): $bg = $colorMap[$color] ?? '#ccc'; ?>
                            <div class="color-swatch" 
                                 style="background:<?= e($bg) ?>;"
                                 title="<?= e($color) ?>"
                                 onclick="selectColor(this, '<?= e($color) ?>')">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity -->
                <div style="margin-bottom:var(--gap-md);">
                    <div class="selector-label"><span>Quantity</span></div>
                    <div class="quantity-selector">
                        <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                        <input type="number" class="qty-input" id="productQty" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <?php if ($product['stock'] > 0): ?>
                <div class="product-actions-detail">
                    <button class="btn btn-primary btn-lg" onclick="addToCartDetail(<?= $product['id'] ?>)">
                        <i class="fas fa-shopping-bag"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline btn-lg wishlist-btn <?= $isWishlisted ? 'wishlisted' : '' ?>" 
                            data-id="<?= $product['id'] ?>"
                            onclick="toggleWishlist(<?= $product['id'] ?>, this)"
                            style="flex:0;">
                        <i class="fa<?= $isWishlisted ? 's' : 'r' ?> fa-heart"></i>
                    </button>
                </div>
                <a href="<?= SITE_URL ?>/checkout.php?buy_now=<?= $product['id'] ?>" class="btn btn-accent w-100 btn-lg" style="margin-bottom:var(--gap);">
                    <i class="fas fa-bolt"></i> Buy Now
                </a>
                <?php else: ?>
                    <button class="btn btn-primary btn-lg w-100" disabled>Out of Stock</button>
                <?php endif; ?>

                <!-- Product Meta -->
                <div style="border-top:1px solid var(--secondary);padding-top:var(--gap);margin-top:var(--gap);font-size:0.85rem;color:var(--text-muted);">
                    <p><strong>Category:</strong> <a href="<?= SITE_URL ?>/shop.php?category=<?= e($product['cat_slug']) ?>" style="color:var(--accent);"><?= e($product['cat_name']) ?></a></p>
                    <p style="margin-top:4px;"><strong>SKU:</strong> SG-<?= str_pad($product['id'], 5, '0', STR_PAD_LEFT) ?></p>
                </div>

                <!-- Trust Badges -->
                <div style="display:flex;gap:var(--gap);margin-top:var(--gap);flex-wrap:wrap;">
                    <?php $badges = [['fas fa-shield-alt','Authentic Product'],['fas fa-undo','Easy Return'],['fas fa-truck','Fast Delivery']]; foreach ($badges as [$ico,$lbl]): ?>
                        <div style="display:flex;align-items:center;gap:6px;font-size:0.78rem;color:var(--text-muted);">
                            <i class="<?= $ico ?>" style="color:var(--primary);"></i><?= $lbl ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Description -->
        <?php if ($product['description']): ?>
        <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xl);margin-top:var(--gap-xl);box-shadow:var(--shadow-card);">
            <h2 style="font-family:var(--font-heading);font-size:1.4rem;font-weight:700;margin-bottom:var(--gap);">Product Description</h2>
            <div style="color:var(--text-muted);line-height:1.8;font-size:0.9rem;"><?= nl2br(e($product['description'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div style="margin-top:var(--gap-xxl);">
            <div class="section-header">
                <span class="section-tag">You Might Like</span>
                <h2 class="section-title">Related <span>Products</span></h2>
            </div>
            <div class="products-grid products-grid-4">
                <?php foreach ($relatedProducts as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let selectedSize  = '';
let selectedColor = '';

function changeImage(src, el) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function selectSize(el, size) {
    selectedSize = size;
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
}

function selectColor(el, color) {
    selectedColor = color;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selectedColorLabel').textContent = color;
}

function changeQty(delta) {
    const input = document.getElementById('productQty');
    const max = parseInt(input.max) || 99;
    input.value = Math.max(1, Math.min(max, parseInt(input.value) + delta));
}

function addToCartDetail(productId) {
    const qty = parseInt(document.getElementById('productQty').value);
    addToCart(productId, qty, selectedSize, selectedColor);
}

function toggleWishlist(productId, btn) {
    fetch('<?= SITE_URL ?>/api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('success', d.message);
            btn.classList.toggle('wishlisted');
            btn.querySelector('i').className = btn.classList.contains('wishlisted') ? 'fas fa-heart' : 'far fa-heart';
        } else {
            if (d.redirect) window.location.href = d.redirect;
            else showToast('error', d.message);
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
