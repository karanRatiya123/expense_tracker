<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();
$userId = (int) $_SESSION['user']['id'];
$user = $_SESSION['user'];
$activePage = 'profile';

// Hydrate full record (DB has created_at + avatar_path; session store is the source in fallback).
if ($pdo) {
    $stmt = $pdo->prepare('SELECT id, name, email, avatar_path, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    if ($row = $stmt->fetch()) $user = array_merge($user, $row);

    $txCount = (int) $pdo->query("SELECT COUNT(*) FROM transactions WHERE user_id = $userId")->fetchColumn();
    // budgets table doesn't exist yet — counts from transactions by category as a stand-in.
    $budgetCount = (int) $pdo->query("SELECT COUNT(DISTINCT category) FROM transactions WHERE user_id = $userId")->fetchColumn();
} else {
    foreach ($_SESSION['users_db'] as $u) {
        if ((int) $u['id'] === $userId) {
            if (!isset($u['created_at'])) $u['created_at'] = date('Y-m-d H:i:s');
            if (!isset($u['avatar_path'])) $u['avatar_path'] = null;
            $user = array_merge($user, $u);
            break;
        }
    }
    $txCount = 6;       // matches the seeded ledger rows
    $budgetCount = 5;   // matches the seeded budget categories
}

$initials = strtoupper(substr($user['name'], 0, 1));
$createdAt = $user['created_at'] ?? date('Y-m-d H:i:s');
$memberSince = date('F Y', strtotime($createdAt));
$accountAgeDays = max(1, (int) floor((time() - strtotime($createdAt)) / 86400));

$flashError = getFlashMessage('error');
$flashSuccess = getFlashMessage('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>ApexSpend — Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist+Mono:wght@400;500&family=Geist:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 1.25rem;
        }
        @media (max-width: 880px) { .profile-grid { grid-template-columns: 1fr; } }

        .profile-card { padding: 1.5rem; }
        .avatar-xl {
            width: 96px; height: 96px;
            border-radius: 50%;
            display: grid; place-items: center;
            font-family: var(--font-serif);
            font-size: 2.4rem;
            font-style: italic;
            color: var(--card);
            background: linear-gradient(135deg, var(--chart-1), var(--chart-2));
            margin-bottom: 1rem;
        }
        .profile-name { margin: 0 0 .15rem; font-size: 1.4rem; }
        .profile-email { margin: 0 0 1rem; color: var(--muted-foreground); font-size: .9rem; }
        .meta-list { list-style: none; padding: 0; margin: 1rem 0 0; }
        .meta-list li {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: .55rem 0; border-top: 1px dashed var(--border);
            font-size: .9rem;
        }
        .meta-list li:first-child { border-top: 0; }
        .meta-list .k { color: var(--muted-foreground); }
        .meta-list .v { font-family: var(--font-mono); }

        .stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin: 1rem 0 0; }
        .stat {
            background: var(--muted);
            border-radius: var(--radius);
            padding: .85rem;
            text-align: center;
        }
        .stat .n { font-family: var(--font-serif); font-size: 1.6rem; line-height: 1; }
        .stat .l { font-size: .7rem; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: .04em; margin-top: .25rem; }

        .section-title { font-family: var(--font-serif); font-style: italic; font-size: 1.1rem; margin: 0 0 .75rem; }
        form.profile-form { display: grid; gap: .75rem; }
        .pf-field { display: grid; gap: .3rem; }
        .pf-field label { font-size: .8rem; color: var(--muted-foreground); }
        .pf-field input {
            padding: .6rem .75rem; font: inherit; color: var(--foreground);
            background: var(--input); border: 1px solid var(--border);
            border-radius: var(--radius); outline: none;
        }
        .pf-field input:focus { border-color: var(--ring); }
        .pf-field input[readonly] { opacity: .7; cursor: default; }
        .pf-actions { display: flex; gap: .5rem; }
        .btn {
            font: inherit; font-weight: 500; cursor: pointer;
            padding: .55rem 1rem; border-radius: var(--radius); border: 1px solid var(--border);
            background: var(--card); color: var(--foreground);
        }
        .btn-primary { background: var(--primary); color: var(--primary-foreground); border-color: var(--primary); }
        .btn-ghost { background: transparent; }

        .flash {
            padding: .6rem .85rem; border-radius: var(--radius);
            font-size: .9rem; margin-bottom: 1rem;
        }
        .flash.error { background: rgb(255 99 99 / .12); color: var(--destructive); }
        .flash.success { background: rgb(80 200 120 / .12); color: #4ade80; }

        .pref-row { display: flex; justify-content: space-between; align-items: center; padding: .55rem 0; border-top: 1px dashed var(--border); }
        .pref-row:first-of-type { border-top: 0; }
        .pref-row .k { font-size: .9rem; }
        .pref-row .v { color: var(--muted-foreground); font-size: .85rem; font-family: var(--font-mono); }
    </style>
</head>
<body>
<div class="app">

    <aside class="side">
        <div class="side-top">
            <div class="brand">
                <span class="brand-mark">ApexSpend</span>
                <span class="brand-dot" aria-hidden="true"></span>
            </div>
            <div class="filing">
                <span class="num">VOL. 04 · NO. 209</span>
                <span class="dot" aria-hidden="true"></span>
                <span><?php echo date('D · d M Y'); ?></span>
            </div>
            <ul class="nav" role="navigation">
                <li><a href="dashboard.php" class="<?= $activePage==='overview' ? 'is-active' : '' ?>"><i class="fa-solid fa-house nav-ic"></i><span class="label">Overview</span></a></li>
                <li><a href="transactions.php" class="<?= $activePage==='ledger' ? 'is-active' : '' ?>"><i class="fa-solid fa-receipt nav-ic"></i><span class="label">Ledger</span></a></li>
                <li><a href="analytics.php" class="<?= $activePage==='analytics' ? 'is-active' : '' ?>"><i class="fa-solid fa-chart-pie nav-ic"></i><span class="label">Analytics</span></a></li>
                <li><a href="budgets.php" class="<?= $activePage==='budgets' ? 'is-active' : '' ?>"><i class="fa-solid fa-bullseye nav-ic"></i><span class="label">Budgets</span></a></li>
                <li><a href="profile.php" class="<?= $activePage==='profile' ? 'is-active' : '' ?>"><i class="fa-solid fa-user nav-ic"></i><span class="label">Profile</span></a></li>
            </ul>
        </div>
        <div class="who">
            <a href="profile.php" class="avatar"><?= htmlspecialchars($initials) ?></a>
            <div class="who-meta">
                <strong><?= htmlspecialchars($user['name']) ?></strong>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <button type="button" class="logout" onclick="window.location.href='logout.php'" aria-label="Sign out">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </button>
        </div>
    </aside>

    <main class="canvas">

        <header class="masthead">
            <div class="masthead-col">
                <div class="kicker">
                    <span class="kicker-num">§ 05</span>
                    <span class="kicker-text">The Reader's Card</span>
                </div>
                <h1>Your <em>profile</em>.</h1>
                <p class="standfirst">Name, email, password, and the rest of the filing — all in one place.</p>
            </div>
            <div class="masthead-figure">
                <div class="figure-label">Member since</div>
                <div class="figure-value" style="font-size:1.8rem;"><?= htmlspecialchars($memberSince) ?></div>
                <div class="figure-meta"><span class="muted"><?= $accountAgeDays ?> days on the books</span></div>
            </div>
        </header>

        <?php if ($flashError): ?><div class="flash error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>
        <?php if ($flashSuccess): ?><div class="flash success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>

        <div class="profile-grid">

            <!-- LEFT: identity card -->
            <section class="card profile-card">
                <div class="avatar-xl"><?= htmlspecialchars($initials) ?></div>
                <h2 class="profile-name"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>

                <ul class="meta-list">
                    <li><span class="k">Member since</span><span class="v"><?= htmlspecialchars($memberSince) ?></span></li>
                    <li><span class="k">Account age</span><span class="v"><?= $accountAgeDays ?> days</span></li>
                    <li><span class="k">User ID</span><span class="v">#<?= (int) $user['id'] ?></span></li>
                </ul>

                <div class="stat-row">
                    <div class="stat"><div class="n"><?= $txCount ?></div><div class="l">Transactions</div></div>
                    <div class="stat"><div class="n"><?= $budgetCount ?></div><div class="l">Budgets</div></div>
                    <div class="stat"><div class="n"><?= $accountAgeDays ?></div><div class="l">Days</div></div>
                </div>
            </section>

            <!-- RIGHT: forms + prefs -->
            <div style="display:grid; gap:1.25rem;">

                <section class="card profile-card">
                    <h3 class="section-title">Basic information</h3>
                    <form class="profile-form" method="POST" action="update_profile.php" id="infoForm">
                        <input type="hidden" name="action" value="update_info">
                        <div class="pf-field">
                            <label for="name">Full name</label>
                            <input id="name" name="name" type="text" value="<?= htmlspecialchars($user['name']) ?>" required minlength="2">
                        </div>
                        <div class="pf-field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="pf-field">
                            <label>Member since</label>
                            <input value="<?= htmlspecialchars($memberSince) ?>" readonly>
                        </div>
                        <div class="pf-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-regular fa-floppy-disk"></i>&nbsp; Save changes</button>
                            <button type="reset" class="btn btn-ghost">Reset</button>
                        </div>
                    </form>
                </section>

                <section class="card profile-card">
                    <h3 class="section-title">Security</h3>
                    <form class="profile-form" method="POST" action="update_profile.php" id="pwForm">
                        <input type="hidden" name="action" value="change_password">
                        <div class="pf-field">
                            <label for="old_password">Current password</label>
                            <input id="old_password" name="old_password" type="password" required autocomplete="current-password">
                        </div>
                        <div class="pf-field">
                            <label for="new_password">New password</label>
                            <input id="new_password" name="new_password" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="pf-field">
                            <label for="confirm_password">Confirm new password</label>
                            <input id="confirm_password" name="confirm_password" type="password" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="pf-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-shield-halved"></i>&nbsp; Update password</button>
                        </div>
                    </form>
                </section>

                <section class="card profile-card">
                    <h3 class="section-title">Preferences</h3>
                    <div class="pref-row"><span class="k">Currency</span><span class="v">₹ · INR (locked)</span></div>
                    <div class="pref-row"><span class="k">Theme</span><span class="v">Dark (default)</span></div>
                    <div class="pref-row"><span class="k">Date format</span><span class="v">DD MMM YYYY</span></div>
                </section>

            </div>
        </div>

    </main>
</div>

<script src="profile.js"></script>
</body>
</html>
