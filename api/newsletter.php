<?php
/**
 * Newsletter Subscribe API — Suprova's Gallery
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$email = trim($_POST['email'] ?? '');

if (!isValidEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid email address']);
}

$db = getDB();
$stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'You are already subscribed! 💌']);
}

$db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)")->execute([$email]);
jsonResponse(['success' => true, 'message' => 'Thank you for subscribing! 🎉 Exclusive deals coming your way.']);
