# SQLite Migration Design

**Date:** 2026-03-20
**Project:** Boyas Invoice Self-Hosted
**Scope:** Replace JSON file storage with SQLite via Eloquent models

---

## Overview

Replace the three JSON storage files (`sender.json`, `recipients.json`, `invoice_counter.json`) with proper SQLite tables backed by Eloquent models. Delete the `JsonStorage` service class. All controller interfaces (API routes, request/response shapes) remain unchanged.

---

## Data Model

### `sender` table
Single-row record (always `id = 1`). Replaces `sender.json`.

| Column | Type | Nullable |
|---|---|---|
| `id` | integer PK (auto-increment) | no |
| `name` | string | yes |
| `company` | string | yes |
| `address` | string | yes |
| `city_state_zip` | string | yes |
| `email` | string | yes |
| `phone` | string | yes |
| `logo_path` | string | yes |
| `created_at` | timestamp | yes |
| `updated_at` | timestamp | yes |

### `recipients` table
Standard multi-row table. Replaces `recipients.json`. Preserves existing `r_XXXX` uniqid string format for primary key to avoid breaking the frontend.

| Column | Type | Nullable |
|---|---|---|
| `id` | string PK | no |
| `name` | string | no |
| `company` | string | yes |
| `address` | string | yes |
| `city_state_zip` | string | yes |
| `email` | string | yes |
| `created_at` | timestamp | yes |
| `updated_at` | timestamp | yes |

### `invoice_counter` table
Single-row record (always `id = 1`). Replaces `invoice_counter.json`.

| Column | Type | Nullable |
|---|---|---|
| `id` | integer PK (auto-increment) | no |
| `counter` | integer (default 0) | no |
| `updated_at` | timestamp | yes |

---

## Architecture

### New files
- `database/migrations/*_create_sender_table.php`
- `database/migrations/*_create_recipients_table.php`
- `database/migrations/*_create_invoice_counter_table.php`
- `app/Models/Sender.php`
- `app/Models/Recipient.php`
- `app/Models/InvoiceCounter.php`
- `tests/Feature/SenderTest.php`
- `tests/Feature/RecipientTest.php`
- `tests/Feature/InvoiceTest.php`

### Modified files
- `app/Http/Controllers/SenderController.php`
- `app/Http/Controllers/RecipientController.php`
- `app/Http/Controllers/InvoiceController.php`

### Deleted files
- `app/Services/JsonStorage.php`

---

## Controller Changes

### SenderController
| Method | Change |
|---|---|
| `show()` | `JsonStorage::get(...)` → `Sender::firstOrNew(['id' => 1])` |
| `update()` | `JsonStorage::put(...)` → `Sender::updateOrCreate(['id' => 1], $validated)` |
| `uploadLogo()` | File storage unchanged; logo_path update → `Sender::updateOrCreate(['id' => 1], ['logo_path' => $path])` |

### RecipientController
| Method | Change |
|---|---|
| `index()` | `JsonStorage::get(...)` → `Recipient::all()` |
| `store()` | Array manipulation + `JsonStorage::put(...)` → `Recipient::updateOrCreate(['id' => $id ?? 'r_'.uniqid()], $data)` |
| `destroy($id)` | Array filter + `JsonStorage::put(...)` → `Recipient::findOrFail($id)->delete()` (proper 404 on missing id) |

### InvoiceController
| Method | Change |
|---|---|
| `generate()` — sender read | `JsonStorage::get('sender.json', [])` → `Sender::find(1) ?? new Sender()` |
| `generate()` — counter increment | Read/write JSON array → `$c = InvoiceCounter::firstOrCreate(['id' => 1], ['counter' => 0]); $c->increment('counter');` |

---

## Testing Strategy

Feature tests written **before** controller changes. Tests hit the real HTTP layer and use `RefreshDatabase` to reset SQLite between each test. Tests validate the same contracts whether the backend is JSON or DB — making them a reliable refactor safety net.

### SenderTest.php
- `GET /api/sender` returns 200 with correct JSON shape
- `GET /api/sender` returns nullable defaults when no profile exists
- `POST /api/sender` saves and returns updated profile
- `POST /api/sender` fails validation without required `name` field

### RecipientTest.php
- `GET /api/recipients` returns empty array initially
- `GET /api/recipients` returns saved recipients
- `POST /api/recipients` creates new recipient with `r_`-prefixed string id
- `POST /api/recipients` updates existing recipient when id is provided
- `DELETE /api/recipients/{id}` returns 200 on success
- `DELETE /api/recipients/{id}` returns 404 for unknown id

### InvoiceTest.php
- `POST /api/invoice/generate` returns `application/pdf` content-type
- `POST /api/invoice/generate` sets `X-Invoice-Number` header matching `INV-{YEAR}-{NNNN}` format
- `POST /api/invoice/generate` increments counter on each call

---

## Implementation Sequence

```
1.  git checkout dev && git pull
2.  git checkout -b feature/sqlite-migration

3.  Write all feature tests (SenderTest, RecipientTest, InvoiceTest)
4.  composer run test  ← confirm tests exist and fail as expected

5.  Create three migrations
6.  php artisan migrate

7.  Create Sender, Recipient, InvoiceCounter models

8.  Update SenderController
9.  composer run test  ← SenderTest passes

10. Update RecipientController
11. composer run test  ← RecipientTest passes

12. Update InvoiceController
13. composer run test  ← InvoiceTest passes

14. Delete app/Services/JsonStorage.php
15. composer run test  ← full suite passes

16. Manual smoke test in browser

17. git commit -m "feat: migrate JSON storage to SQLite via Eloquent"
18. Open PR: feature/sqlite-migration → dev
```

---

## Out of Scope

- Data migration from existing JSON files (fresh start — no existing data to preserve)
- Docker containerization (separate future effort)
- AWS deployment (long-term goal, not this phase)
- Auth / multi-user support
