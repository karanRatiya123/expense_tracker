<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user     = $_SESSION['user'];
$initials = strtoupper(substr($user['name'], 0, 1));
$activePage = 'budgets';

function format_inr($n) {
    return number_format((float)$n, 2, '.', ',');
}

// ---- Seed demo transactions (KEEP IN SYNC with transactions.php + analytics.php) ----
if (!isset($_SESSION['transactions'])) {
    if (isset($user['email']) && $user['email'] === 'demo@apexspend.com') {
        $_SESSION['transactions'] = [
            ['id' => 'tx_201', 'type' => 'expense', 'title' => 'Chai Point',        'note' => 'Evening chai & samosa',          'amount' => 180.00,    'category' => 'Food & Dining',         'date' => '2026-07-28T15:45:00', 'method' => 'UPI ··4182'],
            ['id' => 'tx_202', 'type' => 'expense', 'title' => 'BigBasket',         'note' => 'Weekly groceries',               'amount' => 11800.50,  'category' => 'Food & Dining',         'date' => '2026-07-28T14:30:00', 'method' => 'UPI ··4182'],
            ['id' => 'tx_203', 'type' => 'income',  'title' => 'Tech Corp Payroll', 'note' => 'Salary, July',                    'amount' => 450000.00, 'category' => 'Income',                'date' => '2026-07-25T09:00:00', 'method' => 'NEFT ··0019'],
            ['id' => 'tx_204', 'type' => 'expense', 'title' => 'Tata Power',        'note' => 'Electric bill, July',             'amount' => 8240.00,   'category' => 'Housing & Utilities',   'date' => '2026-07-22T10:14:00', 'method' => 'NEFT ··0019'],
            ['id' => 'tx_205', 'type' => 'expense', 'title' => 'Netflix',           'note' => 'Standard plan, monthly',          'amount' => 649.00,    'category' => 'Entertainment',         'date' => '2026-07-20T18:12:00', 'method' => 'UPI ··4182'],
            ['id' => 'tx_206', 'type' => 'expense', 'title' => 'Rapido',            'note' => 'Ride to Kempegowda Airport, T2',  'amount' => 1420.00,   'category' => 'Transportation',        'date' => '2026-07-18T11:30:00', 'method' => 'UPI'],
            ['id' => 'tx_207', 'type' => 'expense', 'title' => 'Apollo Pharmacy',   'note' => 'Vitamins & first-aid',            'amount' => 2150.00,   'category' => 'Health & Medical',      'date' => '2026-07-15T19:45:00', 'method' => 'RuPay ··2207'],
            ['id' => 'tx_208', 'type' => 'expense', 'title' => 'H&M',               'note' => 'Summer wardrobe',                 'amount' => 6480.00,   'category' => 'Shopping & Retail',     'date' => '2026-07-12T16:22:00', 'method' => 'RuPay ··2207'],
            ['id' => 'tx_209', 'type' => 'expense', 'title' => 'BESCOM',            'note' => 'Electricity, June',               'amount' => 6450.00,   'category' => 'Housing & Utilities',   'date' => '2026-07-08T10:00:00', 'method' => 'NEFT ··0019'],
            ['id' => 'tx_210', 'type' => 'expense', 'title' => 'Indigo',            'note' => 'Flight BOM → BLR',                'amount' => 8950.00,   'category' => 'Transportation',        'date' => '2026-07-05T07:15:00', 'method' => 'RuPay ··2207'],
            ['id' => 'tx_211', 'type' => 'expense', 'title' => 'Toit Brewery',      'note' => 'Dinner with friends',             'amount' => 4200.00,   'category' => 'Food & Dining',         'date' => '2026-07-03T20:30:00', 'method' => 'UPI ··4182'],
            ['id' => 'tx_212', 'type' => 'expense', 'title' => 'BookMyShow',        'note' => 'Inox, 4 tickets',                 'amount' => 1800.00,   'category' => 'Entertainment',         'date' => '2026-07-01T19:00:00', 'method' => 'UPI ··4182'],
            ['id' => 'tx_213', 'type' => 'expense', 'title' => 'Airtel Broadband',  'note' => 'Monthly plan',                    'amount' => 999.00,    'category' => 'Housing & Utilities',   'date' => '2026-06-28T08:00:00', 'method' => 'NEFT ··0019'],
            ['id' => 'tx_214', 'type' => 'expense', 'title' => 'Decathlon',         'note' => 'Running shoes',                   'amount' => 5999.00,   'category' => 'Shopping & Retail',     'date' => '2026-06-25T14:10:00', 'method' => 'RuPay ··2207'],
            ['id' => 'tx_215', 'type' => 'income',  'title' => 'Freelance Project', 'note' => 'UI design — milestone 2',         'amount' => 75000.00,  'category' => 'Income',                'date' => '2026-06-20T11:00:00', 'method' => 'IMPS'],
            ['id' => 'tx_216', 'type' => 'expense', 'title' => 'Ola Cabs',          'note' => 'Airport → Home',                  'amount' => 1120.00,   'category' => 'Transportation',        'date' => '2026-06-18T23:45:00', 'method' => 'UPI'],
            ['id' => 'tx_217', 'type' => 'expense', 'title' => 'Saravana Bhavan',   'note' => 'Family dinner',                   'amount' => 2840.00,   'category' => 'Food & Dining',         'date' => '2026-06-15T20:00:00', 'method' => 'Cash'],
            ['id' => 'tx_218', 'type' => 'expense', 'title' => 'Cult Fitness',      'note' => 'Monthly membership',              'amount' => 2500.00,   'category' => 'Health & Medical',      'date' => '2026-06-10T07:30:00', 'method' => 'NEFT ··0019'],
            ['id' => 'tx_219', 'type' => 'expense', 'title' => 'Spotify',           'note' => 'Premium, family plan',            'amount' => 299.00,    'category' => 'Entertainment',         'date' => '2026-06-05T09:00:00', 'method' => 'RuPay ··2207'],
            ['id' => 'tx_220', 'type' => 'expense', 'title' => 'Manipal Hospital',  'note' => 'Annual health check-up',           'amount' => 7800.00,   'category' => 'Health & Medical',      'date' => '2026-06-02T11:30:00', 'method' => 'RuPay ··2207'],
        ];
    } else {
        $_SESSION['transactions'] = [];
    }
}

$transactions = $_SESSION['transactions'];

// =========================================================================
// Build JSON payload for budgets.js
// =========================================================================
$payload = [
    'currency' => 'INR',
    'count'    => count($transactions),
    'transactions' => array_map(fn($t) => [
        'category' => $t['category'],
        'type'     => $t['type'],
        'amount'   => (float)$t['amount'],
        'date'     => $t['date'],
    ], $transactions),
    'categories' => [
        'Food & Dining'       => 'fa-utensils',
        'Housing & Utilities' => 'fa-bolt',
        'Shopping & Retail'   => 'fa-bag-shopping',
        'Transportation'      => 'fa-car',
        'Entertainment'       => 'fa-film',
        'Income'              => 'fa-money-bill-wave',
        'Health & Medical'    => 'fa-briefcase-medical',
    ],
    'demoDate' => '2026-07-28',
];

$jsonPayload = json_encode(
    $payload,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" dark>
    <title>ApexSpend — Budgets</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist+Mono:wght@400;500&family=Geist:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="app">

    <!-- =================== SIDEBAR =================== -->
    <aside class="side">
        <div class="side-top">
            <div class="brand">
                <span class="brand-mark">ApexSpend</span>
                <span class="brand-dot" aria-hidden="true"></span>
            </div>

            <div class="filing">
                <span class="num">VOL. 04 · NO. 209</span>
                <span class="dot" aria-hidden="true"></span>
                <span>TUES · 28 JUL 2026</span>
            </div>

            <ul class="nav" role="navigation">
                <li><a href="dashboard.php" class="<?php echo $activePage==='overview' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-house nav-ic" aria-hidden="true"></i>
                    <span class="label">Overview</span>
                </a></li>
                <li><a href="transactions.php" class="<?php echo $activePage==='ledger' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-receipt nav-ic" aria-hidden="true"></i>
                    <span class="label">Ledger</span>
                </a></li>
                <li><a href="analytics.php" class="<?php echo $activePage==='analytics' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie nav-ic" aria-hidden="true"></i>
                    <span class="label">Analytics</span>
                </a></li>
                <li><a href="budgets.php" class="<?php echo $activePage==='budgets' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-bullseye nav-ic" aria-hidden="true"></i>
                    <span class="label">Budgets</span>
                </a></li>
                <li><a href="profile.php" class="<?php echo $activePage==='settings' || $activePage==='profile' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-user nav-ic" aria-hidden="true"></i>
                    <span class="label">Profile</span>
                </a></li>
            </ul>
        </div>

        <div class="who">
            <a href="profile.php" class="avatar" aria-label="Open profile"><?php echo htmlspecialchars($initials); ?></a>
            <div class="who-meta">
                <strong><a href="profile.php"><?php echo htmlspecialchars($user['name']); ?></a></strong>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <button type="button" class="logout" onclick="window.location.href='logout.php'" aria-label="Sign out">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
            </button>
        </div>
    </aside>

    <!-- =================== MAIN CANVAS =================== -->
    <main class="canvas">

        <!-- =================== MASTHEAD =================== -->
        <header class="masthead">
            <div class="masthead-col">
                <div class="kicker">
                    <span class="kicker-num">§ 04</span>
                    <span class="kicker-text">The Spend Ceiling</span>
                </div>
                <h1>What you allow<br><em>yourself to spend.</em></h1>
                <p class="standfirst">A ceiling, a thermometer, and a trip-wire for each category — set monthly, weekly, or any window you like.</p>
            </div>

            <div class="masthead-figure">
                <div class="figure-label">
                    Total monthly limit
                    <span class="kicker-num" style="margin-left:8px;" id="heroPeriodLabel">Monthly</span>
                </div>
                <div class="figure-value">
                    <span class="currency">₹</span><span id="heroAllocated">—</span>
                </div>
                <div class="figure-meta">
                    <span class="muted">Spent </span>
                    <span class="mono" id="heroSpent">—</span>
                    <span class="muted">· Remaining </span>
                    <span class="mono" id="heroRemaining">—</span>
                </div>
                <div class="actions">
                    <button type="button" class="add" onclick="openBudgetModal()">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <span>Set new budget</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- =================== TOOLBAR =================== -->
        <section class="budgets-toolbar" aria-label="Period selector">
            <div class="toolbar-left">
                <span class="toolbar-label">Period</span>
                <div class="period-toggle" role="group" aria-label="Period">
                    <button type="button" class="is-on" data-bperiod="monthly">Monthly</button>
                    <button type="button" data-bperiod="weekly">Weekly</button>
                    <button type="button" data-bperiod="custom">Custom</button>
                </div>
                <span class="kicker-num" id="budgetCount" style="margin-left:8px;">0 budgets</span>
            </div>
            <div class="toolbar-right">
                <span class="kicker-num" id="budgetUpdated">Live</span>
            </div>
        </section>

        <!-- =================== HERO KPI STRIP (5-up) =================== -->
        <section class="kpi variant-five" id="budgetKpis" aria-label="Budget overview">
            <div class="cell">
                <div class="cell-num">01</div>
                <div class="label">Total budgeted</div>
                <div class="figure"><span class="currency">₹</span><span id="kpiBudgeted">—</span></div>
                <div class="meta"><span class="muted">across all categories</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">02</div>
                <div class="label">Total spent</div>
                <div class="figure"><span class="currency">₹</span><span id="kpiSpent">—</span></div>
                <div class="meta"><span class="muted" id="kpiSpentPct">—</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">03</div>
                <div class="label">Remaining</div>
                <div class="figure"><span class="currency">₹</span><span id="kpiRemaining">—</span></div>
                <div class="meta"><span class="muted" id="kpiRemainingPct">—</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">04</div>
                <div class="label">Over budget</div>
                <div class="figure" id="kpiOver">0</div>
                <div class="meta"><span class="muted">categories past 100%</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">05</div>
                <div class="label">On track</div>
                <div class="figure" id="kpiOnTrack">0</div>
                <div class="meta"><span class="muted">under 75% used</span></div>
            </div>
        </section>

        <!-- =================== BUDGET LEDGER =================== -->
        <section class="section">
            <div class="section-head">
                <h2><i class="fa-solid fa-bullseye section-ic" aria-hidden="true"></i> Budget ledger.</h2>
                <div class="section-meta">
                    <span id="budgetGridMeta">Live</span>
                    <span aria-hidden="true">·</span>
                    <span>Auto-rolling</span>
                </div>
            </div>
            <div class="budgets" id="budgetsGrid"><!-- populated by JS --></div>
        </section>

        <!-- =================== EMPTY STATE =================== -->
        <section class="section" id="budgetsEmpty" hidden>
            <div class="empty-state">
                <i class="fa-solid fa-bullseye" aria-hidden="true" style="font-size:36px;color:var(--whisper);"></i>
                <h2 style="font-family:var(--serif);font-style:italic;margin:12px 0 6px;">No budgets on file.</h2>
                <p>Set your first ceiling — pick a category, a limit, and a period.</p>
                <button type="button" class="add" onclick="openBudgetModal()" style="margin-top:16px;">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    <span>Set new budget</span>
                </button>
            </div>
        </section>

        <!-- =================== FOOTER =================== -->
        <footer class="analytics-foot">Filed under § 04 · End of budgets.</footer>

    </main>
</div>

<!-- =================== ADD/EDIT BUDGET MODAL =================== -->
<div class="scrim" id="budgetModal" aria-hidden="true">
    <div class="sheet" role="dialog" aria-labelledby="budgetSheetTitle">
        <div class="sheet-head">
            <div>
                <span class="kicker-num" id="budgetModalKicker">§ 04</span>
                <h3 id="budgetSheetTitle">New budget</h3>
            </div>
            <button type="button" class="close" onclick="closeBudgetModal()" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="budgetForm" class="modal-form" onsubmit="handleBudgetSave(event)">
            <div class="field">
                <div class="field-row"><label for="bCategory">Category</label></div>
                <div class="input-shell">
                    <i class="fa-solid fa-tag" aria-hidden="true"></i>
                    <select id="bCategory" required>
                        <!-- populated by JS -->
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="field-row"><label for="bAmount">Monthly limit (₹)</label></div>
                    <div class="input-shell">
                        <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
                        <input type="number" id="bAmount" step="0.01" min="1" placeholder="0.00" required>
                    </div>
                </div>
                <div class="field">
                    <div class="field-row"><label for="bPeriod">Period</label></div>
                    <div class="input-shell">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <select id="bPeriod">
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid-2" id="bCustomRange" hidden>
                <div class="field">
                    <div class="field-row"><label for="bStart">Start</label></div>
                    <div class="input-shell">
                        <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i>
                        <input type="date" id="bStart">
                    </div>
                </div>
                <div class="field">
                    <div class="field-row"><label for="bEnd">End</label></div>
                    <div class="input-shell">
                        <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                        <input type="date" id="bEnd">
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="field-row"><label>Alert thresholds (default 75% · 90% · 100%)</label></div>
                <div class="input-shell" style="gap:6px;">
                    <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    <input type="number" id="bT1" min="1" max="100" value="75" style="max-width:64px;" aria-label="Threshold 1">
                    <span class="mono muted">%</span>
                    <input type="number" id="bT2" min="1" max="100" value="90" style="max-width:64px;" aria-label="Threshold 2">
                    <span class="mono muted">%</span>
                    <input type="number" id="bT3" min="1" max="100" value="100" style="max-width:64px;" aria-label="Threshold 3">
                    <span class="mono muted">%</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="submit danger" id="bDeleteBtn" hidden onclick="handleBudgetDelete()">
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                    <span>Delete</span>
                </button>
                <button type="submit" class="submit">
                    <i class="fa-solid fa-feather" aria-hidden="true"></i>
                    <span id="bSaveLabel">File budget</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="toastContainer" class="toast-stack" aria-live="polite"></div>

<script id="budgetsData" type="application/json"><?php echo $jsonPayload; ?></script>
<script src="script.js"></script>
<script src="budgets.js?v=<?php echo time(); ?>"></script>
</body>
</html>
