<?php
/**
 * Admin Product Add/Edit Form — Suprova's Gallery
 */
$pageTitle = 'Product Form';
$adminPage = 'products';
require_once __DIR__ . '/includes/admin_header.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$product = null;
$errors  = [];

if ($id) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?"); $stmt->execute([$id]); $product = $stmt->fetch();
    if (!$product) { header('Location: ' . SITE_URL . '/admin/products.php'); exit; }
}

$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id'       => (int)$_POST['category_id'],
        'name'              => trim($_POST['name'] ?? ''),
        'description'       => trim($_POST['description'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'price'             => (float)$_POST['price'],
        'sale_price'        => $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null,
        'sizes'             => trim($_POST['sizes'] ?? ''),
        'colors'            => trim($_POST['colors'] ?? ''),
        'stock'             => (int)$_POST['stock'],
        'is_featured'       => isset($_POST['is_featured']) ? 1 : 0,
        'is_new_arrival'    => isset($_POST['is_new_arrival']) ? 1 : 0,
        'is_bestseller'     => isset($_POST['is_bestseller']) ? 1 : 0,
        'is_active'         => isset($_POST['is_active']) ? 1 : 0,
    ];

    // Parse sizes & colors to JSON
    $sizesArr  = array_filter(array_map('trim', explode(',', $data['sizes'])));
    $colorsArr = array_filter(array_map('trim', explode(',', $data['colors'])));
    $data['sizes']  = json_encode(array_values($sizesArr));
    $data['colors'] = json_encode(array_values($colorsArr));

    // Validation
    if (!$data['category_id'])        $errors['category'] = 'Category required';
    if (strlen($data['name']) < 2)    $errors['name']     = 'Name required';
    if ($data['price'] <= 0)          $errors['price']    = 'Valid price required';

    // Image upload
    $imageName = $product['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadProductImage($_FILES['image']);
        if ($uploaded) {
            // Delete old image
            if ($imageName) deleteProductImage($imageName);
            $imageName = $uploaded;
        } else {
            $errors['image'] = 'Invalid image. Use JPG, PNG, or WEBP. Max 5MB.';
        }
    }

    if (empty($errors)) {
        $slug = slugify($data['name']);
        // Ensure unique slug
        $chk = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?"); $chk->execute([$slug, $id ?: 0]);
        if ($chk->fetch()) $slug .= '-' . time();

        if ($id) {
            $db->prepare("UPDATE products SET category_id=?,name=?,slug=?,description=?,short_description=?,price=?,sale_price=?,image=?,sizes=?,colors=?,stock=?,is_featured=?,is_new_arrival=?,is_bestseller=?,is_active=? WHERE id=?")
               ->execute([$data['category_id'],$data['name'],$slug,$data['description'],$data['short_description'],$data['price'],$data['sale_price'],$imageName,$data['sizes'],$data['colors'],$data['stock'],$data['is_featured'],$data['is_new_arrival'],$data['is_bestseller'],$data['is_active'],$id]);
            setFlash('success', 'Product updated successfully! ✅');
        } else {
            $db->prepare("INSERT INTO products (category_id,name,slug,description,short_description,price,sale_price,image,sizes,colors,stock,is_featured,is_new_arrival,is_bestseller,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$data['category_id'],$data['name'],$slug,$data['description'],$data['short_description'],$data['price'],$data['sale_price'],$imageName,$data['sizes'],$data['colors'],$data['stock'],$data['is_featured'],$data['is_new_arrival'],$data['is_bestseller'],$data['is_active']]);
            setFlash('success', 'Product added successfully! 🎉');
        }
        header('Location: ' . SITE_URL . '/admin/products.php'); exit;
    }
}

$sizes  = safeJsonDecode($product['sizes']  ?? '', []);
$colors = safeJsonDecode($product['colors'] ?? '', []);
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $id ? 'Edit Product' : 'Add New Product' ?></h1>
        <p class="admin-page-subtitle">Fill in the product details below</p>
    </div>
    <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

<form method="POST" enctype="multipart/form-data" id="productForm">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
        <!-- Main Fields -->
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="admin-table-wrap" style="padding:24px;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:20px;">Basic Information</h3>
                
                <div class="form-group">
                    <label class="form-label">Product Name <span>*</span></label>
                    <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           type="text" name="name" value="<?= e($product['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="form-error"><?= e($errors['name']) ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Description</label>
                    <input class="form-control" type="text" name="short_description" 
                           value="<?= e($product['short_description'] ?? '') ?>" 
                           placeholder="One-line description for product cards" maxlength="500">
                </div>

                <div class="form-group">
                    <label class="form-label">Full Description</label>
                    <textarea class="form-control" name="description" rows="5" 
                              placeholder="Detailed product description..."><?= e($product['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span>*</span></label>
                        <select class="form-control" name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category'])): ?><div class="form-error"><?= e($errors['category']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity <span>*</span></label>
                        <input class="form-control" type="number" name="stock" 
                               value="<?= (int)($product['stock'] ?? 0) ?>" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (৳) <span>*</span></label>
                        <input class="form-control" type="number" name="price" step="0.01" 
                               value="<?= $product['price'] ?? '' ?>" min="0" required placeholder="0.00">
                        <?php if (isset($errors['price'])): ?><div class="form-error"><?= e($errors['price']) ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sale Price (৳)</label>
                        <input class="form-control" type="number" name="sale_price" step="0.01" 
                               value="<?= $product['sale_price'] ?? '' ?>" min="0" placeholder="Leave blank if no sale">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Available Sizes</label>
                        <input class="form-control" type="text" name="sizes" 
                               value="<?= e(implode(', ', $sizes)) ?>" 
                               placeholder="XS, S, M, L, XL, XXL (comma-separated)">
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Comma-separated. Use "Free Size" for one-size.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Available Colors</label>
                        <input class="form-control" type="text" name="colors" 
                               value="<?= e(implode(', ', $colors)) ?>" 
                               placeholder="Red, Blue, Pink (comma-separated)">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Image + Flags -->
        <div style="display:flex;flex-direction:column;gap:20px;">
            <!-- Image Upload -->
            <div class="admin-table-wrap" style="padding:24px;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Product Image</h3>
                
                <?php if ($product && $product['image']): ?>
                    <div style="margin-bottom:16px;text-align:center;">
                        <img src="<?= e(getProductImage($product['image'])) ?>" 
                             style="max-width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:2px solid var(--border);"
                             id="imagePreview">
                        <p style="font-size:0.75rem;color:var(--text-muted);margin-top:6px;">Current Image</p>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom:16px;text-align:center;display:none;" id="previewWrap">
                        <img id="imagePreview" style="max-width:100%;max-height:200px;object-fit:contain;border-radius:8px;border:2px solid var(--border);">
                    </div>
                <?php endif; ?>

                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <p><strong>Click to upload</strong> or drag & drop</p>
                    <p style="font-size:0.75rem;margin-top:4px;">JPG, JPEG, PNG, WEBP — Max 5MB</p>
                </div>
                <input type="file" name="image" id="imageInput" accept=".jpg,.jpeg,.png,.webp" 
                       style="display:none;" onchange="previewImage(this)">
                <?php if (isset($errors['image'])): ?><div class="form-error" style="margin-top:8px;"><?= e($errors['image']) ?></div><?php endif; ?>
            </div>

            <!-- Product Flags -->
            <div class="admin-table-wrap" style="padding:24px;">
                <h3 style="font-size:1rem;font-weight:700;margin-bottom:16px;">Labels & Status</h3>
                <?php
                $flags = [
                    'is_active'      => ['Active (Visible on store)', 1],
                    'is_featured'    => ['Featured Product', 0],
                    'is_new_arrival' => ['New Arrival', 0],
                    'is_bestseller'  => ['Bestseller', 0],
                ];
                foreach ($flags as $name => [$label, $default]): ?>
                    <label style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(0,0,0,0.04);cursor:pointer;">
                        <span style="font-size:0.875rem;font-weight:500;"><?= $label ?></span>
                        <input type="checkbox" name="<?= $name ?>" value="1" 
                               <?= ($product[$name] ?? $default) ? 'checked' : '' ?>
                               style="width:18px;height:18px;accent-color:var(--accent);">
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-100 btn-lg" style="border-radius:var(--radius);">
                <i class="fas fa-save"></i> <?= $id ? 'Update Product' : 'Save Product' ?>
            </button>
            <?php if ($id): ?>
                <a href="<?= SITE_URL ?>/product.php?id=<?= $id ?>" target="_blank" class="btn btn-outline w-100">
                    <i class="fas fa-external-link-alt"></i> View Product Page
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            const wrap = document.getElementById('previewWrap');
            if (preview) { preview.src = e.target.result; if (wrap) wrap.style.display = 'block'; }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Drag & drop
const zone = document.getElementById('uploadZone');
zone?.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
zone?.addEventListener('dragleave', () => zone.classList.remove('dragover'));
zone?.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('imageInput');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        previewImage(input);
    }
});

document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});
</script>
</div></div></div></body></html>
