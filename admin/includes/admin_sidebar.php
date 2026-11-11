<?php
/**
 * Admin Sidebar — Suprova's Gallery
 */
$currentFile = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['index.php',      'fas fa-tachometer-alt', 'Dashboard'],
    ['products.php',   'fas fa-tshirt',         'Products'],
    ['categories.php', 'fas fa-tags',           'Categories'],
    ['orders.php',     'fas fa-box',            'Orders'],
    ['users.php',      'fas fa-users',          'Users'],
];
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <span style="font-size:1.2rem;color:#D63384;">✦</span>
        <div style="font-family:'Playfair Display',serif;font-size:0.9rem;font-weight:700;line-height:1.2;">
            Suprova's<br><em style="color:#FF7EB6;">Gallery</em>
        </div>
    </div>
    <div class="sidebar-label">MENU</div>
    <nav class="sidebar-nav">
        <?php foreach ($navItems as [$file, $icon, $label]): ?>
            <a href="<?= SITE_URL ?>/admin/<?= $file ?>" 
               class="sidebar-item <?= $currentFile === $file ? 'active' : '' ?>">
                <i class="<?= $icon ?>"></i>
                <span><?= $label ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-label" style="margin-top:auto;">ACCOUNT</div>
    <a href="<?= SITE_URL ?>/" class="sidebar-item">
        <i class="fas fa-store"></i><span>Storefront</span>
    </a>
    <a href="<?= SITE_URL ?>/logout.php" class="sidebar-item" style="color:#ff6b6b;">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </a>
</aside>
