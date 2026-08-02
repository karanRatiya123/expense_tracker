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
$category = $_POST['category'] ?? '';
$amount = (float) ($_POST['amount'] ?? 0);
$periodType = $_POST['period_type'] ?? 'monthly';
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$thresholds = $_POST['thresholds'] ?? '[75,90,100]';

if (empty($id) || empty($category) || $amount <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

if ($startDate === '') $startDate = null;
if ($endDate === '') $endDate = null;

$stmt = $pdo->prepare('SELECT id FROM budgets WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$exists = $stmt->fetch();

if ($exists) {
    $stmt = $pdo->prepare('UPDATE budgets SET category = ?, amount = ?, period_type = ?, start_date = ?, end_date = ?, thresholds = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$category, $amount, $periodType, $startDate, $endDate, $thresholds, $id, $userId]);
} else {
    $stmt = $pdo->prepare('INSERT INTO budgets (id, user_id, category, amount, period_type, start_date, end_date, thresholds) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $userId, $category, $amount, $periodType, $startDate, $endDate, $thresholds]);
}

echo json_encode(['success' => true]);
