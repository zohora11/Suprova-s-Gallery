<?php
/**
 * Admin Dashboard — Suprova's Gallery
 */
$pageTitle  = 'Dashboard';
$adminPage  = 'dashboard';

require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Stats
$totalRevenue   = $db->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalOrders    = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalProducts  = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers     = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalMessages  = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

// Recent orders
$recentOrders = $db->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 10
")->fetchAll();

// Top products (by order frequency)
$topProducts = $db->query("
    SELECT p.name, p.image, SUM(oi.quantity) as sold, SUM(oi.subtotal) as revenue
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.status != 'cancelled'
    GROUP BY oi.product_id 
    ORDER BY sold DESC LIMIT 5
")->fetchAll();

// Monthly sales (last 6 months)
$monthlySales = $db->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') as month, SUM(total) as total
    FROM orders 
    WHERE status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY created_at ASC
")->fetchAll();

// Order status distribution
$statusCounts = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
$statusMap = [];
foreach ($statusCounts as $s) $statusMap[$s['status']] = $s['cnt'];

$stats = [
    ['Total Revenue', '৳' . number_format($totalRevenue ?? 0, 2), 'fas fa-bangladeshi-taka-sign', 'pink', '+12%'],
    ['Total Orders', number_format($totalOrders), 'fas fa-box', 'purple', '+8%'],
    ['Products', number_format($totalProducts), 'fas fa-tshirt', 'green', ''],
    ['Customers', number_format($totalUsers), 'fas fa-users', 'blue', '+5%'],
];
?>

<!-- Page Header -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-subtitle">Welcome back, <?= e($_SESSION['user_name']) ?>! Here's what's happening.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if ($totalMessages > 0): ?>
            <a href="#" class="btn btn-outline btn-sm">
                <i class="fas fa-envelope"></i> <?= $totalMessages ?> New Message<?= $totalMessages > 1 ? 's' : '' ?>
            </a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/admin/product_form.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <?php foreach ($stats as [$label, $value, $icon, $color, $trend]): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $color ?>"><i class="<?= $icon ?>"></i></div>
            <div class="stat-value"><?= $value ?></div>
            <div class="stat-label"><?= $label ?></div>
            <?php if ($trend): ?>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i><?= $trend ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;margin-bottom:28px;">
    <!-- Recent Orders -->
    <div class="admin-table-wrap">
        <div class="table-header">
            <h3 class="table-title">Recent Orders</h3>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><a href="<?= SITE_URL ?>/admin/orders.php?view=<?= $order['id'] ?>" style="color:var(--accent);font-weight:600;"><?= e($order['order_number']) ?></a></td>
                        <td><?= e($order['shipping_name']) ?></td>
                        <td style="font-weight:700;">৳<?= number_format($order['total'], 2) ?></td>
                        <td>
                            <?php
                            $cls = ['pending'=>'badge-warning','processing'=>'badge-info','shipped'=>'badge-pink','delivered'=>'badge-success','cancelled'=>'badge-danger'];
                            ?>
                            <span class="badge <?= $cls[$order['status']] ?? 'badge-secondary' ?>"><?= ucfirst($order['status']) ?></span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.8rem;"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Products -->
    <div class="admin-table-wrap">
        <div class="table-header">
            <h3 class="table-title">Top Sellers</h3>
        </div>
        <?php if (empty($topProducts)): ?>
            <div style="padding:40px;text-align:center;color:var(--text-muted);">
                <i class="fas fa-chart-bar" style="font-size:2rem;opacity:0.3;"></i>
                <p style="margin-top:12px;font-size:0.875rem;">No sales data yet</p>
            </div>
        <?php else: ?>
            <?php foreach ($topProducts as $p): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid rgba(0,0,0,0.04);">
                    <img src="<?= e(getProductImage($p['image'])) ?>" style="width:40px;height:50px;object-fit:cover;border-radius:4px;">
                    <div style="flex:1;min-width:0;">
                        <p style="font-weight:600;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($p['name']) ?></p>
                        <p style="font-size:0.75rem;color:var(--text-muted);"><?= $p['sold'] ?> sold</p>
                    </div>
                    <strong style="color:var(--accent);font-size:0.85rem;">৳<?= number_format($p['revenue'], 0) ?></strong>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Order Status Summary -->
<div class="admin-table-wrap" style="margin-bottom:28px;">
    <div class="table-header"><h3 class="table-title">Order Status Overview</h3></div>
    <div style="display:flex;gap:0;flex-wrap:wrap;">
        <?php
        $statuses = ['pending'=>['Pending','badge-warning'],'processing'=>['Processing','badge-info'],'shipped'=>['Shipped','badge-pink'],'delivered'=>['Delivered','badge-success'],'cancelled'=>['Cancelled','badge-danger']];
        foreach ($statuses as $key => [$label, $cls]): ?>
            <div style="flex:1;min-width:120px;text-align:center;padding:24px;border-right:1px solid rgba(0,0,0,0.04);">
                <div style="font-size:2rem;font-weight:800;font-family:'Playfair Display',serif;"><?= $statusMap[$key] ?? 0 ?></div>
                <span class="badge <?= $cls ?>" style="margin-top:8px;"><?= $label ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</div></div>
</div>
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});
</script>
</body></html>
