<?php
/**
 * ApexSpend — Dismiss the one-time welcome card.
 * POST only + CSRF protected. Returns JSON.
 */
require_once 'config.php';
require_once 'includes/welcome.php';

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
$pdo = getDBConnection();

if (!$pdo) {
    // Session-only mode (no MySQL) — the card is DB-gated, so there is
    // nothing to persist and nothing to show either.
    echo json_encode(['success' => true]);
    exit;
}

if (apex_mark_welcome_seen($pdo, $userId)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not dismiss welcome card']);
}
