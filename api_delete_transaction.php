<?php
require_once 'config.php';
require_once 'includes/mailer.php';

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
if ($id === '') {
    echo json_encode(['success' => false, 'error' => 'Missing transaction id']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Fetch details first — the row is gone after the DELETE, but the email needs them.
$detail = $pdo->prepare('SELECT title, amount, category, type FROM transactions WHERE id = ? AND user_id = ?');
$detail->execute([$id, $_SESSION['user']['id']]);
$txData = $detail->fetch();

$stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
$success = $stmt->execute([$id, $_SESSION['user']['id']]);

if ($success && $stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);

    if ($txData) {
        defer_notification_email('transaction_deleted', $txData);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Transaction not found']);
}
?>
