<?php
/**
 * Shop Page — Product Listing with Filter & Search
 * Suprova's Gallery
 */
$pageTitle = "Shop — Suprova's Gallery";
$metaDesc  = "Browse our complete collection of premium women's fashion — sarees, kurtis, lehengas, and more.";

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// --- Filters ---
$search   = trim($_GET['q'] ?? '');
$catSlug  = trim($_GET['category'] ?? '');
$filter   = trim($_GET['filter'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';
$minPrice = (float)($_GET['min'] ?? 0);
$maxPrice = (float)($_GET['max'] ?? 50000);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

// Build WHERE conditions
$where  = ['p.is_active = 1'];
$params = [];

if ($search) {
    $where[] = '(p.name LIKE ? OR p.short_description LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}

if ($catSlug) {
    $where[] = 'c.slug = ?';
    $params[] = $catSlug;
}

if ($filter === 'new_arrival') { $where[] = 'p.is_new_arrival = 1'; }
if ($filter === 'featured')    { $where[] = 'p.is_featured = 1'; }
if ($filter === 'bestseller')  { $where[] = 'p.is_bestseller = 1'; }
if ($filter === 'sale')        { $where[] = 'p.sale_price IS NOT NULL'; }

if ($minPrice > 0) { $where[] = 'COALESCE(p.sale_price, p.price) >= ?'; $params[] = $minPrice; }
if ($maxPrice < 50000) { $where[] = 'COALESCE(p.sale_price, p.price) <= ?'; $params[] = $maxPrice; }

$whereStr = implode(' AND ', $where);

// Sort
$orderBy = match($sort) {
    'price_asc'  => 'COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'COALESCE(p.sale_price, p.price) DESC',
    'popular'    => 'p.views DESC',
    'oldest'     => 'p.created_at ASC',
    default      => 'p.created_at DESC',
};

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereStr");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Fetch products
$stmt = $db->prepare("
    SELECT p.*, c.name as cat_name, c.slug as cat_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE $whereStr 
    ORDER BY $orderBy 
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Wishlist IDs
$wishlistIds = [];
if (isLoggedIn()) {
    $ws = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->execute([$_SESSION['user_id']]);
    $wishlistIds = array_column($ws->fetchAll(), 'product_id');
}

// Active category
$activeCategory = null;
if ($catSlug) {
    $stmtCat = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmtCat->execute([$catSlug]);
    $activeCategory = $stmtCat->fetch();
    if ($activeCategory) $pageTitle = e($activeCategory['name']) . " — Suprova's Gallery";
}

// Price range for filter display
$priceRange = $db->query("SELECT MIN(COALESCE(sale_price,price)) as min_p, MAX(price) as max_p FROM products WHERE is_active=1")->fetch();

$extraJs = '<script src="' . SITE_URL . '/assets/js/shop.js"></script>';

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">
            <?php if ($activeCategory): ?>
                <?= e($activeCategory['name']) ?>
            <?php elseif ($search): ?>
                Search: "<?= e($search) ?>"
            <?php elseif ($filter): ?>
                <?= ucfirst(str_replace('_', ' ', $filter)) ?>
            <?php else: ?>
                All Products
            <?php endif; ?>
        </h1>
        <div class="breadcrumb" style="justify-content:center;margin-top:8px;">
            <a href="<?= SITE_URL ?>/">Home</a>
            <span>/</span>
            <span>Shop</span>
        </div>
    </div>
</section>

<div class="container">
    <div class="shop-layout">

        <!-- FILTER SIDEBAR -->
        <aside class="filter-sidebar">
            <form id="filterForm" method="GET">
                <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
                <h3 style="font-family:var(--font-heading);font-size:1.1rem;font-weight:700;margin-bottom:var(--gap-md);padding-bottom:var(--gap-sm);border-bottom:2px solid var(--secondary);">
                    <i class="fas fa-filter" style="color:var(--accent);margin-right:6px;"></i>Filters
                </h3>

                <!-- Categories -->
                <div class="filter-group">
                    <div class="filter-group-title">Categories</div>
                    <label class="filter-option">
                        <input type="radio" name="category" value="" <?= !$catSlug ? 'checked' : '' ?> onchange="this.form.submit()">
                        All Categories
                    </label>
                    <?php foreach ($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="category" value="<?= e($cat['slug']) ?>" 
                                   <?= $catSlug === $cat['slug'] ? 'checked' : '' ?>
                                   onchange="this.form.submit()">
                            <?= e($cat['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Price Range -->
                <div class="filter-group">
                    <div class="filter-group-title">Price Range</div>
                    <div class="price-range-wrap">
                        <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:8px;">
                            <span id="minDisplay">৳<?= number_format($minPrice) ?></span>
                            <span id="maxDisplay">৳<?= number_format($maxPrice) ?></span>
                        </div>
                        <input type="range" name="min" id="minRange" min="0" max="50000" step="100" 
                               value="<?= $minPrice ?>"
                               oninput="document.getElementById('minDisplay').textContent='৳'+Number(this.value).toLocaleString()">
                        <input type="range" name="max" id="maxRange" min="0" max="50000" step="100" 
                               value="<?= $maxPrice ?>"
                               oninput="document.getElementById('maxDisplay').textContent='৳'+Number(this.value).toLocaleString()">
                        <button type="submit" class="btn btn-primary w-100" style="margin-top:12px;padding:8px;">Apply</button>
                    </div>
                </div>

                <!-- Quick Filters -->
                <div class="filter-group">
                    <div class="filter-group-title">Quick Filters</div>
                    <?php
                    $quickFilters = ['new_arrival'=>'New Arrivals','bestseller'=>'Bestsellers','featured'=>'Featured','sale'=>'On Sale'];
                    foreach ($quickFilters as $val => $label): ?>
                        <label class="filter-option">
                            <input type="radio" name="filter" value="<?= $val ?>" 
                                   <?= $filter === $val ? 'checked' : '' ?>
                                   onchange="this.form.submit()">
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                    <label class="filter-option">
                        <input type="radio" name="filter" value="" <?= !$filter ? 'checked' : '' ?> onchange="this.form.submit()">
                        All
                    </label>
                </div>

                <!-- Clear Filters -->
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline w-100">Clear All Filters</a>
            </form>
        </aside>

        <!-- PRODUCT MAIN AREA -->
        <div class="shop-main">
            <!-- Toolbar -->
            <div class="shop-toolbar">
                <p style="font-size:0.875rem;color:var(--text-muted);">
                    Showing <strong><?= $totalProducts ?></strong> product<?= $totalProducts !== 1 ? 's' : '' ?>
                    <?php if ($search): ?> for "<strong><?= e($search) ?></strong>"<?php endif; ?>
                </p>
                <div style="display:flex;align-items:center;gap:var(--gap);">
                    <div class="view-toggle">
                        <button class="view-btn active" id="gridViewBtn" title="Grid view">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" id="listViewBtn" title="List view">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <form method="GET" id="sortForm">
                        <?php foreach ($_GET as $k => $v): if ($k !== 'sort' && $k !== 'page'): ?>
                            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                        <?php endif; endforeach; ?>
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest"     <?= $sort==='newest'     ?'selected':'' ?>>Newest First</option>
                            <option value="oldest"     <?= $sort==='oldest'     ?'selected':'' ?>>Oldest First</option>
                            <option value="price_asc"  <?= $sort==='price_asc'  ?'selected':'' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort==='price_desc' ?'selected':'' ?>>Price: High to Low</option>
                            <option value="popular"    <?= $sort==='popular'    ?'selected':'' ?>>Most Popular</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                    <h2>No Products Found</h2>
                    <p>Try adjusting your search or filters to find what you're looking for.</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/includes/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
