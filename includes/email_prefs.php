<?php
/**
 * ApexSpend — Email notification preferences
 *
 * Lightweight helpers (no PHPMailer dependency) so pages like profile.php can
 * read/write the preference without booting the mailer.
 */

/**
 * Make sure users.email_notifications exists. Adds it on first run if missing.
 * Cached per request.
 */
function apex_email_pref_ready(PDO $pdo): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo->query('SELECT email_notifications FROM users LIMIT 1');
        return $ready = true;
    } catch (PDOException $e) {
        try {
            $pdo->exec('ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) NOT NULL DEFAULT 1 AFTER avatar_path');
            return $ready = true;
        } catch (PDOException $e2) {
            return $ready = false;
        }
    }
}

/**
 * Is email notification enabled for a user? Defaults to true when unknown.
 */
function email_notifications_enabled(PDO $pdo, int $userId): bool
{
    if ($userId <= 0 || !apex_email_pref_ready($pdo)) {
        return true;
    }

    try {
        $stmt = $pdo->prepare('SELECT email_notifications FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return !$row || (int) $row['email_notifications'] === 1;
    } catch (PDOException $e) {
        return true;
    }
}

/**
 * Persist the toggle. Returns true on success.
 */
function set_email_notifications(PDO $pdo, int $userId, bool $enabled): bool
{
    if ($userId <= 0 || !apex_email_pref_ready($pdo)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('UPDATE users SET email_notifications = ? WHERE id = ?');
        return $stmt->execute([$enabled ? 1 : 0, $userId]);
    } catch (PDOException $e) {
        return false;
    }
}
