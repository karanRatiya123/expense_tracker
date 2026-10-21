<?php
require_once 'config.php';
require_once __DIR__ . '/includes/categories.php';

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

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Category not found.']);
        exit;
    }
    if ((int) $row['is_builtin'] === 1) {
        echo json_encode(['success' => false, 'error' => 'Built-in categories cannot be deleted.']);
        exit;
    }

    // Guard: the user must keep at least one category.
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE user_id = ?');
    $cnt->execute([$userId]);
    if ((int) $cnt->fetchColumn() <= 1) {
        echo json_encode(['success' => false, 'error' => 'You need at least one category.']);
        exit;
    }

    $name = $row['name'];

    $pdo->beginTransaction();
    // Reassign history to "Other" — never orphan a transaction.
    $pdo->prepare('UPDATE transactions SET category = ? WHERE user_id = ? AND category = ?')
        ->execute(['Other', $userId, $name]);
    // Clean up budgets pointing at it.
    $pdo->prepare('DELETE FROM budgets WHERE user_id = ? AND category = ?')
        ->execute([$userId, $name]);
    $pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?')
        ->execute([$id, $userId]);
    $pdo->commit();

    echo json_encode(['success' => true, 'reassigned_to' => 'Other']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Could not delete category, please retry.']);
}
