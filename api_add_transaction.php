<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

csrf_validate();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $method = trim($_POST['method'] ?? '');
    $type = trim($_POST['type'] ?? 'expense');
    $note = trim($_POST['note'] ?? '');

    if (empty($title) || $amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $pdo = getDBConnection();
    if (!$pdo) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $txId = 'tx_' . uniqid();
    $date = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare('INSERT INTO transactions (id, user_id, type, title, note, amount, category, date, method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $success = $stmt->execute([$txId, $userId, $type, $title, $note, $amount, $category, $date, $method]);

    if ($success) {
        if ($type === 'expense') {
            $stmtCheck = $pdo->prepare('SELECT id FROM budgets WHERE user_id = ? AND category = ?');
            $stmtCheck->execute([$userId, $category]);
            if (!$stmtCheck->fetch()) {
                $bgId = 'bg_' . uniqid();
                $stmtBg = $pdo->prepare('INSERT INTO budgets (id, user_id, category, amount, period_type, thresholds) VALUES (?, ?, ?, ?, ?, ?)');
                $stmtBg->execute([$bgId, $userId, $category, 5000, 'monthly', '[75,90,100]']);
            }
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save transaction']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
