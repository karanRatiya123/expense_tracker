<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$id = trim($_POST['id'] ?? '');
if ($id === '') {
    echo json_encode(['success' => false, 'error' => 'Missing transaction id']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
$success = $stmt->execute([$id, $_SESSION['user']['id']]);

if ($success && $stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Transaction not found']);
}
?>
