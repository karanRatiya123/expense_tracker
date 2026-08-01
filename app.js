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
async function handleAddTransaction(e) {
    e.preventDefault();
    const title    = document.getElementById('txTitle').value.trim();
    const amount   = parseFloat(document.getElementById('txAmount').value);
    const category = document.getElementById('txCategory').value;
    const method   = document.getElementById('txMethod').value;
    const type     = currentType;
    const isExp    = currentType === 'expense';

    if (!title || isNaN(amount) || amount <= 0) {
        showToast('Enter a valid title and a positive amount.', 'error');
        return;
    }

    // Prepare data
    const formData = new FormData();
    formData.append('title', title);
    formData.append('amount', amount);
    formData.append('category', category);
    formData.append('method', method);
    formData.append('type', type);

    try {
        const response = await fetch('api_add_transaction.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(`Filed “${title}” successfully. Reloading...`, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(`Failed: ${result.error}`, 'error');
        }
    } catch (err) {
        showToast('Error saving transaction.', 'error');
    }
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

// GitHub dark contribution greens
const HEATMAP_LEVELS = ['#161b22', '#0e4429', '#006d32', '#26a641', '#39d353'];
const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// In-memory day store: { 'YYYY-MM-DD': { total, count } }
const heatmapData = {};

// Generate the last ~12 months of plausible data so the heatmap looks lived-in.
function seedHeatmapData() {
    if (typeof serverTransactions !== 'undefined' && Array.isArray(serverTransactions)) {
        serverTransactions.forEach(t => {
            if (t.type === 'expense') {
                const d = new Date(t.date.replace(' ', 'T'));
                if (isNaN(d.getTime())) return;
                
                const yyyy = d.getFullYear();
                const mm   = String(d.getMonth() + 1).padStart(2, '0');
                const dd   = String(d.getDate()).padStart(2, '0');
                const key  = `${yyyy}-${mm}-${dd}`;
                
                if (!heatmapData[key]) heatmapData[key] = { total: 0, count: 0 };
                heatmapData[key].total += parseFloat(t.amount);
                heatmapData[key].count += 1;
            }
        });
    }
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

// Build the heatmap DOM — GitHub contribution graph layout:
//   rows = Sun→Sat, columns = weeks, left labels = Mon / Wed / Fri only
function buildHeatmap() {
    seedHeatmapData();

    const grid   = document.getElementById('heatmapGrid');
    const months = document.getElementById('heatmapMonths');
    if (!grid || !months) return;

    grid.innerHTML = '';
    months.innerHTML = '';

    const end = new Date(); // Use actual current date
    const totalDays = 371;
    const start = new Date(end);
    start.setDate(start.getDate() - (totalDays - 1));

    // Align first column to Sunday (GitHub default week start)
    const firstColumnDate = new Date(start);
    firstColumnDate.setDate(firstColumnDate.getDate() - firstColumnDate.getDay());

    const diffDays = Math.floor((end - firstColumnDate) / 86400000) + 1;
    const cols = Math.ceil(diffDays / 7);
    const today = isoDate(end);

    const tipDateEl  = document.querySelector('#heatmapTip .tip-date');
    const tipOutEl   = document.querySelector('#heatmapTip .tip-row:nth-child(2) .tip-value');
    const tipCountEl = document.querySelector('#heatmapTip .tip-row:nth-child(3) .tip-value');

    // Track first column index for each month (for top labels)
    const monthAtCol = [];

    for (let c = 0; c < cols; c++) {
        const col = document.createElement('div');
        col.className = 'heatmap-col';
        col.setAttribute('role', 'rowgroup');

        let monthForCol = -1;

        for (let r = 0; r < 7; r++) {
            const cellDate = new Date(firstColumnDate);
            cellDate.setDate(firstColumnDate.getDate() + (c * 7) + r);

            // Out-of-range padding cells (still occupy the slot)
            if (cellDate < start || cellDate > end) {
                const blank = document.createElement('span');
                blank.className = 'hm-cell hm-blank';
                blank.setAttribute('aria-hidden', 'true');
                col.appendChild(blank);
                continue;
            }

            if (monthForCol < 0) monthForCol = cellDate.getMonth();

            const key   = isoDate(cellDate);
            const entry = heatmapData[key] || { total: 0, count: 0 };
            const lvl   = entry.total === 0 ? 0 : levelFor(entry.total);

            const cell = document.createElement('a');
            cell.className = 'hm-cell';
            cell.href = 'analytics.php?date=' + encodeURIComponent(key);
            cell.style.background = HEATMAP_LEVELS[lvl];
            cell.dataset.date  = key;
            cell.dataset.total = entry.total;
            cell.dataset.count = entry.count;
            cell.setAttribute('role', 'gridcell');

            if (key === today) cell.classList.add('is-today');

            cell.addEventListener('mouseenter', (ev) => showHeatmapTip(ev.currentTarget, tipDateEl, tipOutEl, tipCountEl));
            cell.addEventListener('mouseleave', hideHeatmapTip);
            cell.addEventListener('focus',      (ev) => showHeatmapTip(ev.currentTarget, tipDateEl, tipOutEl, tipCountEl));
            cell.addEventListener('blur',       hideHeatmapTip);
            cell.setAttribute(
                'aria-label',
                `${formatLongDate(cellDate)}, ${entry.total === 0 ? 'no outflows' : '₹' + formatINR(entry.total) + ' across ' + entry.count + (entry.count === 1 ? ' entry' : ' entries')}`
            );

            col.appendChild(cell);
        }

        monthAtCol[c] = monthForCol;
        grid.appendChild(col);
    }

    // Month labels: place over the first week that contains day 1 of that month
    // (or the first visible day of a month if the window starts mid-month)
    const monthLabelCols = [];
    let prevMonth = -1;
    for (let c = 0; c < cols; c++) {
        let labelMonth = -1;
        for (let r = 0; r < 7; r++) {
            const d = new Date(firstColumnDate);
            d.setDate(firstColumnDate.getDate() + (c * 7) + r);
            if (d < start || d > end) continue;
            if (d.getDate() === 1) {
                labelMonth = d.getMonth();
                break;
            }
        }
        // First column of the range: show its month even if not the 1st
        if (labelMonth < 0 && c === 0 && monthAtCol[0] >= 0) {
            labelMonth = monthAtCol[0];
        }
        if (labelMonth < 0 || labelMonth === prevMonth) continue;
        prevMonth = labelMonth;
        monthLabelCols.push({ col: c, month: labelMonth });
    }

    monthLabelCols.forEach(({ col, month }) => {
        const m = document.createElement('span');
        m.className = 'heatmap-month';
        m.dataset.col = String(col);
        m.textContent = MONTH_NAMES[month];
        months.appendChild(m);
    });

    // Position labels from measured column widths (full-width flex grid)
    positionHeatmapMonths();
    if (!window.__heatmapMonthResizeBound) {
        window.__heatmapMonthResizeBound = true;
        window.addEventListener('resize', () => {
            clearTimeout(window.__heatmapMonthResizeT);
            window.__heatmapMonthResizeT = setTimeout(positionHeatmapMonths, 80);
        });
    }
}

// Align month labels to week columns after layout / resize
function positionHeatmapMonths() {
    const grid   = document.getElementById('heatmapGrid');
    const months = document.getElementById('heatmapMonths');
    if (!grid || !months) return;

    const weekCols = grid.querySelectorAll('.heatmap-col');
    if (!weekCols.length) return;

    const gridLeft = grid.getBoundingClientRect().left;
    months.querySelectorAll('.heatmap-month').forEach((label) => {
        const idx = parseInt(label.dataset.col || '0', 10);
        const col = weekCols[idx];
        if (!col) return;
        const left = col.getBoundingClientRect().left - gridLeft;
        label.style.left = Math.max(0, left) + 'px';
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

    // Position above the cell (fixed coords = viewport)
    const rect = cell.getBoundingClientRect();
    const tipRect = tip.getBoundingClientRect();
    const left = rect.left + (rect.width / 2) - (tipRect.width / 2);
    const top  = rect.top - tipRect.height - 10;

    tip.style.left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8)) + 'px';
    tip.style.top  = Math.max(8, top) + 'px';
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

// ============================================================
// GROUPED BAR CHART — Income vs Expense with Monthly/Yearly toggle
// ============================================================

const BAR_DATA = {
    monthly: {
        labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        income:  [650000, 720000, 680000, 800000, 820000, 850000],
        expense: [310000, 340000, 290000, 380000, 310000, 321450]
    },
    yearly: {
        labels: ['2022', '2023', '2024', '2025', '2026'],
        income:  [6200000, 7050000, 7800000, 8650000, 9300000],
        expense: [3450000, 3720000, 4010000, 4180000, 4320000]
    }
};

let barChart = null;

function initBarChart() {
    const el = document.getElementById('barIncomeExpenseChart');
    if (!el || typeof Chart === 'undefined') return;

    barChart = new Chart(el, {
        type: 'bar',
        data: {
            labels: BAR_DATA.monthly.labels.slice(),
            datasets: [
                {
                    label: 'Income',
                    data: BAR_DATA.monthly.income.slice(),
                    backgroundColor: '#f59e0b',
                    borderColor: '#0a0a0a',
                    borderWidth: 1,
                    borderRadius: 2,
                    barPercentage: 0.78,
                    categoryPercentage: 0.72
                },
                {
                    label: 'Expense',
                    data: BAR_DATA.monthly.expense.slice(),
                    backgroundColor: '#8a8a8a',
                    borderColor: '#0a0a0a',
                    borderWidth: 1,
                    borderRadius: 2,
                    barPercentage: 0.78,
                    categoryPercentage: 0.72
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
                    callbacks: { label: (c) => `${c.dataset.label}: ₹${formatINR(c.parsed.y)}` }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#8a8a8a', font: { family: "'Geist Mono', monospace", size: 10 } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.04)', drawTicks: false },
                    ticks: {
                        color: '#8a8a8a',
                        font: { family: "'Geist Mono', monospace", size: 10 },
                        callback: (v) => {
                            if (v >= 10000000) return '₹' + (v / 10000000).toFixed(1) + 'Cr';
                            if (v >= 100000)   return '₹' + Math.round(v / 100000) + 'L';
                            if (v >= 1000)      return '₹' + Math.round(v / 1000)    + 'k';
                            return '₹' + v;
                        }
                    }
                }
            }
        }
    });
}

function setBarPeriod(period) {
    if (!barChart || !BAR_DATA[period]) return;
    const d = BAR_DATA[period];
    barChart.data.labels = d.labels.slice();
    barChart.data.datasets[0].data = d.income.slice();
    barChart.data.datasets[1].data = d.expense.slice();
    barChart.update();

    const title = document.getElementById('barChartTitle');
    if (title) {
        title.textContent = period === 'monthly'
            ? 'Income vs Expense · last 6 months'
            : 'Income vs Expense · last 5 years';
    }
}

function wireBarToggle() {
    const buttons = document.querySelectorAll('.period-toggle button');
    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            buttons.forEach((b) => b.classList.remove('is-on'));
            btn.classList.add('is-on');
            setBarPeriod(btn.getAttribute('data-period'));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    wireFilters();
    buildHeatmap();
    initCharts();
    initBarChart();
    wireBarToggle();

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