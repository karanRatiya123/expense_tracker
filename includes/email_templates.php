<?php
/**
 * ApexSpend — HTML email templates
 *
 * Every template returns ['subject' => string, 'body' => string].
 * Styling is inline + table-based for Gmail / Outlook compatibility.
 */

/** Escape a value for HTML output. */
function email_esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Keep subjects single-line (guards against header injection). */
function email_subject_text($value): string
{
    $text = str_replace(["\r", "\n", '%0a', '%0d'], ' ', (string) $value);
    return mb_substr(trim($text), 0, 180);
}

/** ₹12,34,567.50 — Indian digit grouping. */
function email_format_inr($amount): string
{
    $amount = (float) $amount;
    $negative = $amount < 0;
    $parts = explode('.', number_format(abs($amount), 2, '.', ''));
    $whole = $parts[0];
    $decimal = $parts[1] ?? '00';

    if (strlen($whole) > 3) {
        $last3 = substr($whole, -3);
        $rest = substr($whole, 0, -3);
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $whole = $rest . ',' . $last3;
    }

    return ($negative ? '-' : '') . '₹' . $whole . '.' . $decimal;
}

/**
 * Base layout. $bodyContent is trusted HTML built by this file.
 */
function email_wrap(string $title, string $bodyContent, string $preheader = ''): string
{
    $safeTitle = email_esc($title);
    $safePre = email_esc($preheader !== '' ? $preheader : $title);
    $stamp = date('d M Y, h:i A');

    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>' . $safeTitle . '</title>
</head>
<body style="margin:0; padding:0; background-color:#0a0a0a; font-family:Arial, Helvetica, sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">' . $safePre . '</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0a0a0a; padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%; max-width:600px; background-color:#141414; border-radius:12px; overflow:hidden; border:1px solid #262626;">

                    <tr>
                        <td style="padding:28px 36px 20px; border-bottom:1px solid #262626;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <span style="font-size:22px; font-weight:700; color:#f5f5f5; letter-spacing:-0.5px;">Apex</span><span style="font-size:22px; font-weight:700; color:#f59e0b; letter-spacing:-0.5px;">Spend</span>
                                    </td>
                                    <td align="right">
                                        <span style="font-size:12px; color:#6b6b6b;">' . $stamp . '</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 36px;">
                            ' . $bodyContent . '
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 36px 28px; border-top:1px solid #262626;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#5c5c5c; text-align:center;">
                                This is an automatic notification from ApexSpend.<br>
                                Turn these off any time in <span style="color:#f59e0b;">Profile &rarr; Preferences</span>.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

/** Centred amount hero card. */
function email_amount_card(string $label, string $amount, string $color, string $sub = ''): string
{
    return '
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; margin:0 0 24px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">' . email_esc($label) . '</p>
                    <p style="margin:0; font-size:34px; line-height:1.2; font-weight:700; color:' . $color . '; word-break:break-word;">' . email_esc($amount) . '</p>'
                    . ($sub !== '' ? '<p style="margin:8px 0 0; font-size:13px; color:#8a8a8a;">' . $sub . '</p>' : '') . '
                </td>
            </tr>
        </table>';
}

/**
 * Two-column key/value table.
 * $rows: [ ['label' => 'Title', 'value' => '...', 'html' => '<span>..</span>', 'last' => true], ... ]
 */
function email_details_table(array $rows): string
{
    $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 4px;">';
    $count = count($rows);
    foreach ($rows as $i => $row) {
        $isLast = $i === $count - 1;
        $border = $isLast ? '' : 'border-bottom:1px solid #1f1f1f;';
        $value = isset($row['html']) ? $row['html'] : email_esc($row['value'] ?? '—');
        $html .= '
            <tr>
                <td style="padding:12px 0; ' . $border . '"><span style="font-size:13px; color:#8a8a8a;">' . email_esc($row['label']) . '</span></td>
                <td style="padding:12px 0; ' . $border . ' text-align:right;"><span style="font-size:14px; color:#f5f5f5;">' . $value . '</span></td>
            </tr>';
    }
    return $html . '</table>';
}

/** Small coloured pill. */
function email_pill(string $text, string $color, string $bg): string
{
    return '<span style="display:inline-block; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:700; letter-spacing:0.5px; background-color:' . $bg . '; color:' . $color . ';">' . email_esc($text) . '</span>';
}

/* -------------------------------------------------------------------------
 *  Transactions
 * ---------------------------------------------------------------------- */

function email_transaction_added(array $data): array
{
    $isExpense = ($data['type'] ?? 'expense') === 'expense';
    $color = $isExpense ? '#ef4444' : '#22c55e';
    $bg = $isExpense ? '#3b1111' : '#0f2e1a';
    $icon = $isExpense ? '💸' : '💰';
    $label = $isExpense ? 'Expense' : 'Income';

    $rows = [
        ['label' => 'Title', 'value' => $data['title'] ?? '—'],
        ['label' => 'Category', 'value' => $data['category'] ?? '—'],
        ['label' => 'Payment method', 'value' => ($data['method'] ?? '') ?: 'Not specified'],
        ['label' => 'Type', 'html' => email_pill(strtoupper($label), $color, $bg)],
    ];
    if (!empty($data['note'])) {
        $rows[] = ['label' => 'Note', 'value' => $data['note']];
    }

    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">' . $icon . ' New ' . $label . ' Added</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">A new entry has been recorded in your ledger.</p>'
        . email_amount_card('Amount', email_format_inr($data['amount'] ?? 0), $color)
        . email_details_table($rows);

    $subject = $icon . ' ' . $label . ' Added: ' . email_format_inr($data['amount'] ?? 0) . ' on ' . ($data['title'] ?? 'your ledger');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('New ' . $label, $body, email_format_inr($data['amount'] ?? 0) . ' · ' . ($data['title'] ?? ''))];
}

function email_transaction_updated(array $data): array
{
    $isExpense = ($data['type'] ?? 'expense') === 'expense';
    $color = $isExpense ? '#ef4444' : '#22c55e';

    $rows = [
        ['label' => 'Title', 'value' => $data['title'] ?? '—'],
        ['label' => 'Category', 'value' => $data['category'] ?? '—'],
        ['label' => 'Payment method', 'value' => ($data['method'] ?? '') ?: 'Not specified'],
        ['label' => 'Type', 'value' => strtoupper($data['type'] ?? 'expense')],
    ];

    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">✏️ Transaction Updated</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">An entry in your ledger has been modified.</p>'
        . email_amount_card('Updated amount', email_format_inr($data['amount'] ?? 0), $color)
        . email_details_table($rows);

    $subject = '✏️ Transaction Updated: ' . email_format_inr($data['amount'] ?? 0) . ' on ' . ($data['title'] ?? 'your ledger');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Transaction Updated', $body, 'Updated · ' . ($data['title'] ?? ''))];
}

function email_transaction_deleted(array $data): array
{
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">🗑️ Transaction Deleted</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">An entry has been removed from your ledger.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; margin:0 0 18px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Deleted entry</p>
                    <p style="margin:0 0 8px; font-size:20px; font-weight:600; color:#ef4444; word-break:break-word;">' . email_esc($data['title'] ?? 'Transaction') . '</p>
                    <p style="margin:0; font-size:26px; font-weight:700; color:#8a8a8a; text-decoration:line-through;">' . email_esc(email_format_inr($data['amount'] ?? 0)) . '</p>
                </td>
            </tr>
        </table>
        <p style="margin:0; font-size:13px; color:#6b6b6b; text-align:center;">Category: <span style="color:#f59e0b;">' . email_esc($data['category'] ?? '—') . '</span></p>';

    $subject = '🗑️ Transaction Deleted: ' . email_format_inr($data['amount'] ?? 0) . ' — ' . ($data['title'] ?? 'Entry');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Transaction Deleted', $body, 'Removed · ' . ($data['title'] ?? ''))];
}

/* -------------------------------------------------------------------------
 *  Budgets
 * ---------------------------------------------------------------------- */

function email_budget_saved(array $data): array
{
    $isNew = !empty($data['is_new']);
    $period = $data['period_type'] ?? 'monthly';

    $thresholds = $data['thresholds'] ?? '[75,90,100]';
    $decoded = json_decode((string) $thresholds, true);
    if (is_array($decoded)) {
        $thresholds = implode('%, ', $decoded) . '%';
    }

    $rows = [
        ['label' => 'Category', 'value' => $data['category'] ?? '—'],
        ['label' => 'Period', 'value' => ucfirst($period)],
        ['label' => 'Alert thresholds', 'value' => $thresholds],
    ];

    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">📊 ' . ($isNew ? 'Budget Created' : 'Budget Updated') . '</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">Your spending limits have been ' . ($isNew ? 'set' : 'updated') . '.</p>'
        . email_amount_card('Budget limit', email_format_inr($data['amount'] ?? 0), '#f59e0b', 'per ' . email_esc($period))
        . email_details_table($rows);

    $subject = '📊 Budget ' . ($isNew ? 'Set' : 'Updated') . ': ' . email_format_inr($data['amount'] ?? 0) . ' for ' . ($data['category'] ?? 'a category');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Budget ' . ($isNew ? 'Created' : 'Updated'), $body)];
}

function email_budget_deleted(array $data): array
{
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">📊 Budget Removed</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">A spending limit has been removed from your account.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Removed budget</p>
                    <p style="margin:0; font-size:20px; font-weight:600; color:#ef4444; word-break:break-word;">' . email_esc($data['category'] ?? 'Unknown') . '</p>
                </td>
            </tr>
        </table>';

    $subject = '📊 Budget Removed: ' . ($data['category'] ?? 'Unknown');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Budget Removed', $body)];
}

/* -------------------------------------------------------------------------
 *  Family space
 * ---------------------------------------------------------------------- */

function email_family_created(array $data): array
{
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">👨‍👩‍👧 Family Space Created</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">Your shared financial space is ready.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; margin:0 0 18px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Family name</p>
                    <p style="margin:0 0 16px; font-size:26px; font-weight:700; color:#f5f5f5; word-break:break-word;">' . email_esc($data['name'] ?? '') . '</p>
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Invite code</p>
                    <p style="margin:0; font-size:30px; font-weight:700; color:#f59e0b; letter-spacing:6px; font-family:Courier New, monospace;">' . email_esc($data['invite_code'] ?? '') . '</p>
                </td>
            </tr>
        </table>
        <p style="margin:0; font-size:13px; color:#8a8a8a; text-align:center;">Share this code with your family so they can join your space.</p>';

    $subject = '👨‍👩‍👧 Family Created: ' . ($data['name'] ?? '');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Family Created', $body, 'Invite code ' . ($data['invite_code'] ?? ''))];
}

function email_family_joined(array $data): array
{
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">👋 You Joined a Family</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">You are now part of a shared financial space.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Family</p>
                    <p style="margin:0; font-size:26px; font-weight:700; color:#22c55e; word-break:break-word;">' . email_esc($data['name'] ?? '') . '</p>
                </td>
            </tr>
        </table>';

    $subject = '👋 You Joined: ' . ($data['name'] ?? 'a family');
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Joined Family', $body)];
}

function email_family_left(array $data): array
{
    $name = $data['name'] ?? 'Your Family';
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">🚪 You Left a Family</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">You have left the shared financial space.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0 0 6px; font-size:11px; color:#8a8a8a; text-transform:uppercase; letter-spacing:1.4px;">Family</p>
                    <p style="margin:0; font-size:26px; font-weight:700; color:#ef4444; word-break:break-word;">' . email_esc($name) . '</p>
                </td>
            </tr>
        </table>';

    $subject = '🚪 You Left: ' . $name;
    return ['subject' => email_subject_text($subject), 'body' => email_wrap('Left Family', $body)];
}

/* -------------------------------------------------------------------------
 *  Account
 * ---------------------------------------------------------------------- */

function email_password_changed(): array
{
    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">🔒 Password Changed</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">Your account password has been successfully updated.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:24px; text-align:center;">
                    <p style="margin:0; font-size:14px; color:#22c55e;">✅ Password updated at ' . date('d M Y, h:i A') . '</p>
                </td>
            </tr>
        </table>
        <p style="margin:18px 0 0; font-size:13px; line-height:1.6; color:#ef4444; text-align:center;">If you did not make this change, secure your account immediately.</p>';

    return ['subject' => '🔒 Password Changed Successfully', 'body' => email_wrap('Password Changed', $body, 'Your ApexSpend password was updated')];
}

function email_welcome(array $data): array
{
    $name = trim((string) ($data['name'] ?? ''));
    $firstName = $name !== '' ? explode(' ', $name)[0] : 'there';

    $steps = [
        ['num' => '01', 'icon' => '💸', 'title' => 'Add your first entry', 'copy' => 'Record an expense or income — the ledger does the rest.'],
        ['num' => '02', 'icon' => '📊', 'title' => 'Set a budget', 'copy' => 'Give each category a monthly limit with 75 / 90 / 100% alerts.'],
        ['num' => '03', 'icon' => '👨‍👩‍👧', 'title' => 'Create a family space', 'copy' => 'Invite family with a code and track shared spending together.'],
    ];

    $rows = '';
    foreach ($steps as $index => $step) {
        $border = $index < count($steps) - 1 ? 'border-bottom:1px solid #262626;' : '';
        $rows .= '
            <tr>
                <td style="padding:16px 12px 16px 0; vertical-align:top; width:32px; ' . $border . '">
                    <span style="font-family:Courier New, monospace; font-size:12px; color:#f59e0b;">' . $step['num'] . '</span>
                </td>
                <td style="padding:16px 0; ' . $border . '">
                    <p style="margin:0 0 4px; font-size:15px; font-weight:600; color:#f5f5f5;">' . $step['icon'] . ' ' . email_esc($step['title']) . '</p>
                    <p style="margin:0; font-size:13px; line-height:1.6; color:#8a8a8a;">' . email_esc($step['copy']) . '</p>
                </td>
            </tr>';
    }

    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">👋 Welcome to ApexSpend, ' . email_esc($firstName) . '</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">Your account is ready. Here\'s how to get the most out of it in the next 5 minutes.</p>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#1a1a1a; border-radius:8px; border:1px solid #2a2a2a;">
            <tr>
                <td style="padding:8px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rows . '
                    </table>
                </td>
            </tr>
        </table>

        <p style="margin:22px 0 0; font-size:13px; line-height:1.6; color:#8a8a8a; text-align:center;">Sign in any time to pick up where you left off.</p>';

    $subject = '👋 Welcome to ApexSpend, ' . $firstName;
    return [
        'subject' => email_subject_text($subject),
        'body'    => email_wrap('Welcome to ApexSpend', $body, 'Your account is ready — here are three quick steps to get started.'),
    ];
}

function email_profile_updated(array $data): array
{
    $rows = [
        ['label' => 'Name', 'value' => $data['name'] ?? '—'],
        ['label' => 'Email', 'value' => $data['email'] ?? '—'],
    ];

    $body = '
        <h1 style="margin:0 0 6px; font-size:23px; line-height:1.3; color:#f5f5f5; font-weight:600;">👤 Profile Updated</h1>
        <p style="margin:0 0 22px; font-size:14px; color:#8a8a8a;">Your profile information has been changed.</p>'
        . email_details_table($rows);

    return ['subject' => '👤 Profile Updated Successfully', 'body' => email_wrap('Profile Updated', $body)];
}
