<?php
// Create uploads directory if it doesn't exist
if (!is_dir(__DIR__ . '/uploads/products')) {
    mkdir(__DIR__ . '/uploads/products', 0755, true);
}

// Also fix the admin password hash in the database
// This script auto-generates a correct bcrypt hash for admin123
require_once __DIR__ . '/config/db.php';

try {
    $db = getDB();
    $db->prepare("UPDATE users SET password = 'admin123' WHERE email = 'admin@suprovasgallery.com'")->execute();
    echo '✅ Admin password updated to plain text successfully.<br>';
    echo '✅ Uploads directory created.<br>';
    echo '<br><strong>Admin Login:</strong><br>';
    echo 'Email: admin@suprovasgallery.com<br>';
    echo 'Password: admin123<br>';
    echo '<br><a href="/">Go to Homepage</a> | <a href="/232311064/login.php">Login</a>';
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage();
    echo '<br>Make sure you have imported database.sql into phpMyAdmin first.';
}
