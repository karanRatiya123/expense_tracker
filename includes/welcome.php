<?php
/**
 * ApexSpend — one-time welcome card helpers
 *
 * Lightweight (no PHPMailer dependency) so the dashboard can check the flag
 * without booting the mailer. Mirrors includes/email_prefs.php.
 */

/**
 * Make sure users.welcome_seen exists. Adds it on first run if missing.
 *
 * Accounts created before this feature are marked as seen in the same pass,
 * so only genuinely new signups ever see the onboarding card.
 * Cached per request.
 */
function apex_welcome_ready(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->query('SELECT welcome_seen FROM users LIMIT 1');
        return $ready = true;
    } catch (PDOException $e) {
        try {
            $pdo->exec('ALTER TABLE users ADD COLUMN welcome_seen TINYINT(1) NOT NULL DEFAULT 0');
            $pdo->exec('UPDATE users SET welcome_seen = 1');
            return $ready = true;
        } catch (PDOException $e2) {
            return $ready = false;
        }
    }
}

/** Should this user still see the welcome card? */
function apex_needs_welcome(PDO $pdo, int $userId): bool
{
    if ($userId <= 0 || !apex_welcome_ready($pdo)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('SELECT welcome_seen FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return !$row || (int) $row['welcome_seen'] === 0;
    } catch (PDOException $e) {
        return false;
    }
}

/** Persist the dismissal. Returns true on success. */
function apex_mark_welcome_seen(PDO $pdo, int $userId): bool
{
    if ($userId <= 0 || !apex_welcome_ready($pdo)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET welcome_seen = 1 WHERE id = ?');
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
