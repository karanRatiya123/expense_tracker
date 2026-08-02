<?php
require_once 'config.php';
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
$stmt = $pdo->prepare('SELECT * FROM budgets WHERE user_id = ?');
$stmt->execute([$userId]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$items = [];
foreach ($budgets as $b) {
    $period = ['type' => $b['period_type']];
    if ($b['period_type'] === 'custom') {
        $period['startDate'] = $b['start_date'];
        $period['endDate'] = $b['end_date'];
    }
    $thresholds = [75, 90, 100];
    if (!empty($b['thresholds'])) {
        $decoded = json_decode($b['thresholds'], true);
        if (is_array($decoded)) {
            $thresholds = $decoded;
        }
    }
    $items[] = [
        'id' => $b['id'],
        'category' => $b['category'],
        'amount' => (float) $b['amount'],
        'period' => $period,
        'thresholds' => $thresholds,
        'createdAt' => $b['created_at']
    ];
}

echo json_encode(['success' => true, 'items' => $items]);
