<?php
/**
 * Live Search API — Suprova's Gallery
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) jsonResponse(['results' => []]);

$db = getDB();
$stmt = $db->prepare("
    SELECT p.id, p.name, p.image, COALESCE(p.sale_price, p.price) as price, c.name as cat_name
    FROM products p 
    JOIN categories c ON p.category_id = c.id
    WHERE (p.name LIKE ? OR c.name LIKE ?) AND p.is_active = 1
    ORDER BY p.views DESC LIMIT 8
");
$stmt->execute(["%$q%", "%$q%"]);
$products = $stmt->fetchAll();

$results = array_map(function($p) {
    return [
        'id'       => $p['id'],
        'name'     => $p['name'],
        'price'    => '৳' . number_format($p['price'], 2),
        'category' => $p['cat_name'],
        'image'    => getProductImage($p['image']),
        'url'      => SITE_URL . '/product.php?id=' . $p['id'],
    ];
}, $products);

jsonResponse(['results' => $results]);
