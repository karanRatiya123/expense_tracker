<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

csrf_validate();

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM budgets WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);

echo json_encode(['success' => true]);
