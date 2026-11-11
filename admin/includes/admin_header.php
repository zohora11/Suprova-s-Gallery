<?php
/**
 * Admin Header Include — Suprova's Gallery
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$adminPage = $adminPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? "Admin Panel") ?> — Suprova's Gallery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div style="flex:1;"></div>
            <div class="admin-topbar-actions">
                <a href="<?= SITE_URL ?>/" target="_blank" class="btn btn-sm btn-outline" style="font-size:0.8rem;">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <div class="admin-user">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#FF7EB6,#D63384);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                    <span style="font-size:0.875rem;font-weight:600;"><?= e($_SESSION['user_name']) ?></span>
                </div>
            </div>
        </header>

        <div class="admin-content">
        <!-- Flash -->
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="flash-message flash-<?= e($flash['type']) ?>" style="margin-bottom:1.5rem;">
                <?= e($flash['message']) ?>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>
