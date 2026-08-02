<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

csrf_validate();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$id = trim($_POST['id'] ?? '');
$title = trim($_POST['title'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$category = trim($_POST['category'] ?? '');
$method = trim($_POST['method'] ?? '');
$type = trim($_POST['type'] ?? 'expense');
$note = trim($_POST['note'] ?? '');
$dateInput = trim($_POST['date'] ?? '');

if ($id === '' || $title === '' || $amount <= 0 || $category === '' || !in_array($type, ['expense', 'income'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$timestamp = strtotime($dateInput);
if (!$timestamp) {
    echo json_encode(['success' => false, 'error' => 'Invalid date']);
    exit;
}
$date = date('Y-m-d H:i:s', $timestamp);

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$exists = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE id = ? AND user_id = ?');
$exists->execute([$id, $_SESSION['user']['id']]);
if ((int)$exists->fetchColumn() === 0) {
    echo json_encode(['success' => false, 'error' => 'Transaction not found']);
    exit;
}

$stmt = $pdo->prepare(
    'UPDATE transactions
     SET type = ?, title = ?, note = ?, amount = ?, category = ?, date = ?, method = ?
     WHERE id = ? AND user_id = ?'
);
$success = $stmt->execute([$type, $title, $note, $amount, $category, $date, $method, $id, $_SESSION['user']['id']]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update transaction']);
}
?>
