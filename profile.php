<?php
/**
 * User Profile Page — Suprova's Gallery
 */
$pageTitle = "My Profile — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();
$user = getCurrentUser();
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addr  = trim($_POST['address'] ?? '');
        $city  = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zip   = trim($_POST['zip'] ?? '');

        if (strlen($name) < 2) $errors['name'] = 'Name too short';
        if ($phone && !isValidPhone($phone)) $errors['phone'] = 'Invalid phone';

        if (empty($errors)) {
            $db->prepare("UPDATE users SET name=?,phone=?,address=?,city=?,state=?,zip=? WHERE id=?")
               ->execute([$name,$phone,$addr,$city,$state,$zip,$_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            $user = getCurrentUser();
            $success = 'Profile updated successfully! 🎉';
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current !== $user['password']) $errors['current'] = 'Current password incorrect';
        if (strlen($new) < 6) $errors['new'] = 'Min. 6 characters';
        if ($new !== $confirm) $errors['confirm'] = 'Passwords do not match';

        if (empty($errors)) {
            $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$new, $_SESSION['user_id']]);
            $success = 'Password changed successfully!';
        }
    }
}

$activeTab = $_GET['tab'] ?? 'profile';

$orderCount = (int)$db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?")->execute([$_SESSION['user_id']]) ? 
              $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?")->execute([$_SESSION['user_id']]) : 0;
$stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?"); $stmt->execute([$_SESSION['user_id']]); $orderCount = $stmt->fetchColumn();
$stmt2 = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?"); $stmt2->execute([$_SESSION['user_id']]); $wishCount = $stmt2->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1 class="page-hero-title">My Account</h1></div>
</section>

<div class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <h3 style="font-family:var(--font-heading);font-size:1.1rem;font-weight:700;"><?= e($user['name']) ?></h3>
                <p style="font-size:0.8rem;color:var(--text-muted);"><?= e($user['email']) ?></p>
                <div style="display:flex;justify-content:center;gap:var(--gap-md);margin:var(--gap) 0;padding:var(--gap) 0;border-top:1px solid var(--secondary);border-bottom:1px solid var(--secondary);">
                    <div style="text-align:center;">
                        <strong style="color:var(--accent);font-size:1.3rem;"><?= $orderCount ?></strong>
                        <p style="font-size:0.7rem;color:var(--text-muted);">Orders</p>
                    </div>
                    <div style="text-align:center;">
                        <strong style="color:var(--accent);font-size:1.3rem;"><?= $wishCount ?></strong>
                        <p style="font-size:0.7rem;color:var(--text-muted);">Wishlist</p>
                    </div>
                </div>
                <nav class="profile-nav">
                    <a href="?tab=profile" class="<?= $activeTab === 'profile' ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile Info</a>
                    <a href="?tab=password" class="<?= $activeTab === 'password' ? 'active' : '' ?>"><i class="fas fa-lock"></i> Change Password</a>
                    <a href="<?= SITE_URL ?>/orders.php"><i class="fas fa-box"></i> My Orders</a>
                    <a href="<?= SITE_URL ?>/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a>
                    <a href="<?= SITE_URL ?>/logout.php" style="color:var(--danger)!important;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </aside>

            <!-- Main Content -->
            <div>
                <?php if ($success): ?>
                    <div class="flash-message flash-success" style="margin-bottom:var(--gap);"><?= e($success) ?></div>
                <?php endif; ?>

                <?php if ($activeTab === 'profile'): ?>
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xl);box-shadow:var(--shadow-card);">
                    <h2 style="font-family:var(--font-heading);font-size:1.3rem;font-weight:700;margin-bottom:var(--gap-md);">Profile Information</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name <span>*</span></label>
                                <input class="form-control" type="text" name="name" value="<?= e($user['name']) ?>" required>
                                <?php if (isset($errors['name'])): ?><div class="form-error"><?= e($errors['name']) ?></div><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input class="form-control" type="tel" name="phone" value="<?= e($user['phone']) ?>" placeholder="01XXXXXXXXX">
                                <?php if (isset($errors['phone'])): ?><div class="form-error"><?= e($errors['phone']) ?></div><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" value="<?= e($user['email']) ?>" disabled style="opacity:0.6;">
                            <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Email cannot be changed.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Your address"><?= e($user['address']) ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input class="form-control" type="text" name="city" value="<?= e($user['city']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Division</label>
                                <select class="form-control" name="state">
                                    <?php foreach (['Dhaka','Chittagong','Rajshahi','Khulna','Sylhet','Barisal','Rangpur','Mymensingh'] as $d): ?>
                                        <option <?= $user['state'] === $d ? 'selected' : '' ?>><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Postal Code</label>
                                <input class="form-control" type="text" name="zip" value="<?= e($user['zip']) ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>

                <?php elseif ($activeTab === 'password'): ?>
                <div style="background:var(--white);border-radius:var(--radius-lg);padding:var(--gap-xl);box-shadow:var(--shadow-card);">
                    <h2 style="font-family:var(--font-heading);font-size:1.3rem;font-weight:700;margin-bottom:var(--gap-md);">Change Password</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label class="form-label">Current Password <span>*</span></label>
                            <input class="form-control <?= isset($errors['current']) ? 'is-invalid' : '' ?>" type="password" name="current_password" placeholder="Current password">
                            <?php if (isset($errors['current'])): ?><div class="form-error"><?= e($errors['current']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password <span>*</span></label>
                            <input class="form-control <?= isset($errors['new']) ? 'is-invalid' : '' ?>" type="password" name="new_password" placeholder="Min. 6 characters">
                            <?php if (isset($errors['new'])): ?><div class="form-error"><?= e($errors['new']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password <span>*</span></label>
                            <input class="form-control <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>" type="password" name="confirm_password" placeholder="Repeat new password">
                            <?php if (isset($errors['confirm'])): ?><div class="form-error"><?= e($errors['confirm']) ?></div><?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Update Password</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
