# Boyas Invoice

A self-hosted, single-user invoicing app. Generate professional PDF invoices, manage sender and recipient details, and keep a running invoice counter — all stored locally on your own machine with no external database required.

Built with **Laravel 12** (backend) + **Vue 3** (frontend).

---

## Features

- Generate and download PDF invoices
- Save and reuse recipient profiles
- Auto-incrementing invoice numbers (`INV-YYYY-NNNN`)
- Upload your own logo to appear on invoices
- All data stored as local JSON files — no cloud, no database setup

---

## First Time Here?

If you are not a developer and this is your first time setting up a project like this, start with the step-by-step guide:

**[SETUP_GUIDE.md](./SETUP_GUIDE.md)** — covers everything from installing Git to opening the app in your browser, with instructions for both Windows and macOS.

---

## Quick Start (for developers)

**Requirements:** PHP 8.2+, Composer, Node.js 18+

```bash
git clone <your-repo-url> boyas-invoice
cd boyas-invoice
composer run setup
composer run dev
```

Then open **http://localhost:8000** in your browser.

### What `composer run setup` does

Runs these steps automatically in sequence:

1. `composer install` — installs PHP dependencies
2. Copies `.env.example` → `.env` (if not already present)
3. `php artisan key:generate` — generates the app encryption key
4. `php artisan migrate` — initializes the SQLite database file
5. `npm install` — installs JavaScript dependencies
6. `npm run build` — compiles the Vue frontend

### Daily development

```bash
composer run dev   # starts Laravel server, queue worker, log viewer, and Vite HMR concurrently
```

The app is available at **http://localhost:8000**.

---

## Architecture Overview

| Layer | Technology | Notes |
|-------|-----------|-------|
| Backend | Laravel 12 | REST API under `/api/` |
| Frontend | Vue 3 SPA | Single-page app, Tailwind CSS |
| PDF generation | DomPDF | Rendered server-side |
| Data storage | JSON files | No relational database for app data |
| Build tool | Vite | HMR in dev, hashed bundles in prod |

**Data files** (auto-created on first use):
- `storage/app/sender.json` — your business profile
- `storage/app/recipients.json` — saved recipients
- `storage/app/invoice_counter.json` — invoice number tracker

---

## Testing & Code Style

```bash
composer run test    # clears config cache, then runs PHPUnit
./vendor/bin/pint    # Laravel Pint (PSR-12 code style fixer)
```
