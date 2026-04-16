# Boyas Invoice

A self-hosted, single-user invoicing app. Generate professional PDF invoices, manage sender
and recipient details, and keep a running invoice counter — all stored locally on your own
machine with no external services required.

Built with **Laravel 12** (backend) + **Vue 3** (frontend).

---

## For Non-Developers

If you're not a developer, use the step-by-step guide:

**[SETUP_GUIDE.md](./SETUP_GUIDE.md)** — install Docker Desktop, download a ZIP, double-click a file. That's the whole setup.

---

## Quick Start (Docker)

**Requirements:** [Docker Desktop](https://www.docker.com/products/docker-desktop/)

```bash
docker compose up -d
```

Open **http://localhost:8080**. The entrypoint handles `.env`, key generation, and migrations automatically on first boot.

---

## Quick Start (Native dev)

**Requirements:** PHP 8.2+, Composer, Node.js 18+

```bash
composer run setup   # install deps, copy .env, generate key, migrate, build assets
composer run dev     # Laravel server + Vite HMR + queue worker + log viewer
```

Open **http://localhost:8000**.

---

## Features

- Generate and download PDF invoices
- Save and reuse recipient profiles
- Auto-incrementing invoice numbers (`INV-YYYY-NNNN`)
- Upload a logo that appears on every invoice
- All data stored locally — no cloud, no external database

---

## Architecture

| Layer | Technology | Notes |
|-------|-----------|-------|
| Backend | Laravel 12 | REST API under `/api/` |
| Frontend | Vue 3 SPA | Single-page app |
| PDF generation | DomPDF | Rendered server-side |
| Data storage | SQLite + Eloquent | App data via `JsonStorage` service |
| Build tool | Vite | HMR in dev, hashed bundles in prod |
| Container | Nginx + PHP-FPM | Managed by Supervisor inside one image |

**Persistent data** (survives restarts and updates):
- `storage/app/sender.json` — sender profile (Docker: `boyas_storage` volume)
- `storage/app/recipients.json` — saved recipients
- `storage/app/invoice_counter.json` — invoice number tracker
- `database/database.sqlite` — SQLite DB (Docker: `boyas_database` volume)

---

## Testing & Code Style

```bash
composer run test    # clears config cache, then runs PHPUnit
./vendor/bin/pint    # Laravel Pint (PSR-12 code style fixer)
```
