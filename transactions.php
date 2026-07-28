<?php
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'name' => 'Alex Morgan',
        'email' => 'alex.morgan@apexspend.com'
    ];
}

$user = $_SESSION['user'];
$initials = strtoupper(substr($user['name'], 0, 1));

// ---- Format helpers ----
function format_inr($n) {
    return number_format((float)$n, 2, '.', ',');
}
function format_long_date($iso) {
    $ts = strtotime($iso);
    if (!$ts) return '';
    return date('d M', $ts);
}
function format_time($iso) {
    $ts = strtotime($iso);
    if (!$ts) return '';
    return date('H:i', $ts);
}
function category_icon($cat) {
    $map = [
        'Food & Dining' => 'fa-utensils',
        'Housing & Utilities' => 'fa-bolt',
        'Shopping & Retail' => 'fa-bag-shopping',
        'Transportation' => 'fa-car',
        'Entertainment' => 'fa-film',
        'Income' => 'fa-money-bill-wave',
        'Health & Medical' => 'fa-briefcase-medical',
    ];
    return $map[$cat] ?? 'fa-receipt';
}

// ---- Seed demo transactions if not already in session ----
if (!isset($_SESSION['transactions']) || empty($_SESSION['transactions'])) {
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
}

$transactions = $_SESSION['transactions'];

// Compute summary stats
$total_in      = 0;
$total_out     = 0;
$count         = count($transactions);
foreach ($transactions as $t) {
    if ($t['type'] === 'income') $total_in += $t['amount'];
    else                          $total_out += $t['amount'];
}
$net = $total_in - $total_out;

// Unique categories for filter chips
$categories = [];
foreach ($transactions as $t) {
    $categories[$t['category']] = true;
}
$categories = array_keys($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>ApexSpend — The Ledger</title>
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
                <li><a href="dashboard.php">
                    <i class="fa-solid fa-house nav-ic" aria-hidden="true"></i>
                    <span class="label">Overview</span>
                </a></li>
                <li><a href="transactions.php" class="is-active">
                    <i class="fa-solid fa-receipt nav-ic" aria-hidden="true"></i>
                    <span class="label">Ledger</span>
                </a></li>
                <li><a href="#">
                    <i class="fa-solid fa-chart-pie nav-ic" aria-hidden="true"></i>
                    <span class="label">Analytics</span>
                </a></li>
                <li><a href="#">
                    <i class="fa-solid fa-bullseye nav-ic" aria-hidden="true"></i>
                    <span class="label">Budgets</span>
                </a></li>
                <li><a href="#">
                    <i class="fa-solid fa-gear nav-ic" aria-hidden="true"></i>
                    <span class="label">Settings</span>
                </a></li>
            </ul>
        </div>

        <div class="who">
            <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div class="who-meta">
                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
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
                    <span class="kicker-num">§ 02</span>
                    <span class="kicker-text">The Daily Ledger</span>
                </div>
                <h1>Every line item,<br><em>filed.</em></h1>
                <p class="standfirst">The complete archive of your inflows and outflows — searchable, filterable, and ordered in the moment they happened.</p>
            </div>

            <div class="masthead-figure">
                <div class="figure-label">Net position · all time</div>
                <div class="figure-value">
                    <span class="currency">₹</span><span id="totalBalance"><?php echo format_inr($net); ?></span>
                </div>
                <div class="figure-meta">
                    <span class="muted"><?php echo $count; ?> entries on file</span>
                </div>
            </div>
        </header>

        <!-- =================== KPI ROW =================== -->
        <section class="kpi" aria-label="Ledger summary">
            <div class="cell">
                <div class="cell-num">01</div>
                <div class="label">Total inflow</div>
                <div class="figure"><span class="currency">₹</span><span id="totalIncome"><?php echo format_inr($total_in); ?></span></div>
                <div class="meta"><span class="muted">Income entries</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">02</div>
                <div class="label">Total outflow</div>
                <div class="figure"><span class="currency">₹</span><span id="totalExpenses"><?php echo format_inr($total_out); ?></span></div>
                <div class="meta"><span class="muted">Expense entries</span></div>
            </div>
            <div class="cell">
                <div class="cell-num">03</div>
                <div class="label">Entries on file</div>
                <div class="figure"><span id="totalCount"><?php echo $count; ?></span></div>
                <div class="meta"><span class="muted">Across <?php echo count($categories); ?> categories</span></div>
            </div>
        </section>

        <!-- =================== LEDGER =================== -->
        <section class="section ledger-section" aria-label="Transaction history">
            <div class="section-head">
                <h2><i class="fa-solid fa-book section-ic" aria-hidden="true"></i> The ledger.</h2>
                <div class="section-meta">
                    <span>Searchable</span>
                    <span aria-hidden="true">·</span>
                    <span><span id="visibleCount"><?php echo $count; ?></span> of <?php echo $count; ?> entries</span>
                </div>
            </div>

            <div class="ledger">

                <!-- Search -->
                <div class="ledger-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" id="txSearch" placeholder="Search merchant, note, or category…" autocomplete="off">
                </div>

                <!-- Category filter chips -->
                <div class="filters" role="tablist" aria-label="Filter by category">
                    <button class="is-on" data-filter="all">All</button>
                    <button data-filter="income">Income</button>
                    <button data-filter="expense">Expense</button>
                    <?php foreach ($categories as $cat): ?>
                        <button data-filter="<?php echo htmlspecialchars($cat); ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Header row -->
                <div class="led-grid head" aria-hidden="true">
                    <span class="col-label">When</span>
                    <span class="col-label">Merchant &amp; note</span>
                    <span class="col-label">Category</span>
                    <span class="col-label col-num">Method</span>
                    <span class="col-label col-num">Amount</span>
                </div>

                <!-- Body -->
                <div id="ledgerBody">
                <?php foreach ($transactions as $t):
                    $isIncome  = $t['type'] === 'income';
                    $sign      = $isIncome ? '+' : '−';
                    $cls       = $isIncome ? 'amount pos' : 'amount';
                    $searchHay = strtolower($t['title'] . ' ' . $t['note'] . ' ' . $t['category']);
                ?>
                    <div class="led-grid"
                         data-category="<?php echo htmlspecialchars($t['category']); ?>"
                         data-type="<?php echo htmlspecialchars($t['type']); ?>"
                         data-merchant="<?php echo htmlspecialchars($t['title'] . ' ' . $t['note']); ?>"
                         data-hay="<?php echo htmlspecialchars($searchHay); ?>">
                        <span class="when">
                            <span class="day"><?php echo format_long_date($t['date']); ?></span>
                            <?php echo format_time($t['date']); ?>
                        </span>
                        <span class="col-merch">
                            <strong><?php echo htmlspecialchars($t['title']); ?></strong>
                            <?php echo htmlspecialchars($t['note']); ?>
                        </span>
                        <span class="cat">
                            <i class="fa-solid <?php echo category_icon($t['category']); ?>" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($t['category']); ?>
                        </span>
                        <span class="method"><?php echo htmlspecialchars($t['method']); ?></span>
                        <span class="amount-wrap">
                            <span class="<?php echo $cls; ?>"><?php echo $sign; ?> ₹<?php echo format_inr($t['amount']); ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
                </div>

                <div class="ledger-foot">
                    <span class="filing">Filed 28 Jul 2026 — Page 01 of 01</span>
                    <span class="muted" id="emptyMsg" hidden>No matching entries.</span>
                    <span class="muted" id="endMsg">End of entries.</span>
                </div>
            </div>
        </section>

    </main>
</div>

<script src="transactions.js"></script>
</body>
</html>
