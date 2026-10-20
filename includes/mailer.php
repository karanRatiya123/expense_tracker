<?php
/**
 * ApexSpend — Mailer helper
 *
 * Two ways to send:
 *   defer_notification_email($type, $data)  ← use this from web endpoints
 *   send_notification_email($type, $data)   ← sends inline (scripts, tests)
 *
 * Why defer? A Gmail SMTP round-trip costs several seconds. Deferring flushes
 * the HTTP response first, so the user's click returns instantly and the email
 * goes out in the shutdown phase of the same request.
 *
 * Safe by design:
 *   - never throws, never breaks the calling request
 *   - returns early (instantly) when SMTP is not configured
 *   - respects the per-user email_notifications preference
 *   - short SMTP timeout so a dead network cannot hang anything
 */

require_once __DIR__ . '/email_prefs.php';
require_once __DIR__ . '/email_templates.php';

$__apexAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($__apexAutoload)) {
    require_once $__apexAutoload;
}
unset($__apexAutoload);

// Guarantee an output buffer exists so we can flush a complete response with a
// correct Content-Length before the (slow) SMTP round-trip. Without this, PHP
// streams the body and the browser must wait for the connection to close.
if (PHP_SAPI !== 'cli' && ob_get_level() === 0) {
    ob_start();
}

/** Last error message, for diagnostics. */
function apex_mail_last_error(): string
{
    return $GLOBALS['__apex_mail_error'] ?? '';
}

function apex_mail_set_error(string $message): void
{
    $GLOBALS['__apex_mail_error'] = $message;
    error_log('ApexSpend Mail: ' . $message);
}

/**
 * Are real SMTP credentials present? Placeholders fail fast.
 */
function apex_mail_configured(): bool
{
    if (!defined('MAIL_ENABLED') || !MAIL_ENABLED) {
        return false;
    }

    $user = defined('MAIL_USERNAME') ? trim((string) MAIL_USERNAME) : '';
    $pass = defined('MAIL_PASSWORD') ? trim((string) MAIL_PASSWORD) : '';

    if ($user === '' || $pass === '') {
        return false;
    }

    $placeholderUser = stripos($user, 'your_email') !== false
        || stripos($user, 'your-email') !== false
        || stripos($user, 'example.com') !== false;
    $placeholderPass = $pass === 'abcdefghijklmnop' || stripos($pass, 'your_app_password') !== false;

    return !$placeholderUser && !$placeholderPass;
}

/** Map an event type to its template. */
function apex_build_email(string $type, array $data): ?array
{
    switch ($type) {
        case 'transaction_added':
            return email_transaction_added($data);
        case 'transaction_updated':
            return email_transaction_updated($data);
        case 'transaction_deleted':
            return email_transaction_deleted($data);
        case 'budget_saved':
            return email_budget_saved($data);
        case 'budget_deleted':
            return email_budget_deleted($data);
        case 'family_created':
            return email_family_created($data);
        case 'family_joined':
            return email_family_joined($data);
        case 'family_left':
            return email_family_left($data);
        case 'password_changed':
            return email_password_changed();
        case 'profile_updated':
            return email_profile_updated($data);
        case 'welcome':
            return email_welcome($data);
        default:
            return null;
    }
}

/**
 * Resolve the current recipient + preference. Returns null when there is
 * nothing to send (no session, feature off, user opted out, unknown type).
 *
 * @return array{email:string,name:string,user_id:int,type:string,data:array}|null
 */
function apex_mail_job(string $type, array $data): ?array
{
    if (!apex_mail_configured()) {
        return null;
    }

    if (!isset($_SESSION['user']['email']) || $_SESSION['user']['email'] === '') {
        return null;
    }

    if (!apex_build_email($type, $data)) {
        return null;
    }

    $userId = (int) ($_SESSION['user']['id'] ?? 0);

    // Preference comes with the session (set on login and by the toggle), so
    // sending an email never costs a database round-trip.
    if ((int) ($_SESSION['user']['email_notifications'] ?? 1) === 0) {
        return null;
    }

    return [
        'email'   => (string) $_SESSION['user']['email'],
        'name'    => (string) ($_SESSION['user']['name'] ?? ''),
        'user_id' => $userId,
        'type'    => $type,
        'data'    => $data,
    ];
}

/**
 * Hand the response to the browser and stop holding the session lock.
 */
function apex_release_client(): void
{
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
        return;
    }

    if (ob_get_level() > 0) {
        $size = ob_get_length();
        if ($size !== false && !headers_sent()) {
            header('Connection: close');
            header('Content-Length: ' . $size);
        }
        @ob_end_flush();
    }

    flush();
}

/**
 * Flush the finished response, release the session lock, and deliver every
 * queued job once PHP shuts down. Shared by all deferred senders.
 */
function apex_queue_job(array $job): void
{
    $GLOBALS['__apex_mail_queue'][] = $job;

    if (!empty($GLOBALS['__apex_mail_started'])) {
        return;
    }
    $GLOBALS['__apex_mail_started'] = true;

    // 1. Hand the finished response to the browser right now (inline flush is
    //    reliable; doing this from a shutdown callback is not).
    ignore_user_abort(true);
    apex_release_client();

    // 2. Free the session lock so other tabs/requests never wait on SMTP.
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    // 3. Whatever time SMTP needs happens after the user is served.
    register_shutdown_function(function () {
        foreach ($GLOBALS['__apex_mail_queue'] ?? [] as $job) {
            apex_deliver_mail($job);
        }
    });
}

/**
 * Queue a notification to go out after the HTTP response is flushed.
 *
 * Call this *after* your endpoint has echo'd its response (JSON or redirect):
 * it flushes the response immediately, then PHP sends the email once the
 * client is already done. The caller's own timeline is untouched.
 */
function defer_notification_email(string $type, array $data = []): void
{
    try {
        $job = apex_mail_job($type, $data);
    } catch (\Throwable $e) {
        apex_mail_set_error($e->getMessage());
        return;
    }

    if (!$job) {
        return;
    }

    apex_queue_job($job);
}

/**
 * Welcome email for a brand-new account.
 *
 * Signup happens before anyone is logged in, so the recipient is passed in
 * explicitly instead of coming from the session. The opt-out preference is
 * deliberately skipped: new accounts default to ON and this is transactional.
 */
function queue_welcome_email(string $name, string $email): void
{
    $email = trim($email);
    if ($email === '' || !apex_mail_configured()) {
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $name = trim($name);

    apex_queue_job([
        'email'   => $email,
        'name'    => $name,
        'user_id' => 0,
        'type'    => 'welcome',
        'data'    => ['name' => $name],
    ]);
}

/**
 * Send a notification inline (used by scripts/tests and by deferred jobs).
 */
function send_notification_email(string $type, array $data = []): bool
{
    try {
        $job = apex_mail_job($type, $data);
        if (!$job) {
            return false;
        }
        return apex_deliver_mail($job);
    } catch (\Throwable $e) {
        apex_mail_set_error($e->getMessage());
        return false;
    }
}

/**
 * Build + hand the message to PHPMailer. Never throws.
 */
function apex_deliver_mail(array $job): bool
{
    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        apex_mail_set_error('PHPMailer is not installed (run composer install).');
        return false;
    }

    $email = apex_build_email($job['type'], $job['data']);
    if (!$email) {
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host        = MAIL_HOST;
        $mail->SMTPAuth    = true;
        $mail->Username    = MAIL_USERNAME;
        $mail->Password    = MAIL_PASSWORD;
        $mail->SMTPSecure  = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port        = MAIL_PORT;
        $mail->Timeout     = defined('MAIL_TIMEOUT') ? MAIL_TIMEOUT : 10;
        $mail->SMTPAutoTLS = true;

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($job['email'], $job['name']);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $email['subject'];
        $mail->Body    = $email['body'];
        $mail->AltBody = trim(preg_replace(
            '/[ \t]+/',
            ' ',
            strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $email['body']))
        ));

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        $detail = $mail->ErrorInfo ?: $e->getMessage();
        apex_mail_set_error($detail !== '' ? $detail : 'Unknown mail error');
        return false;
    }
}
