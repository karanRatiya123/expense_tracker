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

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$userId = $_SESSION['user']['id'];
$familyId = trim($_POST['family_id'] ?? '');

try {
    if ($familyId === '') {
        // Default: most recent active membership.
        $cur = $pdo->prepare('SELECT family_id FROM family_members WHERE user_id = ? AND status = ? ORDER BY joined_at DESC LIMIT 1');
        $cur->execute([$userId, 'active']);
        $row = $cur->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'No family to leave']);
            exit;
        }
        $familyId = $row['family_id'];
    }

    // Capture the family name before the row disappears.
    $famRow = $pdo->prepare('SELECT name FROM families WHERE id = ?');
    $famRow->execute([$familyId]);
    $familyName = $famRow->fetchColumn() ?: null;

    $del = $pdo->prepare('DELETE FROM family_members WHERE family_id = ? AND user_id = ?');
    $del->execute([$familyId, $userId]);

    // Tidy up: remove the family itself once nobody is left.
    $left = $pdo->prepare('SELECT COUNT(*) FROM family_members WHERE family_id = ?');
    $left->execute([$familyId]);
    if ((int) $left->fetchColumn() === 0) {
        $pdo->prepare('DELETE FROM families WHERE id = ?')->execute([$familyId]);
    }

    echo json_encode(['success' => true]);

    if ($del->rowCount() > 0) {
        defer_notification_email('family_left', ['name' => $familyName ?: 'Your Family']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Could not leave family, please retry']);
}
