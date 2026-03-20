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
  id:        Number,   // Date.now() + Math.random() for uniqueness
  type:      String,   // 'pending' | 'success' | 'error'
  message:   String,
  timestamp: Date,
}
```

**Exposed API:**
- `logs` — readonly reactive array, newest-first
- `addLog(type, message)` — appends entry, returns a ref to it so callers can mutate `type`/`message` in-place after async resolution
- `clearLog()` — empties the array

### Component: `ActivityLog.vue`

**File:** `resources/js/components/ActivityLog.vue`

Self-contained panel component. Imports `useActivityLog` directly.

**UI elements:**
- Header row: "Activity" label + "Clear" button (calls `clearLog()`)
- Scrollable list, newest entry at top
- Per entry: colored indicator dot, message text, dimmed timestamp (`h:mm:ss AM/PM`)
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

The log panel sits between the sidebar and the main content area with a white background and a right border (`1px solid #e7e5e4`). The topbar spans only the `.main` column. The log panel has `overflow-y: auto` so long histories scroll independently.

## Events Logged

### SenderProfile.vue

| Trigger | Initial entry | Resolution |
|---|---|---|
| Save starts | `pending` "Saving profile…" | `success` "Profile saved" / `error` "Failed to save profile" |
| Logo upload starts | `pending` "Uploading logo…" | `success` "Logo uploaded" / `error` "Failed to upload logo" |
| Logo removed (on save) | `success` "Logo removed" | — |

### NewInvoice.vue

| Trigger | Initial entry | Resolution |
|---|---|---|
| PDF generation starts | `pending` "Generating PDF…" | `success` "Invoice {number} generated" / `error` real server message |

For the error case, extract the actual message from the axios error response (validation errors, server exceptions) rather than displaying the current generic fallback string.

## Files Changed

| File | Change |
|---|---|
| `resources/js/composables/useActivityLog.js` | New — shared log state |
| `resources/js/components/ActivityLog.vue` | New — panel UI |
| `resources/js/App.vue` | Add panel to layout |
| `resources/js/components/SenderProfile.vue` | Import composable, call `addLog` |
| `resources/js/components/NewInvoice.vue` | Import composable, call `addLog`, improve error extraction |

## Out of Scope

- Log persistence across page refreshes (session-only)
- Filtering or searching log entries
- Timestamps older than the current session
