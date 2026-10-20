<?php
require_once 'config.php';
require_once 'includes/mailer.php';
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

// Capture the category before deleting so the email can name it.
$info = $pdo->prepare('SELECT category, amount FROM budgets WHERE id = ? AND user_id = ?');
$info->execute([$id, $userId]);
$budgetData = $info->fetch();

$stmt = $pdo->prepare('DELETE FROM budgets WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);

echo json_encode(['success' => true]);

if ($budgetData && $stmt->rowCount() > 0) {
    defer_notification_email('budget_deleted', $budgetData);
}
