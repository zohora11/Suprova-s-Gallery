<?php
/**
 * Admin Orders Management — Suprova's Gallery
 */
$pageTitle = 'Orders';
$adminPage = 'orders';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$status, $oid]);
        setFlash('success', 'Order status updated to ' . ucfirst($status));
    }
    header('Location: ' . SITE_URL . '/admin/orders.php'); exit;
}

$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1']; $params = [];
if ($statusFilter) { $where[] = 'o.status = ?'; $params[] = $statusFilter; }
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM orders o WHERE $whereStr"); $total->execute($params);
$totalOrders = (int)$total->fetchColumn();
$totalPages  = max(1, ceil($totalOrders / $perPage));
$offset      = ($page - 1) * $perPage;

$stmt = $db->prepare("
    SELECT o.*, u.name as user_name, COUNT(oi.id) as item_count
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE $whereStr 
    GROUP BY o.id 
    ORDER BY o.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();
$statuses = ['pending','processing','shipped','delivered','cancelled'];
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Orders</h1>
        <p class="admin-page-subtitle"><?= $totalOrders ?> orders total</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="<?= SITE_URL ?>/admin/orders.php" 
       class="badge <?= !$statusFilter ? 'badge-pink' : 'badge-secondary' ?>" 
       style="font-size:0.85rem;padding:8px 16px;cursor:pointer;">All</a>
    <?php
    $cls = ['pending'=>'badge-warning','processing'=>'badge-info','shipped'=>'badge-pink','delivered'=>'badge-success','cancelled'=>'badge-danger'];
    foreach ($statuses as $s): ?>
        <a href="?status=<?= $s ?>" class="badge <?= $statusFilter === $s ? $cls[$s] : 'badge-secondary' ?>" 
           style="font-size:0.85rem;padding:8px 16px;cursor:pointer;"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="admin-table-wrap">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);">No orders found</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong style="color:var(--accent);"><?= e($order['order_number']) ?></strong></td>
                        <td>
                            <strong><?= e($order['shipping_name']) ?></strong><br>
                            <small style="color:var(--text-muted);"><?= e($order['shipping_email']) ?></small>
                        </td>
                        <td><?= $order['item_count'] ?> item<?= $order['item_count'] > 1 ? 's' : '' ?></td>
                        <td style="font-weight:700;">৳<?= number_format($order['total'], 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status" onchange="this.form.submit()" 
                                        class="form-control" style="padding:4px 8px;width:auto;font-size:0.8rem;border-radius:999px;">
                                    <?php foreach ($statuses as $s): ?>
                                        <option <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                            <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="?view=<?= $order['id'] ?>&<?= http_build_query(array_diff_key($_GET, ['view'=>''])) ?>" 
                               class="btn btn-outline btn-sm btn-icon" title="View Detail"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php if (isset($_GET['view']) && (int)$_GET['view'] === $order['id']): ?>
                        <?php
                        $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?"); $items->execute([$order['id']]); $orderItems = $items->fetchAll();
                        ?>
                        <tr>
                            <td colspan="8">
                                <div style="background:#fafafa;border-radius:8px;padding:20px;margin:8px 0;">
                                    <h4 style="font-weight:700;margin-bottom:12px;">Order Items</h4>
                                    <table style="width:100%;font-size:0.85rem;border-collapse:collapse;">
                                        <thead><tr style="border-bottom:2px solid var(--border);">
                                            <th style="padding:6px;text-align:left;">Product</th>
                                            <th style="padding:6px;">Size</th>
                                            <th style="padding:6px;">Color</th>
                                            <th style="padding:6px;">Qty</th>
                                            <th style="padding:6px;text-align:right;">Subtotal</th>
                                        </tr></thead>
                                        <tbody>
                                            <?php foreach ($orderItems as $item): ?>
                                                <tr style="border-bottom:1px solid var(--border);">
                                                    <td style="padding:8px 6px;font-weight:600;"><?= e($item['product_name']) ?></td>
                                                    <td style="padding:8px 6px;text-align:center;"><?= e($item['size'] ?: '—') ?></td>
                                                    <td style="padding:8px 6px;text-align:center;"><?= e($item['color'] ?: '—') ?></td>
                                                    <td style="padding:8px 6px;text-align:center;"><?= $item['quantity'] ?></td>
                                                    <td style="padding:8px 6px;text-align:right;font-weight:700;color:var(--accent);">৳<?= number_format($item['subtotal'],2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                                        <div>
                                            <strong style="font-size:0.8rem;">Ship to:</strong>
                                            <p style="font-size:0.875rem;margin-top:4px;"><?= e($order['shipping_address']) ?>, <?= e($order['shipping_city']) ?>, <?= e($order['shipping_state']) ?> - <?= e($order['shipping_zip']) ?></p>
                                            <p style="font-size:0.875rem;"><?= e($order['shipping_phone']) ?></p>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:0.875rem;color:var(--text-muted);">Subtotal: ৳<?= number_format($order['subtotal'],2) ?></div>
                                            <div style="font-size:0.875rem;color:var(--text-muted);">Shipping: ৳<?= number_format($order['shipping_cost'],2) ?></div>
                                            <div style="font-size:1.1rem;font-weight:800;color:var(--accent);">Total: ৳<?= number_format($order['total'],2) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});
</script>
</div></div></div></body></html>
