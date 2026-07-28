# expense_tracker

A personal expense tracking dashboard built with PHP, Chart.js, and a GitHub-style contribution heatmap. Dark editorial aesthetic with Indian Rupee (₹) currency formatting.

## Features

- **GitHub-style spending heatmap** — 53×7 cells covering 12 months, hover tooltips show daily outflow
- **KPI dashboard** — net balance, income, outflows, savings, with mini bar-chart sparklines
- **Budget tracking** — 5 categories with color-coded progress bars
- **Editable ledger** — filterable transaction list with add-transaction modal
- **Default dark mode** — OKLCH-themed, hairline rules, no shadows

## Setup

1. Drop the folder into `xampp/htdocs/`.
2. Start Apache + MySQL from the XAMPP control panel.
3. Open `http://localhost/EXPENSE%20TRACKING%20WEBSITE/`.
4. The dashboard renders immediately — no login required.

## Stack

- PHP (no framework, just session helpers in `config.php`)
- Vanilla JavaScript (no build step)
- Chart.js for the line and doughnut charts
- Font Awesome 6 for icons
- Geist + Geist Mono + Instrument Serif for typography

## File layout

```
index.php         Redirects to dashboard.php
dashboard.php     Main page
config.php        Session + DB config
style.css         Theme tokens + all styling
app.js            Heatmap, charts, modal, transactions
script.js         Toast helper
logout.php        Clears session
```

## Currency

All amounts are in **Indian Rupees (₹)** with lakh-grouping (e.g. `₹12,84,550.00`). The `formatINR()` helper in `app.js` handles the conversion.