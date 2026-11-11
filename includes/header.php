<?php
/**
 * Header Include
 * Suprova's Gallery
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$cartCount     = getCartCount();
$wishlistCount = getWishlistCount();
$categories    = getCategories();
$flash         = getFlash();

// Page title fallback
$pageTitle = $pageTitle ?? "Suprova's Gallery";
$metaDesc  = $metaDesc  ?? "Premium women's fashion boutique — Sarees, Kurtis, Lehengas & more";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#FF7EB6">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">

    <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Announcement Bar -->
<div class="announcement-bar">
    <p>✨ Free Shipping on orders above ৳2000 &nbsp;|&nbsp; 🎁 New Arrivals Every Week &nbsp;|&nbsp; 💖 Exclusive Member Discounts</p>
</div>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/" class="nav-logo">
            <span class="logo-icon">✦</span>
            <span class="logo-text">Suprova's<br><em>Gallery</em></span>
        </a>

        <!-- Search Bar -->
        <div class="nav-search">
            <form action="<?= SITE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" id="searchInput" class="search-input" 
                       placeholder="Search sarees, kurtis, lehengas..." 
                       value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>

        <!-- Nav Actions -->
        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/profile.php" class="nav-icon" title="My Account">
                    <i class="fas fa-user-circle"></i>
                    <span class="nav-label">Hi, <?= e(explode(' ', $_SESSION['user_name'])[0]) ?></span>
                </a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="nav-icon" title="Login">
                    <i class="fas fa-user"></i>
                    <span class="nav-label">Login</span>
                </a>
            <?php endif; ?>

            <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon" title="Wishlist">
                <i class="fas fa-heart"></i>
                <?php if ($wishlistCount > 0): ?>
                    <span class="badge" id="wishlistBadge"><?= $wishlistCount ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon" title="Cart">
                <i class="fas fa-shopping-bag"></i>
                <span class="badge" id="cartBadge" <?= $cartCount === 0 ? 'style="display:none"' : '' ?>><?= $cartCount ?></span>
            </a>

            <?php if (isAdmin()): ?>
                <a href="<?= SITE_URL ?>/admin/" class="btn btn-accent btn-sm" title="Admin Panel">
                    <i class="fas fa-cog"></i>
                </a>
            <?php endif; ?>
        </div>

        <!-- Hamburger -->
        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    <!-- Main Nav Links -->
    <div class="nav-menu" id="navMenu">
        <div class="nav-menu-inner">
            <a href="<?= SITE_URL ?>/" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Home</a>
            <div class="nav-dropdown">
                <a href="<?= SITE_URL ?>/shop.php" class="nav-link">Shop <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <a href="<?= SITE_URL ?>/shop.php" class="dropdown-item">All Products</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= SITE_URL ?>/shop.php?category=<?= e($cat['slug']) ?>" class="dropdown-item">
                            <?= e($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?= SITE_URL ?>/shop.php?filter=new_arrival" class="nav-link">New Arrivals</a>
            <a href="<?= SITE_URL ?>/shop.php?filter=sale" class="nav-link">Sale</a>
            <a href="<?= SITE_URL ?>/contact.php" class="nav-link">Contact</a>
        </div>
    </div>
</nav>

<!-- Overlay for mobile menu -->
<div class="nav-overlay" id="navOverlay"></div>

<!-- Flash Message -->
<?php if ($flash): ?>
<div class="flash-message flash-<?= e($flash['type']) ?>" id="flashMessage">
    <span><?= e($flash['message']) ?></span>
    <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>

<main id="mainContent">
