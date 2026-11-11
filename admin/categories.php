<?php
/**
 * Admin Categories — CRUD
 * Suprova's Gallery
 */
$pageTitle = 'Categories';
$adminPage = 'categories';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();

// Delete
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['delete']]);
    setFlash('success', 'Category deleted'); header('Location: ' . SITE_URL . '/admin/categories.php'); exit;
}

// Add/Edit
$editCat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?"); $stmt->execute([(int)$_GET['edit']]); $editCat = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $slug = slugify($name);
    $id   = (int)($_POST['id'] ?? 0);
    if ($name) {
        if ($id) {
            $db->prepare("UPDATE categories SET name=?,slug=?,description=? WHERE id=?")->execute([$name,$slug,$desc,$id]);
            setFlash('success', 'Category updated!');
        } else {
            $db->prepare("INSERT INTO categories (name,slug,description) VALUES (?,?,?)")->execute([$name,$slug,$desc]);
            setFlash('success', 'Category added!');
        }
        header('Location: ' . SITE_URL . '/admin/categories.php'); exit;
    }
}

$stmt = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.sort_order");
$categories = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h1 class="admin-page-title">Categories</h1>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
    <div class="admin-table-wrap">
        <div class="table-header"><h3 class="table-title">All Categories</h3></div>
        <table class="admin-table">
            <thead><tr><th>#</th><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= $cat['id'] ?></td>
                        <td><strong><?= e($cat['name']) ?></strong><br><small style="color:var(--text-muted);">/<?= e($cat['slug']) ?></small></td>
                        <td><?= $cat['product_count'] ?></td>
                        <td><span class="badge <?= $cat['is_active'] ? 'badge-success' : 'badge-secondary' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="?edit=<?= $cat['id'] ?>" class="btn btn-primary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                                <button onclick="if(confirm('Delete this category?')) window.location.href='?delete=<?= $cat['id'] ?>'" class="btn btn-danger btn-sm btn-icon"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-table-wrap" style="padding:24px;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h3>
        <form method="POST">
            <?php if ($editCat): ?><input type="hidden" name="id" value="<?= $editCat['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label class="form-label">Name <span>*</span></label>
                <input class="form-control" type="text" name="name" value="<?= e($editCat['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= e($editCat['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100"><?= $editCat ? 'Update' : 'Add Category' ?></button>
            <?php if ($editCat): ?>
                <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-outline w-100" style="margin-top:8px;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>document.getElementById('sidebarToggle')?.addEventListener('click', () => document.getElementById('adminSidebar')?.classList.toggle('open'));</script>
</div></div></div></body></html>
