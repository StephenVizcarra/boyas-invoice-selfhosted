# Activity Log Panel — Design Spec

**Date:** 2026-03-20
**Status:** Approved

## Overview

Add a persistent activity log panel to the Boyas Invoice app that displays a live stream of what the app is doing — profile saves, logo uploads, PDF generation — across all tabs. The primary motivation is surfacing real error messages instead of the generic "check your sender profile is filled in" catch-all.

## Architecture

### Composable: `useActivityLog`

**File:** `resources/js/composables/useActivityLog.js`

Module-level reactive state (not inside `setup()`) so the same instance is shared across every component that imports it — no provide/inject or event bus needed.

**Log entry shape:**
```js
{
  id:        String,   // crypto.randomUUID()
  type:      String,   // 'pending' | 'success' | 'error'
  message:   String,
  timestamp: Date,     // JS Date object, formatted at render time
}
```

**`id` generation:** Use `crypto.randomUUID()` for guaranteed uniqueness and a clean string key for `v-for`.

**Timestamp display:** Format using `toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' })` at render time in the component template.

**Exposed API:**
- `logs` — readonly reactive array, newest-first
- `addLog(type, message)` — appends entry, returns the reactive entry object directly (not a Vue `ref`) so callers can mutate `entry.type` and `entry.message` in-place after async resolution
- `clearLog()` — empties the array

**Entry cap:** No automatic cap. The "Clear" button is the only removal mechanism. This is intentional for a single-user self-hosted app where session log volume is low.

### Component: `ActivityLog.vue`

**File:** `resources/js/components/ActivityLog.vue`

Self-contained panel component. Imports `useActivityLog` directly.

**UI elements:**
- Header row: "Activity" label + "Clear" button (calls `clearLog()`)
- Scrollable list, newest entry at top
- Per entry: colored indicator dot, message text, dimmed timestamp
- Empty state: "No activity yet." in muted text

**Entry type colors:**
- `pending` — amber (`#d97706`)
- `success` — green (`#16a34a`)
- `error` — red (`#dc2626`)

### Layout change in `App.vue`

The shell becomes a 3-column flex layout:

```
[ Sidebar 220px ] [ Activity Log 260px ] [ Main content flex:1 ]
```

The log panel sits between the sidebar and main content. The sidebar retains its existing `border-right: 1px solid #292524` (dark tone). The log panel has a white background with `border-right: 1px solid #e7e5e4` (light tone, matching the topbar border) — no left border, since the sidebar's right border provides the visual separation on that side. The topbar remains nested inside `.main` and is unaffected. The log panel has `overflow-y: auto` and stretches to full viewport height via the flex layout.

## Events Logged

### SenderProfile.vue

The `save()` function performs two sequential async operations within one `try/finally`. Log entries must be created and resolved independently to reflect per-operation outcomes:

**Profile save:**
1. Create `pending` entry: "Saving profile…"
2. `await axios.post('/api/sender', form.value)`
3. On success: mutate entry → `success` "Profile saved"
4. On error: mutate entry → `error` "Failed to save profile", then `throw` to exit

**Logo removal (inside the `logoRemoved` branch):**
1. Create `pending` entry: "Removing logo…"
2. `await axios.delete('/api/sender/logo')`
3. On success: mutate entry → `success` "Logo removed"
4. On error: mutate entry → `error` "Failed to remove logo"

**Logo upload (inside the `logoFile` branch):**
1. Create `pending` entry: "Uploading logo…"
2. `await axios.post('/api/sender/logo', fd, ...)`
3. On success: mutate entry → `success` "Logo uploaded"
4. On error: mutate entry → `error` "Failed to upload logo"

Each operation has its own `try/catch` so a logo failure does not roll back the already-resolved profile success entry.

### NewInvoice.vue

| Trigger | Initial entry | Resolution |
|---|---|---|
| PDF generation starts | `pending` "Generating PDF…" | `success` "Invoice {number} generated" / `error` extracted message |

**Error message extraction priority** (first truthy value wins):
1. `e.response?.data?.message` — Laravel single-message errors
2. `Object.values(e.response?.data?.errors ?? {}).flat().join(' ')` — Laravel validation errors
3. `e.message` — network/JS errors
4. `'Unknown error'` — final fallback

## Files Changed

| File | Change |
|---|---|
| `resources/js/composables/useActivityLog.js` | New — shared log state |
| `resources/js/components/ActivityLog.vue` | New — panel UI |
| `resources/js/App.vue` | Add panel to layout |
| `resources/js/components/SenderProfile.vue` | Import composable, add per-operation log entries |
| `resources/js/components/NewInvoice.vue` | Import composable, call `addLog`, improve error extraction |

## Out of Scope

- Log persistence across page refreshes (session-only)
- Filtering or searching log entries
- Automatic entry cap or expiry
- Logging mount-time fetch failures in `SenderProfile` or `NewInvoice` (API unreachable at load is not logged)
