<?php
/**
 * Admin Users — Suprova's Gallery
 */
$pageTitle = 'Users';
$adminPage = 'users';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Toggle active status
if (isset($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'admin'")->execute([$uid]);
    header('Location: ' . SITE_URL . '/admin/users.php'); exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$search = trim($_GET['q'] ?? '');
$where = ["role = 'customer'"]; $params = [];
if ($search) { $where[] = '(name LIKE ? OR email LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereStr = implode(' AND ', $where);

$total = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereStr"); $total->execute($params);
$totalUsers = (int)$total->fetchColumn();
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT u.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent FROM users u LEFT JOIN orders o ON u.id = o.user_id WHERE $whereStr GROUP BY u.id ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <div><h1 class="admin-page-title">Customers</h1><p class="admin-page-subtitle"><?= $totalUsers ?> customers</p></div>
    <form method="GET"><div class="admin-search"><input type="text" name="q" placeholder="Search by name or email..." value="<?= e($search) ?>"><button type="submit"><i class="fas fa-search"></i></button></div></form>
</div>

<div class="admin-table-wrap">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr><th>Customer</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Status</th><th>Joined</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No customers found</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;background:linear-gradient(135deg,#FF7EB6,#D63384);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.875rem;flex-shrink:0;">
                                    <?= strtoupper(substr($u['name'],0,1)) ?>
                                </div>
                                <div>
                                    <strong><?= e($u['name']) ?></strong><br>
                                    <small style="color:var(--text-muted);"><?= e($u['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= e($u['phone'] ?: '—') ?></td>
                        <td><?= (int)$u['order_count'] ?></td>
                        <td style="font-weight:700;color:var(--accent);">৳<?= number_format($u['total_spent'] ?? 0, 2) ?></td>
                        <td><span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-danger' ?>"><?= $u['is_active'] ? 'Active' : 'Suspended' ?></span></td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="?toggle=<?= $u['id'] ?>" class="btn btn-sm <?= $u['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                               onclick="return confirm('<?= $u['is_active'] ? 'Suspend' : 'Activate' ?> this user?')">
                                <?= $u['is_active'] ? 'Suspend' : 'Activate' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>document.getElementById('sidebarToggle')?.addEventListener('click', () => document.getElementById('adminSidebar')?.classList.toggle('open'));</script>
</div></div></div></body></html>
