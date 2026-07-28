/**
 * ApexSpend — Dashboard Engine (Dark Editorial)
 * Handles new transaction modal, ledger calculations, filter chips,
 * Chart.js initialization, and the GitHub-style spending heatmap.
 */

let currentType = 'expense';
let balance = 12845.50;
let income  = 8500.00;
let expense = 3214.50;

// ----- Modal Controls -----
function openModal() {
    const m = document.getElementById('txModal');
    if (!m) return;
    m.classList.add('is-on');
    m.setAttribute('aria-hidden', 'false');
    setTimeout(() => {
        const f = document.getElementById('txTitle');
        if (f) f.focus();
    }, 60);
}

function closeModal() {
    const m = document.getElementById('txModal');
    if (!m) return;
    m.classList.remove('is-on');
    m.setAttribute('aria-hidden', 'true');
}

function setTxType(type) {
    currentType = type;
    const exp = document.getElementById('typeExpenseBtn');
    const inc = document.getElementById('typeIncomeBtn');
    if (!exp || !inc) return;
    if (type === 'expense') {
        exp.className = 'is-on expense';
        inc.className = 'income';
    } else {
        inc.className = 'is-on income';
        exp.className = 'expense';
    }
}

// ----- Toast Notification Helper -----
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    let icon = 'fa-circle-info';
    if (type === 'success') icon = 'fa-circle-check';
    if (type === 'error')   icon = 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}" aria-hidden="true"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}

// ----- Add Transaction Handler -----
function handleAddTransaction(e) {
    e.preventDefault();
    const title    = document.getElementById('txTitle').value.trim();
    const amount   = parseFloat(document.getElementById('txAmount').value);
    const category = document.getElementById('txCategory').value;
    const method   = document.getElementById('txMethod').value;

    if (!title || isNaN(amount) || amount <= 0) {
        showToast('Enter a valid title and a positive amount.', 'error');
        return;
    }

    const isExp = currentType === 'expense';
    const sign  = isExp ? '−' : '+';

    if (isExp) { expense += amount; balance -= amount; }
    else       { income  += amount; balance += amount; }

    const balEl = document.getElementById('totalBalance');
    const incEl = document.getElementById('totalIncome');
    const expEl = document.getElementById('totalExpenses');
    const savEl = document.getElementById('totalSavings');
    const fmt  = (n) => formatINR(n);

    if (balEl) balEl.textContent = fmt(balance);
    if (incEl) incEl.textContent = fmt(income);
    if (expEl) expEl.textContent = fmt(expense);
    if (savEl) savEl.textContent = fmt(income - expense);

    const now = new Date();
    const day = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    const time = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false });
    const icon = isExp ? categoryIcon(category) : 'fa-money-bill-wave';
    const note = isExp ? 'Filed now' : 'Income';

    const row = document.createElement('div');
    row.className = 'led-grid';
    row.setAttribute('data-category', category);
    row.innerHTML = `
        <span class="when"><span class="day">${day}</span>${time}</span>
        <span class="col-merch"><strong>${escapeHtml(title)}</strong> ${escapeHtml(note)}</span>
        <span class="cat"><i class="fa-solid ${icon}" aria-hidden="true"></i> ${escapeHtml(category)}</span>
        <span class="method">${escapeHtml(method)}</span>
        <span class="amount-wrap">
            <span class="amount${isExp ? '' : ' pos'}">${sign} ₹${formatINR(amount)}</span>
            <span class="balance">₹${formatINR(balance)}</span>
        </span>
    `;

    const body = document.getElementById('ledgerBody');
    if (body) body.insertBefore(row, body.firstChild);

    // Also update the heatmap cell for today, if the heatmap is rendered
    if (typeof updateHeatmapDay === 'function' && isExp) {
        updateHeatmapDay(now, amount);
    }

    document.getElementById('txForm').reset();
    closeModal();
    showToast(`Filed “${title}” for ${sign} ₹${formatINR(amount)}.`, 'success');
}

function categoryIcon(cat) {
    const map = {
        'Food & Dining': 'fa-utensils',
        'Housing & Utilities': 'fa-bolt',
        'Shopping & Retail': 'fa-bag-shopping',
        'Transportation': 'fa-car',
        'Entertainment': 'fa-film',
        'Income': 'fa-money-bill-wave',
        'Health & Wellness': 'fa-heart-pulse'
    };
    return map[cat] || 'fa-tag';
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

// ----- Indian Rupee formatter -----
// 1234567.50 → "12,34,567.50"  (lakh/crore grouping)
function formatINR(n) {
    const num = Number(n) || 0;
    const fixed = num.toFixed(2);
    const parts = fixed.split('.');
    const intPart = parts[0];
    const decPart = parts[1];

    if (intPart.length <= 3) return intPart + '.' + decPart;

    // First group = last 3 digits, then chunks of 2 working leftward.
    const last3 = intPart.slice(-3);
    const rest  = intPart.slice(0, -3);
    const grouped = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
    return grouped + ',' + last3 + '.' + decPart;
}

// ----- Wire Category Filters -----
function wireFilters() {
    const buttons = document.querySelectorAll('.filters button');
    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            buttons.forEach((b) => b.classList.remove('is-on'));
            btn.classList.add('is-on');
            const filter = btn.getAttribute('data-filter');
            const rows = document.querySelectorAll('#ledgerBody .led-grid');
            rows.forEach((r) => {
                const cat = r.getAttribute('data-category') || '';
                if (filter === 'all' || cat === filter) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        });
    });
}

// ============================================================
// SPENDING HEATMAP — GitHub-style 12-month contribution grid
// ============================================================

const HEATMAP_LEVELS = ['#161616', '#0e2e1a', '#14532d', '#16a34a', '#22c55e'];
const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const WEEKDAYS_SHORT = ['', 'Mon', '', 'Wed', '', 'Fri', '']; // render Mon / Wed / Fri like GitHub

// In-memory day store: { 'YYYY-MM-DD': { total, count } }
const heatmapData = {};

// Generate the last 372 days of plausible data so the heatmap looks lived-in.
function seedHeatmapData() {
    const today = new Date(2026, 6, 28); // 28 Jul 2026 (fixed demo date)
    const totalDays = 372;
    const start = new Date(today);
    start.setDate(start.getDate() - (totalDays - 1));

    // Seeded PRNG so the demo looks consistent across reloads
    let seed = 42;
    const rand = () => {
        seed = (seed * 9301 + 49297) % 233280;
        return seed / 233280;
    };

    for (let i = 0; i < totalDays; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const key = isoDate(d);

        // About 22% of days have zero outflows; the rest spread across 4 levels
        const r = rand();
        if (r < 0.22) {
            heatmapData[key] = { total: 0, count: 0 };
        } else {
            // Most days cluster around low spend; heavy days are rare
            const base = rand();
            let total, count;
            if (base < 0.45) {
                total = Math.round(1500 + rand() * 6000);    // ₹1.5k – ₹7.5k
                count = 1 + Math.floor(rand() * 2);
            } else if (base < 0.78) {
                total = Math.round(7500 + rand() * 10000);   // ₹7.5k – ₹17.5k
                count = 1 + Math.floor(rand() * 3);
            } else if (base < 0.94) {
                total = Math.round(17500 + rand() * 15000);  // ₹17.5k – ₹32.5k
                count = 2 + Math.floor(rand() * 3);
            } else {
                total = Math.round(32500 + rand() * 28000);  // ₹32.5k – ₹60.5k
                count = 3 + Math.floor(rand() * 3);
            }
            heatmapData[key] = { total, count };
        }
    }

    // Force some interesting recent days to match the dashboard narrative
    const force = [
        { d: '2026-07-28', total: 11800.50, count: 1 },  // today, BigBasket
        { d: '2026-07-25', total: 450000.00, count: 1, isIncome: true },
        { d: '2026-07-22', total: 8240.00,  count: 1 },
        { d: '2026-07-20', total: 649.00,   count: 1 },
        { d: '2026-07-18', total: 1420.00,  count: 1 },
        { d: '2026-07-14', total: 28000.00, count: 3 },
        { d: '2026-07-04', total: 42000.00, count: 4 },
    ];
    force.forEach(f => { heatmapData[f.d] = { total: f.total, count: f.count }; });
}

function isoDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function levelFor(total) {
    if (total <= 0)     return 0;
    if (total < 7500)   return 1;   // < ₹7,500
    if (total < 17500)  return 2;   // < ₹17,500
    if (total < 32500)  return 3;   // < ₹32,500
    return 4;                       // ₹32,500+
}

// Build the heatmap DOM.
// The grid is 53 columns (weeks) × 7 rows (weekdays). The first column may
// be padded so the grid starts on the correct weekday.
function buildHeatmap() {
    seedHeatmapData();

    const grid   = document.getElementById('heatmapGrid');
    const months = document.getElementById('heatmapMonths');
    if (!grid || !months) return;

    grid.innerHTML = '';
    months.innerHTML = '';

    const end   = new Date(2026, 6, 28);
    const totalDays = 372;
    const start = new Date(end);
    start.setDate(start.getDate() - (totalDays - 1));

    // Walk back to the previous Sunday so the first column starts a week cleanly
    const firstDay = new Date(start);
    const dayOfWeek = firstDay.getDay(); // 0 = Sunday
    const firstColumnDate = new Date(firstDay);
    firstColumnDate.setDate(firstColumnDate.getDate() - dayOfWeek);

    // Column count
    const lastDay  = new Date(end);
    const diffDays = Math.ceil((lastDay - firstColumnDate) / 86400000) + 1;
    const cols = Math.ceil(diffDays / 7);

    const today = isoDate(end);

    const tipDateEl  = document.querySelector('#heatmapTip .tip-date');
    const tipOutEl   = document.querySelector('#heatmapTip .tip-row:nth-child(2) .tip-value');
    const tipCountEl = document.querySelector('#heatmapTip .tip-row:nth-child(3) .tip-value');

    const cells = []; // for month-label positioning
    let lastMonth = -1;

    for (let c = 0; c < cols; c++) {
        const col = document.createElement('div');
        col.className = 'heatmap-col';
        for (let r = 0; r < 7; r++) {
            const cellDate = new Date(firstColumnDate);
            cellDate.setDate(firstColumnDate.getDate() + (c * 7) + r);

            // Skip days before the window
            if (cellDate < start || cellDate > end) {
                const blank = document.createElement('span');
                blank.className = 'hm-cell hm-blank';
                col.appendChild(blank);
                continue;
            }

            const key   = isoDate(cellDate);
            const entry = heatmapData[key] || { total: 0, count: 0 };
            const lvl   = entry.total === 0 ? 0 : levelFor(entry.total);

            const cell = document.createElement('span');
            cell.className = 'hm-cell';
            cell.style.background = HEATMAP_LEVELS[lvl];
            cell.dataset.date   = key;
            cell.dataset.total  = entry.total;
            cell.dataset.count  = entry.count;

            if (key === today) {
                cell.classList.add('is-today');
                cell.style.outline = '1px solid #f59e0b';
            }

            // Weekday label cell on the left of the first column for rows 1,3,5
            if (c === 0 && (r === 1 || r === 3 || r === 5)) {
                cell.classList.add('has-label');
                const lbl = document.createElement('span');
                lbl.className = 'hm-weekday';
                lbl.textContent = WEEKDAYS_SHORT[r];
                cell.appendChild(lbl);
            }

            cell.addEventListener('mouseenter', (ev) => showHeatmapTip(ev.currentTarget, tipDateEl, tipOutEl, tipCountEl));
            cell.addEventListener('mouseleave', hideHeatmapTip);
            cell.addEventListener('focus',      (ev) => showHeatmapTip(ev.currentTarget, tipDateEl, tipOutEl, tipCountEl));
            cell.addEventListener('blur',       hideHeatmapTip);
            cell.tabIndex = 0;
            cell.setAttribute('aria-label', `${formatLongDate(cellDate)}, ${entry.total === 0 ? 'no outflows' : '₹' + formatINR(entry.total) + ' across ' + entry.count + (entry.count === 1 ? ' entry' : ' entries')}`);

            col.appendChild(cell);
            cells.push({ date: cellDate, lvl });
        }
        grid.appendChild(col);
    }

    // Render month labels by detecting month-change between columns
    let prev = -1;
    let labelCols = []; // {month, colIndex}
    cells.forEach(({ date }) => {
        const m = date.getMonth();
        if (m !== prev && date.getDate() <= 7) {
            labelCols.push({ month: m, date });
            prev = m;
        }
    });

    labelCols.forEach(({ month, date }) => {
        const idx = Math.floor((date - firstColumnDate) / 86400000 / 7);
        const m = document.createElement('span');
        m.className = 'heatmap-month';
        m.style.gridColumn = `${idx + 1} / span 1`;
        m.textContent = MONTH_NAMES[month];
        months.appendChild(m);
    });
}

function formatLongDate(d) {
    return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function showHeatmapTip(cell, tipDateEl, tipOutEl, tipCountEl) {
    const tip = document.getElementById('heatmapTip');
    if (!tip) return;

    const total = parseFloat(cell.dataset.total || '0');
    const count = parseInt(cell.dataset.count || '0', 10);
    const key   = cell.dataset.date;

    const date = new Date(key + 'T00:00:00');
    tipDateEl.textContent  = formatLongDate(date);
    tipOutEl.textContent   = total === 0 ? '—' : '− ₹' + formatINR(total);
    tipCountEl.textContent = count + (count === 1 ? ' entry' : ' entries');

    // Color the outflow number
    tipOutEl.style.color = total === 0 ? 'var(--whisper)' : 'var(--gain)';

    tip.setAttribute('aria-hidden', 'false');
    tip.style.opacity = '1';

    // Position above the cell
    const rect = cell.getBoundingClientRect();
    const tipRect = tip.getBoundingClientRect();
    const scrollX = window.scrollX || window.pageXOffset;
    const scrollY = window.scrollY || window.pageYOffset;
    const left = rect.left + scrollX + (rect.width / 2) - (tipRect.width / 2);
    const top  = rect.top + scrollY - tipRect.height - 10;

    tip.style.left = Math.max(8, Math.min(left, document.documentElement.clientWidth - tipRect.width - 8)) + 'px';
    tip.style.top  = top + 'px';
}

function hideHeatmapTip() {
    const tip = document.getElementById('heatmapTip');
    if (!tip) return;
    tip.style.opacity = '0';
    tip.setAttribute('aria-hidden', 'true');
}

// Called when a new transaction is filed — update today's cell.
function updateHeatmapDay(dateObj, addedAmount) {
    const key = isoDate(dateObj);
    const cell = document.querySelector(`.hm-cell[data-date="${key}"]`);
    if (!cell) return;
    const prevTotal = parseFloat(cell.dataset.total || '0');
    const prevCount = parseInt(cell.dataset.count || '0', 10);
    const newTotal  = prevTotal + addedAmount;
    const newCount  = prevCount + 1;
    cell.dataset.total = newTotal;
    cell.dataset.count = newCount;
    cell.style.background = HEATMAP_LEVELS[newTotal === 0 ? 0 : levelFor(newTotal)];
    heatmapData[key] = { total: newTotal, count: newCount };
}

// ----- Charts Initialization -----

function initCharts() {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Geist Mono', ui-monospace, monospace";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#8a8a8a';

    const lineCtx = document.getElementById('incomeExpenseChart');
    if (lineCtx) {
        const ctx = lineCtx.getContext('2d');

        const grad1 = ctx.createLinearGradient(0, 0, 0, 260);
        grad1.addColorStop(0, 'rgba(245, 158, 11, 0.35)');
        grad1.addColorStop(1, 'rgba(245, 158, 11, 0)');

        const grad2 = ctx.createLinearGradient(0, 0, 0, 260);
        grad2.addColorStop(0, 'rgba(138, 138, 138, 0.25)');
        grad2.addColorStop(1, 'rgba(138, 138, 138, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [
                    {
                        label: 'Income',
                        data: [650000, 720000, 680000, 800000, 820000, 850000],
                        borderColor: '#f59e0b',
                        backgroundColor: grad1,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#0a0a0a',
                        pointBorderWidth: 2,
                        fill: true,
                        tension: 0.32
                    },
                    {
                        label: 'Expense',
                        data: [310000, 340000, 290000, 380000, 310000, 321450],
                        borderColor: '#8a8a8a',
                        backgroundColor: grad2,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#8a8a8a',
                        pointBorderColor: '#0a0a0a',
                        pointBorderWidth: 2,
                        borderDash: [4, 4],
                        fill: true,
                        tension: 0.32
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            font: { family: "'Geist Mono', monospace", size: 11 },
                            color: '#8a8a8a'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleFont: { family: "'Geist Mono', monospace", size: 10 },
                        bodyFont: { family: "'Geist Mono', monospace", size: 12 },
                        padding: 10,
                        borderColor: '#2a2a2a',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: { label: (ctx) => `${ctx.dataset.label}: ₹${formatINR(ctx.parsed.y)}` }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#8a8a8a', font: { family: "'Geist Mono', monospace", size: 10 } } },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)', drawTicks: false },
                        ticks: {
                            color: '#8a8a8a',
                            font: { family: "'Geist Mono', monospace", size: 10 },
                            callback: (v) => {
                                if (v >= 100000) return '₹' + Math.round(v / 100000) + 'L';
                                if (v >= 1000)    return '₹' + Math.round(v / 1000)    + 'k';
                                return '₹' + v;
                            }
                        }
                    }
                }
            }
        });
    }

    const donutCtx = document.getElementById('categoryChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Food & Dining', 'Housing & Utilities', 'Shopping', 'Transport', 'Entertainment', 'Other'],
                datasets: [{
                    data: [120000, 95000, 45000, 35000, 20000, 6450],
                    backgroundColor: [
                        '#f5f5f5',
                        '#f59e0b',
                        '#a855f7',
                        '#4ade80',
                        '#8a8a8a',
                        '#2a2a2a'
                    ],
                    borderColor: '#0a0a0a',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleFont: { family: "'Geist Mono', monospace", size: 10 },
                        bodyFont: { family: "'Geist Mono', monospace", size: 12 },
                        padding: 10,
                        borderColor: '#2a2a2a',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: { label: (ctx) => `${ctx.label}: ₹${formatINR(ctx.parsed)}` }
                    }
                }
            }
        });

        const legend = document.getElementById('categoryLegend');
        if (legend) {
            legend.innerHTML = '';
            const cats = ['Food & Dining', 'Housing & Utilities', 'Shopping', 'Transport', 'Entertainment', 'Other'];
            const cols = ['#f5f5f5', '#f59e0b', '#a855f7', '#4ade80', '#8a8a8a', '#2a2a2a'];
            cats.forEach((c, i) => {
                const item = document.createElement('span');
                item.className = 'item';
                item.innerHTML = `<span class="swatch" style="background:${cols[i]}"></span>${c}`;
                legend.appendChild(item);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    wireFilters();
    buildHeatmap();
    initCharts();

    const scrim = document.getElementById('txModal');
    if (scrim) {
        scrim.addEventListener('click', (e) => {
            if (e.target === scrim) closeModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
});