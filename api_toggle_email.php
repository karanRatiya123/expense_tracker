<?php
/**
 * ApexSpend — Toggle email notifications ON/OFF for the signed-in user.
 * POST only + CSRF protected. Returns JSON.
 */
require_once 'config.php';
require_once 'includes/email_prefs.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

csrf_validate();

$userId = (int) $_SESSION['user']['id'];
$enabled = ((string) ($_POST['enabled'] ?? '0')) === '1';

// Keep the session in step so the mail path never needs a DB round-trip.
$_SESSION['user']['email_notifications'] = $enabled ? 1 : 0;

$pdo = getDBConnection();
if (!$pdo) {
    // Session-only mode (no MySQL) — the value above is the source of truth.
    echo json_encode(['success' => true, 'enabled' => $enabled]);
    exit;
}

if (set_email_notifications($pdo, $userId, $enabled)) {
    echo json_encode(['success' => true, 'enabled' => $enabled]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not update preference']);
}
