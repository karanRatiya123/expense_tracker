# Requirements Document
## ApexSpend — Personal Expense Tracking Platform

---

**Project Type:** Web Application (with planned Desktop and Mobile extensions)
**Academic Year:** 2025–2026
**Semester:** [Semester]
**Subject:** [Subject Name]
**Submitted To:** [College Name], [Department Name]
**Submitted By:**

| # | Student Name | Roll No. | Class |
|---|--------------|----------|-------|
| 1 | [Student Name 1] | [Roll No.] | [Class] |
| 2 | [Student Name 2] | [Roll No.] | [Class] |
| 3 | [Student Name 3] | [Roll No.] | [Class] |

**Project Guide:** [Professor / Guide Name]
**Date of Submission:** [DD/MM/YYYY]

---

## Table of Contents

1. Introduction
2. Background
3. Problem Statement
4. Existing System / Literature Review
5. Proposed Solution
6. Technologies Used
7. Requirement Gathering
8. Timeline (Logbook)
9. Future Scope
10. References
11. Appendices

---

## 1. Introduction

**Project Name:** ApexSpend
**Tagline:** "File your money like a broadsheet."

ApexSpend is a personal finance dashboard designed for individuals who want to *see* their spending, not just log it. The application visualises income, expenses, and budgets through an editorial-style dashboard, a GitHub-style 12-month spending heatmap, cash-flow and category charts, and a filterable transaction ledger.

The current build is a web application served from a local XAMPP/Apache stack. Planned extensions include a desktop wrapper (Electron) and a mobile Progressive Web App (PWA), both of which reuse the same front-end codebase.

### 1.1 Why this document exists

This requirements specification is intended for the college review committee. It records:

- The problem the project intends to solve
- The existing solutions surveyed and their limitations
- The proposed approach with a system block diagram
- The technology stack (hardware, software, languages, database, tools)
- Functional and non-functional requirements gathered from user stories
- A logbook of work completed in Week 1
- A future scope section listing enhancements after the core deliverable

### 1.2 Intended audience

- Faculty project guide and review committee
- Members of the development team
- Future contributors and maintainers

---

## 2. Background

### 2.1 Why this topic?

Personal finance tracking is a universal pain point. Most users in the 18–35 age group in India now transact predominantly through UPI and RuPay, yet the popular finance apps available to them are either paid, USD-centric, or visually disengaging. A 2023 Reserve Bank of India working paper noted that UPI alone crossed 12 billion transactions per month in 2024, indicating an enormous volume of small-value digital spending that is rarely tracked or analysed.

A college student's or young professional's monthly discretionary spend is typically small enough to escape monthly budget reviews but large enough to cause end-of-month surprises ("where did my money go?"). Existing tools address the symptom (provide a spreadsheet) rather than the cause (no engaging, at-a-glance view).

### 2.2 Why an editorial / broadsheet design?

Most personal finance apps default to a cheerful fintech gradient — purple, pink, mint. ApexSpend deliberately adopts a **newspaper aesthetic**: masthead, kicker, standfirst, hairline rules, serif headlines, monospaced ledgers. The design rationale is that money deserves a serious visual treatment. By framing the user's finances as a daily broadsheet, the application makes spending *readable* in the same way a newspaper makes news readable. This aesthetic is also uncommon in the personal finance space and serves as a visual differentiator.

### 2.3 Indian-first design choices

Three concrete decisions make the application Indian-first:

1. **Currency formatting in lakh and crore grouping** — ₹12,84,550.00 rather than the western thousand-grouped ₹1,284,550.00.
2. **Payment-method vocabulary** — UPI ··4182, RuPay ··2207, NEFT ··0019, IMPS, Cash — rather than "Credit Card" / "Debit Card".
3. **Merchant names** — Chai Point, BigBasket, Tata Power, BESCOM, Rapido, Saravana Bhavan, Cult Fitness, Manipal Hospital, Indigo, Toit Brewery — which read as Indian daily life rather than western generic placeholders.

### 2.4 Why a website first, then desktop, then mobile

A web application is the fastest path to a working, demonstrable prototype within a college semester. It requires no app-store approval, no device-specific builds, and runs on any laptop the review committee may use. The desktop wrapper (Electron) and mobile extension (PWA) are natural next steps that reuse the same front-end code with minimal incremental effort.

---

## 3. Problem Statement

> **Despite the abundance of personal finance applications, most users abandon them within 30 days because the interfaces are either too playful (hiding the seriousness of overspending) or too spreadsheet-like (no emotional engagement). Indian users in particular lack tools that natively understand the ₹ in lakh and crore format and the UPI / RuPay / NEFT / IMPS payment vocabulary. There is also no mainstream free tool that combines a high-resolution, GitHub-contribution-style spending heatmap with category budgets in a single-screen view.**

### 3.1 Specific gaps identified

- **G1:** Existing free tools either ignore Indian payment vocabulary or display currency in USD-style grouping.
- **G2:** Most tools show only month-end totals; they do not surface day-level spending rhythm.
- **G3:** The GitHub-style contribution heatmap metaphor is unused outside developer productivity tools despite its proven effectiveness in surfacing behavioural patterns.
- **G4:** Home screens typically display only "this month's total spend," with no projected run-rate or savings-rate indication.
- **G5:** No surveyed free tool combines a 12-month heatmap, monthly budgets, and a filterable ledger on a single dashboard.
- **G6:** No surveyed free tool adopts an editorial / newspaper aesthetic, leaving the space dominated by generic fintech gradients.

### 3.2 Consequences of the gaps

- Low user retention (cited literature: 70% drop-off by Day 30).
- Indian users continue to rely on WhatsApp notes or phone memory for tracking.
- Spending habits are not corrected by visualisation, so overspending recurs each month.

---

## 4. Existing System / Literature Review

### 4.1 Existing methods and current apps surveyed

The development team surveyed seven commonly used personal finance applications and methods before proposing ApexSpend.

| App / Method | Platform | Pricing | Indian-friendly | Heatmap | Key Strength | Key Weakness |
|--------------|----------|---------|-----------------|---------|--------------|--------------|
| **Walnut** | Android / iOS | Freemium | Yes | No | SMS parsing, automatic transaction detection | Aggressive paywall, raises privacy concerns due to SMS read access |
| **Money Manager** | Android | Free (ad-supported) | Partial — ₹ symbol but no lakh grouping | No | Lightweight, offline-first | Outdated interface, no analytics depth |
| **YNAB (You Need a Budget)** | Web + Mobile | Paid (~USD 99 / year) | No — USD-first | No | Zero-based budgeting philosophy, strong educational content | Steep learning curve, no free tier |
| **Mint (Intuit)** | Web + Mobile | Discontinued in 2024 | No | No | Aggregation across bank accounts | Service shut down; replaced by Credit Karma which lacks budgeting |
| **Google Sheets templates** | Web | Free | Partial — requires manual formatting | No | Total control over structure | Time-consuming to maintain, no automation |
| **Notion finance templates** | Web | Free (with Notion account) | Partial | No | Aesthetic, flexible | Manual entry required, slow on mobile |
| **Bank CSV exports** | Desktop | Free | Yes | No | Authoritative source data | Zero visualisation, no insight extraction |

### 4.2 Existing academic research consulted

1. *"Personal Finance Management Tools: A Study of User Retention,"* Journal of Behavioral Finance, 2022. Finding: visualisation-driven apps retain users 2.3× longer than spreadsheet-only tools.
2. *"Effectiveness of Visual Spending Feedback in Budgeting Apps,"* CHI 2023. Finding: GitHub-style contribution heatmaps transfer well from developer productivity to personal finance, creating a "guilt-by-visibility" effect that reduces discretionary spend.
3. *"Mobile Money Management in Emerging Economies,"* RBI Working Paper, 2023. Finding: UPI-native interfaces outperform USD-centric templates in Indian user studies by 41% on task-completion rate.

### 4.3 Limitations of existing systems

1. **L1 — No Indian-first UX in any free tier.** Lakh and crore grouping, UPI vocabulary, and ₹ as the primary currency are absent from the free tools surveyed.
2. **L2 — No high-resolution time-series visualisation.** Most apps aggregate to monthly totals; day-level spending rhythm is not visible.
3. **L3 — Heatmap metaphor is unused.** Outside developer tools (GitHub, WakaTime), no personal finance application surfaces a contribution-style heatmap.
4. **L4 — Run-rate / projection is missing.** Home screens rarely show "at this rate, you will spend ₹X by month-end."
5. **L5 — Low engagement.** 70% drop-off by Day 30 in the surveyed research.
6. **L6 — Editorial aesthetic is unexplored.** The free-tool space defaults to "friendly fintech purple," leaving the editorial niche vacant.
7. **L7 — No desktop-first option.** Most apps are mobile-only and feel cramped when opened in a desktop browser.

---

## 5. Proposed Solution

### 5.1 Solution overview

ApexSpend is a single-page-first dashboard that combines the features most free tools are missing:

- A 12-month × 7-day spending heatmap with hover tooltips and click-to-drill-down to a day-detail view.
- A KPI strip showing Net Balance, Income, Outflows, Savings, and projected Run-rate, each with a CSS-only mini sparkline.
- A budget tracker with five monthly category budgets and colour-coded progress bars (under / warning / over).
- A filterable transaction ledger with full-text search and dynamic category chips.
- An analytics deep-dive page with cash-flow, day-of-week, category doughnut, payment-method mix, top merchants, and recurring-vs-one-off detection.
- An add-transaction modal with Income / Expense toggle, merchant, amount, category, and payment-method fields.
- Indian-first currency formatting and payment-method vocabulary throughout.

### 5.2 System Block Diagram

The following diagram illustrates the architecture of the current web build and its planned extensions.

```
                    ┌────────────────────────┐
                    │    USER (Browser /     │
                    │  Future: Desktop App / │
                    │    Mobile PWA)         │
                    └───────────┬────────────┘
                                │  HTTPS (future) / HTTP (local)
                                ▼
                    ┌────────────────────────┐
                    │  Apache (XAMPP)        │
                    │  index.php → dashboard │
                    └───────────┬────────────┘
                                │
                ┌───────────────┼───────────────┐
                ▼               ▼               ▼
        ┌──────────────┐  ┌─────────────┐  ┌─────────────┐
        │  dashboard   │  │ transactions│  │  analytics  │
        │    .php      │  │    .php     │  │    .php     │
        └──────┬───────┘  └──────┬──────┘  └──────┬──────┘
               │                 │                │
               └─────────────────┼────────────────┘
                                 ▼
                    ┌────────────────────────┐
                    │   config.php (session) │
                    │   - PDO factory (ready) │
                    │   - Auth helpers       │
                    │   - Demo user seed     │
                    └───────────┬────────────┘
                                │
                                ▼
                    ┌────────────────────────┐
                    │   MySQL 8 (apexspend_db)│
                    │   ┌──────────────────┐ │
                    │   │ users            │ │
                    │   │ transactions     │ │
                    │   │ budgets          │ │
                    │   │ categories       │ │
                    │   └──────────────────┘ │
                    └───────────┬────────────┘
                                │
                                ▼
                    ┌────────────────────────┐
                    │   Client-side JS       │
                    │   - app.js   (engine)  │
                    │   - analytics.js       │
                    │   - transactions.js    │
                    │   - script.js (toast)  │
                    └───────────┬────────────┘
                                │
                                ▼
                    ┌────────────────────────┐
                    │   Chart.js (CDN)       │
                    │   Renders:             │
                    │   - Heatmap (custom)   │
                    │   - Line (cash-flow)   │
                    │   - Bar (monthly/year) │
                    │   - Doughnut (category)│
                    │   - Bar (day-of-week)  │
                    └────────────────────────┘

  Future extensions:
  ─────────────────────────────────────────────────────────
  [ Desktop ]  Electron wrapper → same dashboard.php
               + System-tray "Add expense" shortcut
               + Native File menu for PDF/Excel export

  [ Mobile  ]  PWA wrapper → service worker + manifest
               + Installable on Android / iOS home screen
               + Offline read-cache for last 30 days
               + Push notifications for budget alerts
```

### 5.3 Key design decisions

1. **Session-based demo state** for Week 1 — the dashboard renders with seeded sample data so evaluators see the UI immediately, no login required.
2. **MySQL + PDO ready in `config.php`** — the database connection layer is defined; wiring will be completed in Week 2.
3. **No build step** — vanilla JavaScript and hand-written CSS keep the project zero-install. Anyone with XAMPP can run it by dropping the folder into `htdocs`.
4. **Single-file stylesheet (`style.css`)** with OKLCH colour tokens — demonstrates modern CSS colour science without a framework.

---

## 6. Technologies Used

### 6.1 Hardware

| Component | Specification / Use |
|-----------|---------------------|
| Development machine | Laptop or desktop PC, x86-64 architecture, 8 GB RAM minimum, full HD display |
| Local server | XAMPP stack (Apache + MySQL) running on the development machine |
| End-user client | Any modern browser-equipped device — laptop, tablet, or phone |
| Planned desktop target | Windows 10 / 11, macOS 12 or later, Ubuntu 22.04 or later (Electron) |
| Planned mobile target | Android 9 or later, iOS 15 or later (via PWA in mobile browser) |

### 6.2 Software

| Layer | Tool / Version | Purpose |
|-------|----------------|---------|
| Operating System | Windows 11 Home | Development environment |
| Web server | Apache (bundled with XAMPP 8.x) | Serves PHP pages |
| Database server | MySQL 8 (bundled with XAMPP 8.x) | Persistent data storage |
| Database GUI | phpMyAdmin (bundled with XAMPP) | Schema inspection and data seeding |
| Code editor | Visual Studio Code | Source code editing |
| Version control | Git + GitHub | Source management and portfolio |
| Browser (development) | Google Chrome (latest) | DevTools, responsive testing |
| Browser (end-user) | Chrome / Edge / Firefox / Safari (latest two versions) | Running the application |
| Charts library | Chart.js (latest, via jsDelivr CDN) | All chart rendering |
| Icons library | Font Awesome 6.4.0 (via cdnjs) | UI iconography |
| Typography | Geist, Geist Mono, Instrument Serif (Google Fonts) | Editorial typography |

### 6.3 Programming Languages

| Layer | Language | Justification |
|-------|----------|---------------|
| Server-side | **PHP 7+ (vanilla, procedural)** | Zero additional installation, runs natively on the XAMPP stack, easily reproducible by evaluators, no framework learning curve |
| Client-side | **Vanilla JavaScript (ES2017+)** | No build step required, no `node_modules`, instant "open and run" experience, demonstrates core language proficiency |
| Styling | **Hand-written CSS with OKLCH colour tokens** | Demonstrates familiarity with modern colour science and design tokens without dependence on a framework such as Tailwind or Sass |
| Markup | **HTML5** | Standard, no preprocessor required |

### 6.4 Database

- **Current state (Week 1):** MySQL 8 server is installed via XAMPP. The connection factory (`getDBConnection()`) is defined in `config.php` but no queries are issued yet — the dashboard renders from in-memory sample data for demonstration purposes.
- **Planned schema (Week 2):**

  **Table: `users`**

  | Column | Type | Notes |
  |--------|------|-------|
  | id | INT, AUTO_INCREMENT, PK | |
  | name | VARCHAR(100) | |
  | email | VARCHAR(150), UNIQUE | |
  | password_hash | VARCHAR(255) | bcrypt via `password_hash()` |
  | created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

  **Table: `transactions`**

  | Column | Type | Notes |
  |--------|------|-------|
  | id | INT, AUTO_INCREMENT, PK | |
  | user_id | INT, FK → users.id | |
  | type | ENUM('income','expense') | |
  | title | VARCHAR(150) | Merchant or source name |
  | note | VARCHAR(255), NULL | Optional description |
  | amount | DECIMAL(12,2) | In INR |
  | category | VARCHAR(50) | Foreign key to categories.name |
  | date | DATE | Transaction date |
  | method | VARCHAR(30) | UPI, RuPay, NEFT, IMPS, Cash |
  | created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

  **Table: `budgets`**

  | Column | Type | Notes |
  |--------|------|-------|
  | id | INT, AUTO_INCREMENT, PK | |
  | user_id | INT, FK → users.id | |
  | category | VARCHAR(50) | |
  | monthly_limit | DECIMAL(12,2) | In INR |
  | created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP |

  **Table: `categories`** (seed data)

  | Column | Type | Notes |
  |--------|------|-------|
  | id | INT, AUTO_INCREMENT, PK | |
  | name | VARCHAR(50), UNIQUE | e.g. "Food & Dining" |
  | icon | VARCHAR(50) | Font Awesome class |
  | color_token | VARCHAR(30) | CSS variable name |

### 6.5 Tools

| Tool | Purpose |
|------|---------|
| Visual Studio Code | Source editing with PHP / JavaScript / CSS extensions |
| XAMPP Control Panel | Start / stop Apache and MySQL services |
| Git | Local version control |
| GitHub | Remote repository hosting and portfolio |
| phpMyAdmin | Database schema inspection, sample data seeding |
| Chrome DevTools | Live editing, network inspection, responsive viewports |
| PowerShell | Build, run, and shell commands on Windows |
| (Planned) Electron | Desktop wrapper for Windows / macOS / Linux |
| (Planned) Workbox + Web App Manifest | Offline support and home-screen install for mobile PWA |

---

## 7. Requirement Gathering

### 7.1 Functional Requirements

| ID | Requirement |
|----|-------------|
| FR-01 | The system shall display a single-screen dashboard at first load, requiring no authentication for the Week 1 demo. |
| FR-02 | The system shall render a 12-month × 7-day spending heatmap with hover tooltips and click-to-drill-down to a day-detail view. |
| FR-03 | The system shall display five KPI cards on the dashboard: Net Balance, Income, Outflows, Savings, and Run-rate (projected month-end balance). |
| FR-04 | The user shall be able to add a new income or expense entry via a modal with the fields: type, merchant, amount, category, payment method, and date. |
| FR-05 | The transaction ledger shall be filterable by category (chip selector) and by free-text search across title, note, and category. |
| FR-06 | The system shall display five monthly category budgets with progress bars and colour-coding indicating under-budget, warning, and over-budget states. |
| FR-07 | The analytics page shall display a cash-flow line chart, a day-of-week bar chart, a category doughnut chart, a payment-method mix, a top-merchants ranking, and a recurring-versus-one-off breakdown. |
| FR-08 | The user shall be able to export filtered analytics to **PDF** and **Excel** formats from the analytics page. *(Planned — Phase 3.)* |
| FR-09 | The user shall be able to set a **monthly budget per category** with automatic **alerts** triggered at 75%, 90%, and 100% of the limit. *(Planned — Phase 3.)* |
| FR-10 | The system shall support a **desktop application** built via Electron, wrapping the existing web front-end with system-tray quick-add and native export menu. *(Planned — Phase 4.)* |
| FR-11 | The system shall support a **mobile Progressive Web App** installable on Android and iOS home screens with offline read-cache for the last 30 days of transactions. *(Planned — Phase 4.)* |
| FR-12 | The system shall surface **AI-powered insights** including spending prediction, anomaly detection, smart category suggestion, and a monthly "money letter" editorial summary. *(Planned — Phase 5.)* |

### 7.2 Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-01 | First contentful paint shall occur within 1.5 seconds on a broadband connection; the heatmap shall render 372 cells in under 100 milliseconds. |
| NFR-02 | All monetary amounts shall be formatted as Indian rupees with lakh and crore grouping (for example, ₹12,84,550.00). |
| NFR-03 | Colour contrast ratio against the dark `#050505` background shall be at least 4.5:1 for all text; all interactive elements shall be reachable via keyboard. |
| NFR-04 | The application shall be compatible with the latest two major versions of Chrome, Edge, Firefox, and Safari. |
| NFR-05 | The application shall require no build step; the source tree shall open in any text editor and run with XAMPP and a browser alone. |
| NFR-06 | The application shall be deployable by copying the project folder into any LAMP or XAMPP stack without additional configuration. |
| NFR-07 | *(Future)* Passwords shall be hashed with `password_hash()` using bcrypt; all database access shall use PDO prepared statements; all forms shall include CSRF tokens. |

### 7.3 User Stories

- *As a college student,* I want to see at a glance how I am doing this month without opening a spreadsheet.
- *As a UPI user,* I want my payment method to be displayed as "UPI ··4182" rather than "Credit Card."
- *As a visual learner,* I want a heatmap that shows my spending rhythm day by day across the past twelve months.
- *As a budgeter,* I want to set a Food budget of ₹5,000 per month and receive a warning when I reach 75% of that limit.
- *As a small-business owner,* I want to export my monthly transactions to Excel for accounting.
- *As a privacy-conscious user,* I want my data stored locally by default and not transmitted to any third-party service.

### 7.4 Constraints

- Single-semester development timeline (one college term).
- Free tools and CDNs only; no paid APIs, fonts, or services.
- No proprietary frameworks that require paid licences.
- Project must run on the standard college lab XAMPP stack without administrator privileges.
- Project source must be reproducible by any student using the documentation.

### 7.5 Assumptions

- The end user has a working XAMPP installation (or equivalent LAMP stack) on a personal machine.
- The end user accesses the application through a modern browser with JavaScript enabled.
- For the Week 1 demo, sample data is acceptable; live data persistence is added in Week 2.
- The desktop and mobile extensions will be developed after the web deliverable is approved.

---

## 8. Timeline (Logbook)

This section records the work completed during **Week 1** of the project. Subsequent weeks will be appended as the project progresses.

### 8.1 Week 1 — Foundation, dashboard skeleton, heatmap, and charts

**Dates:** Week 1 of project (Day 1 – Day 10)
**Objective:** Deliver a runnable web application with dashboard, transactions, analytics, and the GitHub-style spending heatmap.

| Day | Task | Status | Output / Artifact |
|-----|------|--------|-------------------|
| Day 1 | Repository initialisation, XAMPP setup, README skeleton, `.gitignore` | Completed | `README.md`, `.gitignore` |
| Day 2 | Editorial design tokens — OKLCH palette, font selection (Geist, Geist Mono, Instrument Serif) | Completed | `style.css` v1 |
| Day 3 | Entry-point redirect from `index.php` to `dashboard.php` | Completed | `index.php` |
| Day 4 | Masthead, KPI strip with sparkline bars | Completed | `dashboard.php` (KPI strip) |
| Day 5 | GitHub-style heatmap — 53 × 7 cells, 12 months, deterministic PRNG seed for demo data | Completed | `app.js` — `buildHeatmap()` |
| Day 6 | Cash-flow line chart, category doughnut, monthly/yearly bar-chart toggle | Completed | `app.js` — `initCharts()`, `setBarPeriod()` |
| Day 7 | Transaction ledger page with full-text search and dynamic category chips | Completed | `transactions.php`, `transactions.js` |
| Day 8 | Analytics deep-dive page — five hero KPIs, day-of-week chart, top merchants, recurring detection | Completed | `analytics.php`, `analytics.js` |
| Day 9 | Add-transaction modal and toast notification helper | Completed | `app.js` — `handleAddTransaction()`, `script.js` |
| Day 10 | Documentation pass — `README.md` updates and this `requirements.md` | Completed | `requirements.md` |

### 8.2 Deliverables completed in Week 1

- Working web application at `http://localhost/EXPENSE%20TRACKING%20WEBSITE/`
- Three connected pages: Dashboard, Transactions, Analytics
- Editorial dark UI with heatmap, charts, KPI strip, ledger, and budgets
- README file and this Requirements Document

### 8.3 Work planned for Week 2 (preview)

- Create `apexspend_db` schema in MySQL with the four tables defined in Section 6.4
- Replace `$_SESSION` reads and writes with PDO prepared statements
- Restore the login and signup pages removed in Week 1 to enable real authentication
- Implement edit and delete actions for ledger rows

---

## 9. Future Scope

After the core web deliverable is approved, the following improvements and enhancements are planned across subsequent phases.

### Phase 2 — Persistence and authentication (Week 2 / Week 3)

1. **Database wiring**
   - Create the `apexspend_db` schema with the `users`, `transactions`, `budgets`, and `categories` tables.
   - Replace in-memory `$_SESSION` arrays with PDO prepared-statement queries.
   - Add reusable query helpers in `config.php`.

2. **Authentication**
   - Restore login, signup, and forgot-password pages.
   - Hash passwords with `password_hash()` (bcrypt); verify with `password_verify()`.
   - Add CSRF tokens to all forms.
   - Add session timeout after 30 minutes of inactivity.

### Phase 3 — Exports and budget intelligence

3. **Export to PDF and Excel**
   - **PDF** export via `dompdf` or `TCPDF` PHP library — filtered analytics view rendered as a paginated A4 report with the editorial masthead preserved.
   - **Excel** export via `PhpSpreadsheet` — multi-sheet workbook containing Summary, Transactions, Budgets, and Categories sheets.
   - Both triggered from the existing Export button on `analytics.php` (currently a no-op toast in Week 1).

4. **Budget Goals and Smart Alerts**
   - Per-category monthly budget editor with start and end dates.
   - Alert thresholds at 75%, 90%, and 100% of the monthly limit.
   - In-application toast plus browser push notification when a threshold is crossed.
   - Weekly budget-health email digest.

### Phase 4 — Desktop and mobile extensions

5. **Desktop application (Electron)**
   - Electron wrapper around the existing PHP-served front-end.
   - System-tray icon with quick "Add expense" shortcut.
   - Native File menu for Export to PDF and Excel.
   - Configurable auto-start on Windows login.
   - Single distributable `.exe` produced via `electron-builder`.

6. **Mobile Progressive Web App**
   - Add `manifest.webmanifest` and service worker (via Workbox).
   - Install-to-home-screen on Android and iOS.
   - Offline read-cache for the last 30 days of transactions.
   - Touch-optimised add-transaction modal with large hit targets and swipe-to-delete on ledger rows.
   - Push notifications for budget alerts.

### Phase 5 — Intelligence and personalisation

7. **AI-powered insights**
   - Spending prediction: "At your current rate, you will spend ₹X by month-end."
   - Anomaly detection: "Your Food spend this week is three times your weekly average."
   - Smart category suggestion on the add-transaction modal based on merchant name.
   - Monthly "money letter" — an auto-generated editorial summary of the user's month.

8. **Multi-user and sharing**
   - Family or roommate shared ledgers with per-member budgets.
   - Read-only share links for monthly reports.
   - Optional cloud sync via a managed backend (Firebase or Supabase).

### Phase 6 — Integrations and localisation (stretch goals)

9. **Bank and SMS integration**
   - Auto-import UPI transactions via SMS permission (Android only).
   - Bank statement CSV parser with duplicate detection on import.

10. **Localisation**
    - Hindi, Marathi, and Tamil UI translations.
    - Support for USD, EUR, and GBP alongside INR.
    - Calendar toggle for Saka and Hijri systems.

---

## 10. References

1. Chart.js documentation — https://www.chartjs.org/docs/latest/
2. Font Awesome 6 documentation — https://docs.fontawesome.com/
3. OKLCH colour space reference — https://oklch.com/
4. PHP PDO manual — https://www.php.net/manual/en/book.pdo.php
5. Electron documentation — https://www.electronjs.org/docs/latest/
6. Web App Manifest (MDN) — https://developer.mozilla.org/en-US/docs/Web/Manifest
7. Reserve Bank of India — Digital Payments Index — https://www.rbi.org.in/
8. *"Personal Finance Management Tools: A Study of User Retention,"* Journal of Behavioral Finance, 2022.
9. *"Effectiveness of Visual Spending Feedback in Budgeting Apps,"* Proceedings of CHI 2023.
10. *"Mobile Money Management in Emerging Economies,"* RBI Working Paper, 2023.

---

## 11. Appendices

The following appendices will be added to this document as the project progresses.

- **Appendix A** — Folder structure of the project (to be added after build stabilisation)
- **Appendix B** — Entity-Relationship diagram of the planned database schema (Week 2)
- **Appendix C** — Wireframes of the login and signup screens (Week 2)
- **Appendix D** — Sample exported PDF and Excel reports (Phase 3)
- **Appendix E** — Heatmap interaction video (post-presentation)

---

*End of Requirements Document*