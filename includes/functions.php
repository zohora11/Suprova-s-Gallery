<?php
/**
 * Global Helper Functions
 * Suprova's Gallery
 */

/**
 * Sanitize output to prevent XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format price with currency symbol
 */
function formatPrice(float $price): string {
    return CURRENCY . number_format($price, 2);
}

/**
 * Generate URL slug from string
 */
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Generate unique order number
 */
function generateOrderNumber(): string {
    return 'SG-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

/**
 * Get product image URL (with fallback placeholder)
 */
function getProductImage(string|null $image, string $alt = ''): string {
    if ($image && file_exists(UPLOAD_DIR . $image)) {
        return UPLOAD_URL . $image;
    }
    // Return gradient placeholder SVG as data URI
    return SITE_URL . '/assets/images/product-placeholder.svg';
}

/**
 * Truncate text to specified length
 */
function truncate(string $text, int $length = 100): string {
    if (strlen($text) <= $length) return $text;
    return rtrim(substr($text, 0, $length)) . '...';
}

/**
 * Get all active categories
 */
function getCategories(): array {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get badge class for order status
 */
function getStatusBadge(string $status): string {
    $classes = [
        'pending'    => 'badge-warning',
        'processing' => 'badge-info',
        'shipped'    => 'badge-primary',
        'delivered'  => 'badge-success',
        'cancelled'  => 'badge-danger',
    ];
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone (BD format)
 */
function isValidPhone(string $phone): bool {
    return preg_match('/^(?:\+88|88)?01[3-9]\d{8}$/', $phone);
}

/**
 * Upload product image
 * Returns filename on success or error string
 */
function uploadProductImage(array $file): string|false {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = uniqid('prod_') . '.' . $ext;
    $destination = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return false;
}

/**
 * Delete product image file
 */
function deleteProductImage(string $filename): void {
    $path = UPLOAD_DIR . $filename;
    if (file_exists($path)) unlink($path);
}

/**
 * Flash message system
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * JSON response helper (for API endpoints)
 */
function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Decode JSON safely
 */
function safeJsonDecode(string|null $json, $default = []): mixed {
    if (empty($json)) return $default;
    $decoded = json_decode($json, true);
    return $decoded ?? $default;
}

/**
 * Time ago helper
 */
function timeAgo(string $datetime): string {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hrs ago';
    if ($time < 604800) return floor($time/86400) . ' days ago';
    return date('M d, Y', strtotime($datetime));
}
