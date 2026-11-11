<?php
/**
 * Order Success Page — Suprova's Gallery
 */
$pageTitle = "Order Placed! — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$orderNum = trim($_GET['order'] ?? '');
if (!$orderNum) { header('Location: ' . SITE_URL . '/'); exit; }

$db = getDB();
$stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$orderNum, $_SESSION['user_id']]);
$order = $stmt->fetch();
if (!$order) { header('Location: ' . SITE_URL . '/'); exit; }

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding:var(--gap-xxl) 0;text-align:center;max-width:600px;">
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xxl);box-shadow:var(--shadow-lg);">
        <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto var(--gap-md);font-size:2rem;color:#fff;animation:badgePop 0.5s cubic-bezier(0.34,1.56,0.64,1);">
            <i class="fas fa-check"></i>
        </div>
        <h1 style="font-family:var(--font-heading);font-size:2rem;font-weight:700;margin-bottom:8px;color:var(--success);">
            Order Placed! 🎉
        </h1>
        <p style="color:var(--text-muted);font-size:1rem;margin-bottom:var(--gap-md);">
            Thank you for your purchase! Your order has been received and is being processed.
        </p>

        <div style="background:var(--bg-alt);border-radius:var(--radius);padding:var(--gap-md);margin-bottom:var(--gap-md);text-align:left;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--gap-sm);">
                <?php $details = ['Order Number'=>$order['order_number'],'Date'=>date('M d, Y',strtotime($order['created_at'])),'Total'=>formatPrice($order['total']),'Payment'=>'Cash on Delivery','Status'=>ucfirst($order['status'])]; ?>
                <?php foreach ($details as $k=>$v): ?>
                    <div>
                        <span style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;"><?= $k ?></span>
                        <p style="font-weight:700;font-size:0.9rem;margin-top:2px;"><?= $v ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:var(--gap-lg);">
            <i class="fas fa-envelope" style="color:var(--primary);"></i>
            A confirmation has been noted to <strong><?= e($order['shipping_email']) ?></strong>
        </p>

        <div style="display:flex;gap:var(--gap);justify-content:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/orders.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View My Orders
            </a>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
