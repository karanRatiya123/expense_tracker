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
$activePage = 'overview';
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="#" class="<?php echo $activePage==='budgets' ? 'is-active' : ''; ?>">
                    <i class="fa-solid fa-bullseye nav-ic" aria-hidden="true"></i>
                    <span class="label">Budgets</span>
                </a></li>
                <li><a href="#" class="<?php echo $activePage==='settings' ? 'is-active' : ''; ?>">
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
                    <span class="kicker-num">§ 01</span>
                    <span class="kicker-text">The Daily Ledger</span>
                </div>
                <h1>Good evening,<br><em><?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?></em>.</h1>
                <p class="standfirst">Six figures, two charts, and the line items in between — filed in the order they happened.</p>
            </div>

            <div class="masthead-figure">
                <div class="figure-label">Net balance · July</div>
                <div class="figure-value">
                    <span class="currency">₹</span><span id="totalBalance">12,84,550.00</span>
                </div>
                <div class="figure-meta">
                    <span class="trend up">▲ 14.2%</span>
                    <span class="muted">vs. last month</span>
                </div>
                <div class="actions">
                    <button type="button" class="ghost" onclick="window.print()">
                        <i class="fa-regular fa-file-pdf" aria-hidden="true"></i>
                        <span>Export</span>
                    </button>
                    <button type="button" class="add" onclick="openModal()">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <span>New entry</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- =================== KPI ROW + MINI BAR CHARTS =================== -->
        <section class="kpi" aria-label="Key figures">
            <div class="cell">
                <div class="cell-num">01</div>
                <div class="label">Income, July</div>
                <div class="figure"><span class="currency">₹</span><span id="totalIncome">8,50,000.00</span></div>
                <div class="meta"><span class="trend up">▲ 6.2%</span><span>vs. June</span></div>
                <!-- Mini bar chart: income by month -->
                <div class="spark" aria-hidden="true">
                    <span class="bar" style="--h: 60%"></span>
                    <span class="bar" style="--h: 68%"></span>
                    <span class="bar" style="--h: 64%"></span>
                    <span class="bar" style="--h: 78%"></span>
                    <span class="bar" style="--h: 80%"></span>
                    <span class="bar is-peak" style="--h: 88%"></span>
                </div>
            </div>
            <div class="cell">
                <div class="cell-num">02</div>
                <div class="label">Outflows, July</div>
                <div class="figure"><span class="currency">₹</span><span id="totalExpenses">3,21,450.00</span></div>
                <div class="meta"><span class="trend down">▼ 3.7%</span><span>vs. June</span></div>
                <div class="spark" aria-hidden="true">
                    <span class="bar alt" style="--h: 72%"></span>
                    <span class="bar alt" style="--h: 80%"></span>
                    <span class="bar alt" style="--h: 64%"></span>
                    <span class="bar alt" style="--h: 92%"></span>
                    <span class="bar alt" style="--h: 70%"></span>
                    <span class="bar alt" style="--h: 74%"></span>
                </div>
            </div>
            <div class="cell">
                <div class="cell-num">03</div>
                <div class="label">Saved this month</div>
                <div class="figure"><span class="currency">₹</span><span id="totalSavings">5,28,550.00</span></div>
                <div class="meta"><span class="trend up">▲ 84%</span><span>of goal</span></div>
                <!-- Progress bar -->
                <div class="progress" aria-label="Progress to savings goal">
                    <div class="progress-track">
                        <div class="progress-fill" style="--w: 84%"></div>
                    </div>
                    <span class="progress-label">84% of ₹6,30,000 goal</span>
                </div>
            </div>
            <div class="cell">
                <div class="cell-num">04</div>
                <div class="label">Run-rate, annual</div>
                <div class="figure"><span class="currency">₹</span><span>63,42,600.00</span></div>
                <div class="meta"><span class="trend up">▲ 9.1%</span><span>projection</span></div>
                <div class="progress" aria-label="Progress to annual target">
                    <div class="progress-track">
                        <div class="progress-fill alt" style="--w: 58%"></div>
                    </div>
                    <span class="progress-label">58% of ₹1.1 Cr target</span>
                </div>
            </div>
        </section>

        <!-- =================== CONTRIBUTION HEATMAP (GITHUB-STYLE) =================== -->
        <section class="section heatmap-section" aria-label="Spending heatmap, last 12 months">
            <div class="section-head">
                <h2><i class="fa-solid fa-grip section-ic" aria-hidden="true"></i> Spending heatmap.</h2>
                <div class="section-meta">
                    <span>Last 12 months</span>
                    <span aria-hidden="true">·</span>
                    <span>372 days</span>
                </div>
            </div>

            <div class="heatmap">
                <div class="heatmap-months" id="heatmapMonths" aria-hidden="true"></div>
                <div class="heatmap-grid" id="heatmapGrid"></div>
                <div class="heatmap-foot">
                    <div class="heatmap-legend">
                        <span class="legend-label">Less</span>
                        <span class="legend-cell" data-level="0"></span>
                        <span class="legend-cell" data-level="1"></span>
                        <span class="legend-cell" data-level="2"></span>
                        <span class="legend-cell" data-level="3"></span>
                        <span class="legend-cell" data-level="4"></span>
                        <span class="legend-label">More</span>
                    </div>
                </div>
            </div>

            <!-- Hover tooltip (singletons, positioned via JS) -->
            <div class="heatmap-tip" id="heatmapTip" role="tooltip" aria-hidden="true">
                <div class="tip-date">—</div>
                <div class="tip-row">
                    <span class="tip-label">Outflows</span>
                    <span class="tip-value">—</span>
                </div>
                <div class="tip-row">
                    <span class="tip-label">Entries</span>
                    <span class="tip-value">—</span>
                </div>
            </div>
        </section>

        <!-- =================== ANALYTICS SECTION =================== -->
        <section class="section">
            <div class="section-head">
                <h2><i class="fa-solid fa-chart-line section-ic" aria-hidden="true"></i> Analytics.</h2>
                <div class="section-meta">
                    <span>Last 6 months</span>
                    <span aria-hidden="true">·</span>
                    <span>Live</span>
                </div>
            </div>

            <div class="two-up">
                <div class="chart-frame panel">
                    <div class="chart-head">
                        <h3>Cash flow</h3>
                        <span class="tag">Income · Expense</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="incomeExpenseChart" aria-label="Income versus expenses, last six months"></canvas>
                    </div>
                </div>

                <div class="chart-frame panel">
                    <div class="chart-head">
                        <h3>Outflows</h3>
                        <span class="tag">By category</span>
                    </div>
                    <div class="chart-body">
                        <canvas id="categoryChart" aria-label="Spending by category"></canvas>
                    </div>
                    <div class="legend" id="categoryLegend"></div>
                </div>
            </div>
        </section>

        <!-- =================== INCOME VS EXPENSE — GROUPED BARS =================== -->
        <section class="section" aria-label="Income vs Expense, period toggle">
            <div class="section-head">
                <h2><i class="fa-solid fa-chart-column section-ic" aria-hidden="true"></i> Income vs Expense.</h2>
                <div class="period-toggle" role="tablist" aria-label="Period">
                    <button type="button" class="is-on" data-period="monthly" role="tab">Monthly</button>
                    <button type="button" data-period="yearly" role="tab">Yearly</button>
                </div>
            </div>

            <div class="chart-frame panel">
                <div class="chart-head">
                    <h3 id="barChartTitle">Income vs Expense · last 6 months</h3>
                    <span class="tag">Grouped</span>
                </div>
                <div class="chart-body" style="height:280px;">
                    <canvas id="barIncomeExpenseChart" aria-label="Income versus expense, grouped bars"></canvas>
                </div>
            </div>
        </section>

        <!-- =================== BUDGETS — PROGRESS BARS =================== -->
        <section class="section budgets-section" aria-label="Monthly budgets">
            <div class="section-head">
                <h2><i class="fa-solid fa-bullseye section-ic" aria-hidden="true"></i> Budgets.</h2>
                <div class="section-meta">
                    <span>July</span>
                    <span aria-hidden="true">·</span>
                    <span>Auto-rolling</span>
                </div>
            </div>

            <div class="budgets">
                <div class="budget">
                    <div class="budget-head">
                        <div class="budget-cat">
                            <i class="fa-solid fa-utensils" aria-hidden="true"></i>
                            <span>Food &amp; Dining</span>
                        </div>
                        <div class="budget-figures">
                            <span class="budget-spent">₹12,000.00</span>
                            <span class="budget-of">of ₹15,000.00</span>
                        </div>
                    </div>
                    <div class="progress large">
                        <div class="progress-track">
                            <div class="progress-fill" style="--w: 80%"></div>
                        </div>
                        <span class="progress-label">80% used</span>
                    </div>
                </div>

                <div class="budget">
                    <div class="budget-head">
                        <div class="budget-cat">
                            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                            <span>Housing &amp; Utilities</span>
                        </div>
                        <div class="budget-figures">
                            <span class="budget-spent">₹17,800.00</span>
                            <span class="budget-of">of ₹22,500.00</span>
                        </div>
                    </div>
                    <div class="progress large">
                        <div class="progress-track">
                            <div class="progress-fill" style="--w: 79%"></div>
                        </div>
                        <span class="progress-label">79% used</span>
                    </div>
                </div>

                <div class="budget">
                    <div class="budget-head">
                        <div class="budget-cat">
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                            <span>Shopping &amp; Retail</span>
                        </div>
                        <div class="budget-figures">
                            <span class="budget-spent">₹9,000.00</span>
                            <span class="budget-of">of ₹8,000.00</span>
                        </div>
                    </div>
                    <div class="progress large">
                        <div class="progress-track">
                            <div class="progress-fill warn" style="--w: 100%"></div>
                        </div>
                        <span class="progress-label warn">112% · over budget</span>
                    </div>
                </div>

                <div class="budget">
                    <div class="budget-head">
                        <div class="budget-cat">
                            <i class="fa-solid fa-car" aria-hidden="true"></i>
                            <span>Transportation</span>
                        </div>
                        <div class="budget-figures">
                            <span class="budget-spent">₹4,200.00</span>
                            <span class="budget-of">of ₹6,000.00</span>
                        </div>
                    </div>
                    <div class="progress large">
                        <div class="progress-track">
                            <div class="progress-fill" style="--w: 70%"></div>
                        </div>
                        <span class="progress-label">70% used</span>
                    </div>
                </div>

                <div class="budget">
                    <div class="budget-head">
                        <div class="budget-cat">
                            <i class="fa-solid fa-film" aria-hidden="true"></i>
                            <span>Entertainment</span>
                        </div>
                        <div class="budget-figures">
                            <span class="budget-spent">₹2,000.00</span>
                            <span class="budget-of">of ₹3,000.00</span>
                        </div>
                    </div>
                    <div class="progress large">
                        <div class="progress-track">
                            <div class="progress-fill" style="--w: 67%"></div>
                        </div>
                        <span class="progress-label">67% used</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== LEDGER — THE SIGNATURE =================== -->
        <section class="section ledger-section" aria-label="Recent transactions">
            <div class="section-head">
                <h2><i class="fa-solid fa-book section-ic" aria-hidden="true"></i> The ledger.</h2>
                <div class="section-meta">
                    <span>Real-time</span>
                    <span aria-hidden="true">·</span>
                    <span>6 entries</span>
                </div>
            </div>

            <div class="ledger">

                <div class="filters" role="tablist" aria-label="Filter by category">
                    <button class="is-on" data-filter="all">All</button>
                    <button data-filter="income">Income</button>
                    <button data-filter="Food & Dining">Food & Dining</button>
                    <button data-filter="Housing & Utilities">Housing & Utilities</button>
                    <button data-filter="Entertainment">Entertainment</button>
                    <button data-filter="Transportation">Transportation</button>
                </div>

                <div class="led-grid head" aria-hidden="true">
                    <span class="col-label">When</span>
                    <span class="col-label">Merchant &amp; note</span>
                    <span class="col-label">Category</span>
                    <span class="col-label col-num">Method</span>
                    <span class="col-label col-num">Amount · Balance</span>
                </div>

                <div id="ledgerBody">

                    <div class="led-grid" data-category="Food & Dining">
                        <span class="when"><span class="day">28 Jul</span>15:45</span>
                        <span class="col-merch"><strong>Chai Point</strong> Evening chai &amp; samosa</span>
                        <span class="cat"><i class="fa-solid fa-utensils" aria-hidden="true"></i> Food &amp; Dining</span>
                        <span class="method">UPI ··4182</span>
                        <span class="amount-wrap">
                            <span class="amount">− ₹180.00</span>
                            <span class="balance">₹12,84,370.00</span>
                        </span>
                    </div>

                    <div class="led-grid" data-category="Food & Dining">
                        <span class="when"><span class="day">28 Jul</span>14:30</span>
                        <span class="col-merch"><strong>BigBasket</strong> Weekly groceries</span>
                        <span class="cat"><i class="fa-solid fa-utensils" aria-hidden="true"></i> Food &amp; Dining</span>
                        <span class="method">UPI ··4182</span>
                        <span class="amount-wrap">
                            <span class="amount">− ₹11,800.50</span>
                            <span class="balance">₹12,84,550.00</span>
                        </span>
                    </div>

                    <div class="led-grid" data-category="Income">
                        <span class="when"><span class="day">25 Jul</span>09:00</span>
                        <span class="col-merch"><strong>Tech Corp Payroll</strong> Salary, July</span>
                        <span class="cat"><i class="fa-solid fa-money-bill-wave" aria-hidden="true"></i> Income</span>
                        <span class="method">NEFT ··0019</span>
                        <span class="amount-wrap">
                            <span class="amount pos">+ ₹4,50,000.00</span>
                            <span class="balance">₹12,98,800.00</span>
                        </span>
                    </div>

                    <div class="led-grid" data-category="Housing & Utilities">
                        <span class="when"><span class="day">22 Jul</span>10:14</span>
                        <span class="col-merch"><strong>Tata Power</strong> Electric, Jul</span>
                        <span class="cat"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Housing &amp; Utilities</span>
                        <span class="method">NEFT ··0019</span>
                        <span class="amount-wrap">
                            <span class="amount">− ₹8,240.00</span>
                            <span class="balance">₹8,48,800.00</span>
                        </span>
                    </div>

                    <div class="led-grid" data-category="Entertainment">
                        <span class="when"><span class="day">20 Jul</span>18:12</span>
                        <span class="col-merch"><strong>Netflix</strong> Standard plan</span>
                        <span class="cat"><i class="fa-solid fa-film" aria-hidden="true"></i> Entertainment</span>
                        <span class="method">UPI ··4182</span>
                        <span class="amount-wrap">
                            <span class="amount">− ₹649.00</span>
                            <span class="balance">₹8,58,640.00</span>
                        </span>
                    </div>

                    <div class="led-grid" data-category="Transportation">
                        <span class="when"><span class="day">18 Jul</span>11:30</span>
                        <span class="col-merch"><strong>Rapido</strong> Ride to Kempegowda Airport, T2</span>
                        <span class="cat"><i class="fa-solid fa-car" aria-hidden="true"></i> Transportation</span>
                        <span class="method">UPI</span>
                        <span class="amount-wrap">
                            <span class="amount">− ₹1,420.00</span>
                            <span class="balance">₹8,60,639.00</span>
                        </span>
                    </div>

                </div>

                <div class="ledger-foot">
                    <span class="filing">Filed 28 Jul 2026 — Page 01 of 01</span>
                    <span class="muted">End of entries.</span>
                </div>
            </div>
        </section>

    </main>
</div>

<!-- =================== ADD TRANSACTION MODAL =================== -->
<div class="scrim" id="txModal" aria-hidden="true">
    <div class="sheet" role="dialog" aria-labelledby="sheetTitle">
        <div class="sheet-head">
            <div>
                <span class="kicker-num">§ 03</span>
                <h3 id="sheetTitle">New entry</h3>
            </div>
            <button type="button" class="close" onclick="closeModal()" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="toggle-pair" role="tablist">
            <button type="button" id="typeExpenseBtn" class="is-on expense" onclick="setTxType('expense')">Expense</button>
            <button type="button" id="typeIncomeBtn" class="income" onclick="setTxType('income')">Income</button>
        </div>

        <form id="txForm" onsubmit="handleAddTransaction(event)">
            <div class="field">
                <div class="field-row"><label for="txTitle">Merchant or note</label></div>
                <div class="input-shell">
                    <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                    <input type="text" id="txTitle" placeholder="e.g. Trader Joe's, Rent, Salary" required>
                </div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <div class="field-row"><label for="txAmount">Amount</label></div>
                    <div class="input-shell">
                        <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i>
                        <input type="number" step="0.01" id="txAmount" placeholder="0.00" required>
                    </div>
                </div>

                <div class="field">
                    <div class="field-row"><label for="txCategory">Category</label></div>
                    <div class="input-shell">
                        <i class="fa-solid fa-tag" aria-hidden="true"></i>
                        <select id="txCategory">
                            <option value="Food & Dining">Food & Dining</option>
                            <option value="Housing & Utilities">Housing & Utilities</option>
                            <option value="Shopping & Retail">Shopping & Retail</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Income">Income / Salary</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="field-row"><label for="txMethod">Payment method</label></div>
                <div class="input-shell">
                    <i class="fa-regular fa-credit-card" aria-hidden="true"></i>
                    <select id="txMethod">
                        <option value="UPI ··4182">UPI ··4182</option>
                        <option value="RuPay ··2207">RuPay ··2207</option>
                        <option value="NEFT ··0019">NEFT ··0019</option>
                        <option value="IMPS">IMPS</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="submit">
                <i class="fa-solid fa-feather" aria-hidden="true"></i>
                <span>File entry</span>
            </button>
        </form>
    </div>
</div>

<div id="toastContainer" class="toast-stack" aria-live="polite"></div>

<script src="script.js"></script>
<script src="app.js"></script>
</body>
</html>