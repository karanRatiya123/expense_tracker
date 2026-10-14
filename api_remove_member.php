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

$familyId = trim($_POST['family_id'] ?? '');
$memberId = intval($_POST['user_id'] ?? 0);
if ($familyId === '' || $memberId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    // Only an admin of this family can remove.
    $me = $pdo->prepare("SELECT role FROM family_members WHERE family_id = ? AND user_id = ? AND status = 'active'");
    $me->execute([$familyId, $_SESSION['user']['id']]);
    $myRow = $me->fetch();
    if (!$myRow || $myRow['role'] !== 'admin') {
        echo json_encode(['success' => false, 'error' => 'Only the admin can remove members']);
        exit;
    }

    if ($memberId === (int) $_SESSION['user']['id']) {
        echo json_encode(['success' => false, 'error' => 'Use Leave to remove yourself']);
        exit;
    }

    $target = $pdo->prepare("SELECT id FROM family_members WHERE family_id = ? AND user_id = ? AND status = 'active'");
    $target->execute([$familyId, $memberId]);
    if (!$target->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Member not found']);
        exit;
    }

    $pdo->prepare('DELETE FROM family_members WHERE family_id = ? AND user_id = ?')->execute([$familyId, $memberId]);

    // Tidy up: remove the family itself once nobody is left.
    $left = $pdo->prepare('SELECT COUNT(*) FROM family_members WHERE family_id = ?');
    $left->execute([$familyId]);
    if ((int) $left->fetchColumn() === 0) {
        $pdo->prepare('DELETE FROM families WHERE id = ?')->execute([$familyId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Could not remove member, please retry']);
}
