<?php
/**
 * Homepage — Suprova's Gallery
 */
$pageTitle = "Suprova's Gallery — Premium Women's Fashion";
$metaDesc  = "Discover exquisite sarees, lehengas, kurtis and more at Suprova's Gallery. Premium women's fashion boutique in Bangladesh.";

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Featured products
$featured = $db->query("
    SELECT p.*, c.name as cat_name, c.slug as cat_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 AND p.is_active = 1 
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// New arrivals
$newArrivals = $db->query("
    SELECT p.*, c.name as cat_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_new_arrival = 1 AND p.is_active = 1 
    ORDER BY p.created_at DESC LIMIT 4
")->fetchAll();

// Bestsellers
$bestsellers = $db->query("
    SELECT p.*, c.name as cat_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_bestseller = 1 AND p.is_active = 1 
    ORDER BY p.views DESC LIMIT 4
")->fetchAll();

// Wishlist IDs for logged-in user
$wishlistIds = [];
if (isLoggedIn()) {
    $ws = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->execute([$_SESSION['user_id']]);
    $wishlistIds = array_column($ws->fetchAll(), 'product_id');
}

$categories = getCategories();

require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION — New Editorial Design -->
<section class="hero2" id="hero">

    <!-- Animated Background Orbs -->
    <div class="hero2-orb orb-1"></div>
    <div class="hero2-orb orb-2"></div>
    <div class="hero2-orb orb-3"></div>

    <!-- Giant Watermark -->
    <div class="hero2-watermark" aria-hidden="true">FASHION</div>

    <!-- Scrolling Ticker -->
    <div class="hero2-ticker">
        <div class="ticker-track">
            <span>✦ New Collection 2026</span>
            <span>✦ Sarees &amp; Lehengas</span>
            <span>✦ Free Shipping Above ৳2000</span>
            <span>✦ Premium Quality</span>
            <span>✦ Kurtis &amp; Salwars</span>
            <span>✦ New Collection 2026</span>
            <span>✦ Sarees &amp; Lehengas</span>
            <span>✦ Free Shipping Above ৳2000</span>
            <span>✦ Premium Quality</span>
            <span>✦ Kurtis &amp; Salwars</span>
        </div>
    </div>

    <!-- 3-Column Body -->
    <div class="hero2-body">

        <!-- Left — Stats vertical bar -->
        <div class="hero2-stats-col">
            <div class="hero2-stat-item">
                <span class="h2-stat-num">500+</span>
                <span class="h2-stat-label">Products</span>
            </div>
            <div class="hero2-stat-divider"></div>
            <div class="hero2-stat-item">
                <span class="h2-stat-num">10K+</span>
                <span class="h2-stat-label">Customers</span>
            </div>
            <div class="hero2-stat-divider"></div>
            <div class="hero2-stat-item">
                <span class="h2-stat-num">100%</span>
                <span class="h2-stat-label">Authentic</span>
            </div>
        </div>

        <!-- Center — Main headline -->
        <div class="hero2-center">
            <div class="hero2-eyebrow">
                <span class="eyebrow-dot"></span>
                <span>Suprova's Gallery — Est. 2020</span>
                <span class="eyebrow-dot"></span>
            </div>

            <h1 class="hero2-title">
                <em class="h2t-serif">Where</em>
                <strong class="h2t-bold">Fashion</strong>
                <em class="h2t-serif">Meets</em>
                <strong class="h2t-bold accent-stroke">Elegance</strong>
            </h1>

            <p class="hero2-sub">
                Handpicked premium women's wear crafted<br>for the woman who knows her style.
            </p>

            <div class="hero2-cta">
                <a href="<?= SITE_URL ?>/shop.php" class="btn2-primary">
                    <span>Explore Collection</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= SITE_URL ?>/shop.php?filter=sale" class="btn2-ghost">
                    <i class="fas fa-tag"></i>
                    <span>Shop Sale</span>
                </a>
            </div>

            <!-- Category quick-link pills -->
            <div class="hero2-pills">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= SITE_URL ?>/shop.php?category=<?= e($cat['slug']) ?>" class="hero2-pill">
                        <?= e($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right — Decorative card + ring -->
        <div class="hero2-deco-col">
            <!-- Rotating text ring -->
            <div class="hero2-ring" aria-hidden="true">
                <svg viewBox="0 0 220 220" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <path id="ringPath" d="M110,20 a90,90 0 0,1 0,180 a90,90 0 0,1 0,-180"/>
                    </defs>
                    <text font-family="Poppins,sans-serif" font-size="12.5" fill="rgba(214,51,132,0.55)" letter-spacing="3">
                        <textPath href="#ringPath">PREMIUM FASHION ✦ WOMEN'S WEAR ✦ PREMIUM FASHION ✦ WOMEN'S WEAR ✦</textPath>
                    </text>
                </svg>
            </div>

            <!-- Card -->
            <div class="hero2-deco-card">
                <div class="deco-card-inner">
                    <div class="deco-card-icon">✦</div>
                    <p class="deco-card-label">New In</p>
                    <p class="deco-card-value">2026</p>
                    <p class="deco-card-desc">Bridal &amp; Festive<br>Collection</p>
                    <a href="<?= SITE_URL ?>/shop.php?filter=new_arrival" class="deco-card-link">Discover →</a>
                </div>
            </div>
        </div>

    </div><!-- /hero2-body -->

    <!-- Bottom wave -->
    <div class="hero2-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,45 C480,90 960,0 1440,45 L1440,90 L0,90 Z" fill="#FFF9FB"/>
        </svg>
    </div>

</section>

<!-- CATEGORIES -->
<section class="categories-section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Browse By</span>
            <h2 class="section-title">Shop by <span>Category</span></h2>
            <p class="section-subtitle">Find exactly what you're looking for in our curated collections</p>
        </div>

        <div class="categories-scroll">
            <?php
            $catIcons = ['sarees'=>'🥻','salwar-kameez'=>'👘','kurtis'=>'👗','lehengas'=>'✨','tops-blouses'=>'👚','dupattas-scarves'=>'🧣'];
            foreach ($categories as $cat): ?>
                <a href="<?= SITE_URL ?>/shop.php?category=<?= e($cat['slug']) ?>" class="category-card reveal">
                    <div class="category-icon-wrap">
                        <?= $catIcons[$cat['slug']] ?? '🛍️' ?>
                    </div>
                    <div class="category-name"><?= e($cat['name']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="featured-section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Curated For You</span>
            <h2 class="section-title">Featured <span>Products</span></h2>
            <p class="section-subtitle">Explore our handpicked bestsellers loved by thousands</p>
        </div>

        <div class="products-grid">
            <?php foreach ($featured as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:2.5rem;" class="reveal">
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-lg">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- PROMO BANNER -->
<section class="promo-banner reveal">
    <div class="container">
        <div class="promo-content">
            <div>
                <div class="promo-tag">✦ Exclusive Offer</div>
                <h2 class="promo-title">Up to 40% Off<br>New Arrivals!</h2>
                <p class="promo-subtitle">Limited-time sale — don't miss out on stunning designs at unbeatable prices.</p>
            </div>
            <a href="<?= SITE_URL ?>/shop.php?filter=sale" class="btn btn-lg" style="background:#fff;color:var(--accent);flex-shrink:0;margin-top:1rem;">
                Shop the Sale <i class="fas fa-tag"></i>
            </a>
        </div>
    </div>
</section>

<!-- NEW ARRIVALS -->
<section class="new-arrivals-section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Just In</span>
            <h2 class="section-title">New <span>Arrivals</span></h2>
            <p class="section-subtitle">Fresh styles added every week — be the first to wear the latest</p>
        </div>

        <div class="products-grid products-grid-4">
            <?php foreach ($newArrivals as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- BESTSELLERS -->
<section class="bestsellers-section">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Customer Favourites</span>
            <h2 class="section-title">Best <span>Sellers</span></h2>
        </div>

        <div class="products-grid products-grid-4">
            <?php foreach ($bestsellers as $product): ?>
                <?php include __DIR__ . '/includes/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section style="padding:var(--gap-xxl) 0;background:var(--bg-alt);">
    <div class="container">
        <div class="section-header reveal">
            <span class="section-tag">Why We're Different</span>
            <h2 class="section-title">The <span>Suprova's Gallery</span> Promise</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--gap-md);">
            <?php
            $features = [
                ['fas fa-gem','Premium Quality','Every piece is carefully curated for quality and craftsmanship'],
                ['fas fa-shipping-fast','Fast Delivery','Free shipping on orders above ৳2000, swift delivery'],
                ['fas fa-undo','Easy Returns','7-day hassle-free return policy — no questions asked'],
                ['fas fa-headset','24/7 Support','Dedicated customer support to assist you anytime'],
            ];
            foreach ($features as [$icon, $title, $desc]): ?>
                <div class="reveal" style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-lg);text-align:center;box-shadow:var(--shadow-card);transition:var(--transition);" 
                     onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='var(--shadow-lg)'" 
                     onmouseout="this.style.transform='';this.style.boxShadow='var(--shadow-card)'">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--secondary),var(--primary-light));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto var(--gap);font-size:1.5rem;color:var(--accent);">
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <h3 style="font-family:var(--font-heading);font-size:1.1rem;font-weight:700;margin-bottom:8px;"><?= $title ?></h3>
                    <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.6;"><?= $desc ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
// Reveal animation
const reveals = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); }
    });
}, { threshold: 0.12 });
reveals.forEach(r => observer.observe(r));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
