<?php
/**
 * Admin Products Page — CRUD
 * Suprova's Gallery
 */
$pageTitle = 'Products';
$adminPage = 'products';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    $prod = $db->prepare("SELECT image FROM products WHERE id = ?"); $prod->execute([$pid]); $p = $prod->fetch();
    if ($p && $p['image']) deleteProductImage($p['image']);
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$pid]);
    setFlash('success', 'Product deleted successfully');
    header('Location: ' . SITE_URL . '/admin/products.php'); exit;
}

// Search & filter
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1']; $params = [];
if ($search) { $where[] = 'p.name LIKE ?'; $params[] = "%$search%"; }
if ($catFilter) { $where[] = 'p.category_id = ?'; $params[] = $catFilter; }
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr"); $total->execute($params);
$totalProducts = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = getCategories();
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Products</h1>
        <p class="admin-page-subtitle"><?= $totalProducts ?> products total</p>
    </div>
    <a href="<?= SITE_URL ?>/admin/product_form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<div class="admin-table-wrap">
    <div class="table-header">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
                <div class="admin-search">
                    <input type="text" name="q" placeholder="Search products..." value="<?= e($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                <select name="cat" class="form-control" style="width:auto;padding:8px 12px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $catFilter === $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($search || $catFilter): ?>
                    <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline btn-sm">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No products found</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <img src="<?= e(getProductImage($p['image'])) ?>" class="product-img-preview" alt="<?= e($p['name']) ?>">
                        </td>
                        <td>
                            <strong style="font-size:0.9rem;"><?= e($p['name']) ?></strong>
                            <br><small style="color:var(--text-muted);">SKU: SG-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></small>
                        </td>
                        <td><?= e($p['cat_name']) ?></td>
                        <td>
                            <?php if ($p['sale_price']): ?>
                                <span style="font-weight:700;color:var(--accent);">৳<?= number_format($p['sale_price'], 2) ?></span><br>
                                <small style="text-decoration:line-through;color:var(--text-muted);">৳<?= number_format($p['price'], 2) ?></small>
                            <?php else: ?>
                                <span style="font-weight:700;">৳<?= number_format($p['price'], 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $p['stock'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                <?= $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Out of stock' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="<?= SITE_URL ?>/product.php?id=<?= $p['id'] ?>" target="_blank" 
                                   class="btn btn-outline btn-sm btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= SITE_URL ?>/admin/product_form.php?id=<?= $p['id'] ?>" 
                                   class="btn btn-primary btn-sm btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')"
                                        class="btn btn-danger btn-sm btn-icon" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:8px;padding:20px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$i])) ?>"
                   style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:0.875rem;font-weight:600;
                          <?= $i === $page ? 'background:var(--accent);color:#fff;' : 'border:1px solid var(--border);' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"? This cannot be undone.`)) {
        window.location.href = '<?= SITE_URL ?>/admin/products.php?delete=' + id;
    }
}
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});
</script>
</div></div></div></body></html>
