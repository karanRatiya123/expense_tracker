<?php
require_once 'config.php';
require_once __DIR__ . '/includes/categories.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$items = array_map(fn($r) => [
    'id' => $r['id'],
    'name' => $r['name'],
    'icon' => $r['icon'],
    'color' => $r['color'],
    'is_builtin' => (int) $r['is_builtin'],
    'position' => (int) $r['position'],
], apex_get_categories($pdo, $userId));

echo json_encode(['success' => true, 'items' => $items]);
