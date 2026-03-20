# Activity Log Panel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a persistent live activity log panel between the sidebar and main content, visible on all tabs, showing real-time status of profile saves, logo operations, and PDF generation with actual error messages.

**Architecture:** A module-level Vue 3 composable (`useActivityLog`) holds a shared reactive log array. Both `SenderProfile` and `NewInvoice` import it and mutate log entries in-place as async operations resolve. `ActivityLog.vue` renders the panel; `App.vue` hosts it in the shell layout.

**Tech Stack:** Vue 3 (Composition API, `reactive`), `crypto.randomUUID()`, axios (blob error handling), `toLocaleTimeString`

---

## File Map

| Action | File | Responsibility |
|---|---|---|
| Create | `resources/js/composables/useActivityLog.js` | Shared reactive log store |
| Create | `resources/js/components/ActivityLog.vue` | Panel UI |
| Modify | `resources/js/App.vue` | Add panel to 3-column layout |
| Modify | `resources/js/components/SenderProfile.vue` | Instrument save/logo ops |
| Modify | `resources/js/components/NewInvoice.vue` | Instrument generate, fix error extraction |

---

## Task 1: Create `useActivityLog` composable

**Files:**
- Create: `resources/js/composables/useActivityLog.js`

- [ ] **Step 1.1 — Create the composable**

```js
// resources/js/composables/useActivityLog.js
import { reactive } from 'vue'

const logs = reactive([])

function addLog(type, message) {
  const entry = reactive({
    id:        crypto.randomUUID(),
    type,
    message,
    timestamp: new Date(),
  })
  logs.unshift(entry)
  return entry
}

function clearLog() {
  logs.splice(0)
}

export function useActivityLog() {
  return { logs, addLog, clearLog }
}
```

Key points:
- `logs` is **module-level** so all importers share the same array instance
- `addLog` returns the reactive entry object directly — callers mutate `entry.type` / `entry.message` in-place to transition `pending` → `success` / `error`
- `logs.unshift()` puts newest entries at the top
- `logs.splice(0)` empties in-place (preserves the reactive reference)

- [ ] **Step 1.2 — Verify no backend regressions**

```bash
composer run test
```
Expected: all tests pass (no PHP changes made yet, just a sanity check).

- [ ] **Step 1.3 — Commit**

```bash
git add resources/js/composables/useActivityLog.js
git commit -m "feat: add useActivityLog composable"
```

---

## Task 2: Create `ActivityLog.vue` panel component

**Files:**
- Create: `resources/js/components/ActivityLog.vue`

- [ ] **Step 2.1 — Create the component**

```vue
<!-- resources/js/components/ActivityLog.vue -->
<template>
  <div class="log-panel">
    <div class="log-header">
      <span class="log-title">Activity</span>
      <button class="log-clear" @click="clearLog" :disabled="!logs.length">Clear</button>
    </div>
    <div class="log-body">
      <p v-if="!logs.length" class="log-empty">No activity yet.</p>
      <div v-for="entry in logs" :key="entry.id" class="log-entry">
        <span class="log-dot" :class="`log-dot--${entry.type}`"></span>
        <span class="log-message">{{ entry.message }}</span>
        <span class="log-time">{{ formatTime(entry.timestamp) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useActivityLog } from '../composables/useActivityLog'

const { logs, clearLog } = useActivityLog()

function formatTime(date) {
  return date.toLocaleTimeString('en-US', {
    hour:   'numeric',
    minute: '2-digit',
    second: '2-digit',
  })
}
</script>

<style scoped>
.log-panel {
  width: 260px;
  min-width: 260px;
  background: #ffffff;
  border-right: 1px solid #e7e5e4;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.log-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px 12px;
  border-bottom: 1px solid #f5f4f0;
  flex-shrink: 0;
}

.log-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #a8a29e;
}

.log-clear {
  font-size: 11px;
  font-weight: 500;
  color: #78716c;
  background: none;
  border: none;
  cursor: pointer;
  font-family: 'Figtree', sans-serif;
  padding: 2px 6px;
  border-radius: 4px;
  transition: background 0.12s, color 0.12s;
}

.log-clear:hover:not(:disabled) {
  background: #f5f4f0;
  color: #1c1917;
}

.log-clear:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.log-body {
  flex: 1;
  overflow-y: auto;
  padding: 10px 0;
}

.log-empty {
  font-size: 12px;
  color: #c4bfbb;
  text-align: center;
  padding: 24px 16px;
}

.log-entry {
  display: grid;
  grid-template-columns: 8px 1fr auto;
  align-items: baseline;
  gap: 8px;
  padding: 6px 16px;
}

.log-entry:hover { background: #fafaf9; }

.log-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 4px;
}

.log-dot--pending { background: #d97706; }
.log-dot--success { background: #16a34a; }
.log-dot--error   { background: #dc2626; }

.log-message {
  font-size: 12px;
  color: #44403c;
  line-height: 1.5;
  word-break: break-word;
}

.log-time {
  font-size: 10.5px;
  color: #c4bfbb;
  white-space: nowrap;
  align-self: start;
  padding-top: 1px;
}
</style>
```

- [ ] **Step 2.2 — Commit**

```bash
git add resources/js/components/ActivityLog.vue
git commit -m "feat: add ActivityLog panel component"
```

---

## Task 3: Add panel to `App.vue` layout

**Files:**
- Modify: `resources/js/App.vue`

The current `.shell` is `display: flex` with `.sidebar` (220px) and `.main` (flex:1). Insert `<ActivityLog />` between them.

- [ ] **Step 3.1 — Insert `<ActivityLog />` into the template**

In `App.vue`, the current template has `</aside>` on line 23 followed immediately by `<div class="main">` on line 25. Insert `<ActivityLog />` between them:

```html
    </aside>

    <ActivityLog />

    <div class="main">
```

No other template changes needed. The existing `.sidebar` already has `border-right: 1px solid #292524` which provides the dark separator on the sidebar side — no CSS adjustments required in `App.vue`.

- [ ] **Step 3.2 — Add the import**

In `<script setup>`, add:
```js
import ActivityLog from './components/ActivityLog.vue'
```

- [ ] **Step 3.3 — Start dev server and verify layout**

```bash
composer run dev
```

Open `http://localhost:8000`. Verify:
- Three-column layout: dark sidebar | white log panel (260px) | main content
- Log panel shows "No activity yet." empty state
- "Clear" button is visible but disabled
- Switching between tabs keeps the log panel visible and unchanged

- [ ] **Step 3.4 — Commit**

```bash
git add resources/js/App.vue
git commit -m "feat: add ActivityLog panel to app shell layout"
```

---

## Task 4: Instrument `SenderProfile.vue`

**Files:**
- Modify: `resources/js/components/SenderProfile.vue`

The current `save()` uses a single `try/finally`. Restructure to give each operation its own `try/catch` so profile success and logo failure are independent log entries.

- [ ] **Step 4.1 — Add the import**

At the top of `<script setup>`, add:
```js
import { useActivityLog } from '../composables/useActivityLog'
const { addLog } = useActivityLog()
```

- [ ] **Step 4.2 — Replace the `save()` function**

```js
async function save() {
  saving.value = true
  saved.value  = false

  try {
    // Profile save
    const profileEntry = addLog('pending', 'Saving profile…')
    try {
      await axios.post('/api/sender', form.value)
      profileEntry.type    = 'success'
      profileEntry.message = 'Profile saved'
    } catch {
      profileEntry.type    = 'error'
      profileEntry.message = 'Failed to save profile'
      throw new Error('profile save failed')   // exits try block, hits finally
    }

    // Logo removal
    if (logoRemoved.value) {
      const logoEntry = addLog('pending', 'Removing logo…')
      try {
        await axios.delete('/api/sender/logo')
        logoRemoved.value = false
        logoEntry.type    = 'success'
        logoEntry.message = 'Logo removed'
      } catch {
        logoEntry.type    = 'error'
        logoEntry.message = 'Failed to remove logo'
      }
    // Logo upload
    } else if (logoFile.value) {
      const logoEntry = addLog('pending', 'Uploading logo…')
      try {
        const fd = new FormData()
        fd.append('logo', logoFile.value)
        await axios.post('/api/sender/logo', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        logoFile.value    = null
        logoEntry.type    = 'success'
        logoEntry.message = 'Logo uploaded'
      } catch {
        logoEntry.type    = 'error'
        logoEntry.message = 'Failed to upload logo'
      }
    }

    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } finally {
    saving.value = false
  }
}
```

Note: `throw` in the profile catch propagates to the outer `try/finally`, which runs `saving.value = false` before the error is swallowed. The `saved.value = true` line is only reached if the profile save succeeded.

- [ ] **Step 4.3 — Verify in browser**

With dev server running, go to My Profile tab:
1. Fill in all fields and save → log shows "Saving profile…" → "Profile saved"
2. Upload a logo and save → log shows "Saving profile…" → "Profile saved", then "Uploading logo…" → "Logo uploaded"
3. Click "Remove logo" then save → log shows "Saving profile…" → "Profile saved", then "Removing logo…" → "Logo removed"
4. Clear the log → panel resets to "No activity yet."

- [ ] **Step 4.4 — Commit**

```bash
git add resources/js/components/SenderProfile.vue
git commit -m "feat: instrument SenderProfile with activity log entries"
```

---

## Task 5: Instrument `NewInvoice.vue` with real error extraction

**Files:**
- Modify: `resources/js/components/NewInvoice.vue`

**Important:** `generate()` uses `responseType: 'blob'`. When the server returns an error (4xx/5xx), axios receives the body as a `Blob`, not a parsed JSON object. `e.response?.data` will be a `Blob`. The error must be read via `blob.text()` before parsing as JSON.

- [ ] **Step 5.1 — Add the import**

At the top of `<script setup>`, add:
```js
import { useActivityLog } from '../composables/useActivityLog'
const { addLog } = useActivityLog()
```

- [ ] **Step 5.2 — Add blob error extraction helper**

Add this function anywhere in `<script setup>` before `generate()`:

```js
async function extractError(e) {
  if (e.response?.data instanceof Blob) {
    try {
      const text = await e.response.data.text()
      const json = JSON.parse(text)
      return json.message
        || Object.values(json.errors ?? {}).flat().join(' ')
        || 'Server error'
    } catch {
      return 'Server error'
    }
  }
  return e.response?.data?.message
    || Object.values(e.response?.data?.errors ?? {}).flat().join(' ')
    || e.message
    || 'Unknown error'
}
```

- [ ] **Step 5.3 — Replace the body of `generate()` from `generating.value = true` to the closing brace**

The pre-flight guard block (lines 259–264, the early-return checks for recipient name and line item descriptions) stays untouched. Replace everything from `generating.value = true` through the closing `}` of `generate()`:

```js
  generating.value = true
  const logEntry   = addLog('pending', 'Generating PDF…')
  try {
    if (saveRecipient.value && !selectedRecipientId.value) {
      const { data } = await axios.post('/api/recipients', recipient.value)
      recipients.value.push(data)
      selectedRecipientId.value = data.id
      saveRecipient.value = false
    }

    const payload = useQty.value
      ? lineItems.value.map(i => ({
          description: i.description,
          qty:         parseFloat(i.qty) || 1,
          rate:        parseFloat(i.rate) || 0,
          amount:      (parseFloat(i.qty) || 1) * (parseFloat(i.rate) || 0),
        }))
      : lineItems.value

    const response = await axios.post('/api/invoice/generate', {
      recipient:  recipient.value,
      line_items: payload,
      notes:      notes.value,
    }, { responseType: 'blob' })

    const invoiceNumber = response.headers['x-invoice-number'] || 'Invoice'
    logEntry.type    = 'success'
    logEntry.message = `${invoiceNumber} generated`

    const url  = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href  = url
    link.download = invoiceNumber + '.pdf'
    link.click()
    URL.revokeObjectURL(url)

    lineItems.value = [useQty.value ? { description:'', qty:'', rate:'' } : { description:'', amount:'' }]
    notes.value     = ''
  } catch (e) {
    const msg        = await extractError(e)
    logEntry.type    = 'error'
    logEntry.message = msg
    error.value      = msg
  } finally {
    generating.value = false
  }
}
```

The `error.value = msg` line keeps the existing inline error banner working alongside the log entry. The `link.download` now uses the already-extracted `invoiceNumber` variable instead of re-reading the header.

- [ ] **Step 5.4 — Verify in browser**

With dev server running, go to New Invoice tab:
1. Fill in a recipient and line item, generate → log shows "Generating PDF…" → "INV-YYYY-NNNN generated"
2. Try generating without a sender profile → log shows "Generating PDF…" → actual server error message (not the old generic string)
3. Check that the inline error banner also shows the real message

- [ ] **Step 5.5 — Run backend tests**

```bash
composer run test
```
Expected: all tests pass.

- [ ] **Step 5.6 — Commit**

```bash
git add resources/js/components/NewInvoice.vue
git commit -m "feat: instrument NewInvoice with activity log, fix error extraction"
```
