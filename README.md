# Expense Tracker

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

A personal expense tracking dashboard built with PHP, Chart.js, and a GitHub-style contribution heatmap. Dark editorial aesthetic with Indian Rupee (₹) currency formatting.

**Owner & maintainer:** [@karanRatiya123](https://github.com/karanRatiya123)
**Co-developed with:** Claude (Anthropic) — AI pair-programmer

---

## Features

- **GitHub-style spending heatmap** — 53×7 cells covering 12 months, hover tooltips show daily outflow
- **KPI dashboard** — net balance, income, outflows, savings, with mini bar-chart sparklines
- **Analytics** — monthly trend (line) and category breakdown (doughnut)
- **Budget tracking** — 5 categories with color-coded progress bars
- **Editable ledger** — filterable transaction list with add-transaction modal and AJAX submit
- **Auth + profile** — login, signup, profile update, session-based logout
- **Default dark mode** — OKLCH-themed, hairline rules, no shadows

## Stack

- PHP (no framework, just session helpers in `config.php` and `auth.php`)
- Vanilla JavaScript (no build step)
- Chart.js for the line and doughnut charts
- Font Awesome 6 for icons
- Geist + Geist Mono + Instrument Serif for typography
- MySQL via XAMPP

## Setup

1. Drop the folder into `xampp/htdocs/`.
2. Start Apache + MySQL from the XAMPP control panel.
3. Import `database.sql` into MySQL to create the schema.
4. Open `http://localhost/EXPENSE%20TRACKING%20WEBSITE/`.
5. Sign up or log in — the dashboard renders once authenticated.

## Currency

All amounts are in **Indian Rupees (₹)** with lakh-grouping (e.g. `₹12,84,550.00`). The `formatINR()` helper in `app.js` handles the conversion.

---

## Project structure

```
EXPENSE TRACKING WEBSITE/
├── index.php               Entry point — redirects to dashboard.php
│
├── Pages (user-facing views)
│   ├── dashboard.php       Main dashboard: KPI tiles + heatmap + recent activity
│   ├── analytics.php       Monthly trend line chart + category doughnut
│   ├── budgets.php         Per-category budget bars and progress
│   ├── transactions.php    Filterable transaction ledger + add-transaction modal
│   ├── login.php           Login + signup form (combined auth UI)
│   ├── profile.php         Account settings and profile editor
│   └── logout.php          Clears the PHP session
│
├── Backend (PHP)
│   ├── config.php                DB credentials + session bootstrap
│   ├── auth.php                  Login / signup / session check helpers
│   ├── update_profile.php        Profile update endpoint
│   ├── api_add_transaction.php   AJAX endpoint for adding transactions
│   ├── api_update_transaction.php AJAX endpoint for updating transactions
│   ├── api_delete_transaction.php AJAX endpoint for deleting transactions
│   ├── api_get_budgets.php       AJAX endpoint for fetching budgets
│   ├── api_save_budget.php       AJAX endpoint for saving budgets
│   └── api_delete_budget.php     AJAX endpoint for deleting budgets
│
├── Frontend (JavaScript, per-page)
│   ├── app.js              Dashboard: heatmap, KPI sparklines, shared helpers
│   ├── analytics.js        Analytics charts + palette sync with heatmap
│   ├── budgets.js          Budget bar rendering and progress calc
│   ├── transactions.js     Ledger filtering, search, modal wiring
│   ├── profile.js          Profile form submit + validation
│   └── script.js           Legacy toast helper
│
├── Styling
│   └── style.css           Single design-system file: OKLCH tokens, layout, components
│
├── Data
│   └── database.sql        MySQL schema (users, transactions, budgets)
│
└── .gitignore
```

---

## File importance

Ranked by what actually breaks if the file is wrong or missing.

### Critical (breaks the app if removed)

| File | Why it matters |
|---|---|
| `config.php` | DB credentials and session bootstrap — every authenticated page depends on it. |
| `database.sql` | Source of truth for the MySQL schema. No DB = no data. |
| `auth.php` | Login / signup / `require_login()` guard used across pages. |
| `api_add_transaction.php` | The only path for the add-transaction modal to persist data. |
| `api_update_transaction.php` | Endpoint for updating existing transactions. |
| `api_delete_transaction.php` | Endpoint for deleting transactions. |
| `api_get_budgets.php` | API endpoint for fetching budgets data. |
| `api_save_budget.php` | Endpoint for saving budget allocations. |
| `api_delete_budget.php` | Endpoint for deleting budget allocations. |
| `update_profile.php` | Handles every profile-field save. |
| `style.css` | Single source of design tokens — removing it strips all theming. |

### High (core user experience)

| File | Why it matters |
|---|---|
| `dashboard.php` | The default landing page after login. |
| `app.js` | Heatmap + KPI sparklines — the signature visual. |
| `analytics.js` | The line + doughnut charts on `analytics.php`. |
| `budgets.js` | Drives the per-category budget bars. |
| `transactions.js` | Filter, search, and modal submission on the ledger page. |
| `profile.js` | Wires the profile form to `update_profile.php`. |

### Medium (supporting)

| File | Why it matters |
|---|---|
| `index.php` | Redirect entry — replace with a landing page if you ever need one. |
| `analytics.php` | Secondary view; the app still works without it. |
| `budgets.php` | Secondary view; same. |
| `transactions.php` | Secondary view; same. |
| `login.php` | Combined login/signup UI; the auth backend lives in `auth.php`. |
| `profile.php` | Secondary view; same. |
| `logout.php` | One-line session destroy. |
| `script.js` | Legacy toast helper — kept for backward compatibility. |
| `README.md` | Project documentation and setup guide. |
| `.gitignore` | Excludes unnecessary files from version control. |

---

## Architecture notes

- **No framework, no build step.** Each page owns a `.php` view and a matching `.js` controller. This is deliberate: keep the diff small, keep the deploy simple.
- **Shared helpers** live in `app.js` (e.g. `formatINR()`); per-page scripts read from the same globals.
- **Auth is session-based.** `auth.php` exposes `require_login()` and is included at the top of every protected page.
- **AJAX writes** go through small `api_*.php` endpoints — the modal in `transactions.php` posts to `api_add_transaction.php`.

## Conventions

- All amounts stored as DECIMAL in MySQL, formatted to ₹ at the render layer only.
- Dates are ISO (`YYYY-MM-DD`) everywhere except the heatmap tooltip.
- Color tokens are OKLCH in `style.css` — change one variable to retheme.
- JS is plain ES, no modules, no transpiler.

---

## Contributors

- [@karanRatiya123](https://github.com/karanRatiya123) — design, product, backend, frontend
- **Claude (Anthropic)** — AI pair-programmer. Co-authored via `Co-Authored-By: Claude <noreply@anthropic.com>` on commits.

## License

Personal project. No license granted for reuse without permission from the maintainer.
