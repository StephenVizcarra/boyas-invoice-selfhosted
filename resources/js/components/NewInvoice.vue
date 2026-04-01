<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">New Invoice</h1>
    </div>

    <!-- Bill To -->
    <div class="card" style="margin-bottom: 12px;">
      <div class="card-body">
        <h2 class="section-label">Bill To</h2>

        <select v-model="selectedRecipientId" class="select-input" @change="onRecipientSelect" style="margin-bottom: 16px;">
          <option value="">— New recipient —</option>
          <option v-for="r in recipients" :key="r.id" :value="r.id">
            {{ r.name }}{{ r.company ? ' · ' + r.company : '' }}
          </option>
        </select>

        <div class="field-grid">
          <div class="field">
            <label class="field-label">Name <span class="required">*</span></label>
            <input v-model="recipient.name" type="text" required placeholder="Recipient name" class="field-input">
          </div>
          <div class="field">
            <label class="field-label">Company</label>
            <input v-model="recipient.company" type="text" placeholder="Company name" class="field-input">
          </div>
          <div class="field">
            <label class="field-label">Email</label>
            <input v-model="recipient.email" type="email" placeholder="email@example.com" class="field-input">
          </div>
          <div class="field">
            <label class="field-label">Address</label>
            <input v-model="recipient.address" type="text" placeholder="123 Main St" class="field-input">
          </div>
          <div class="field field--wide">
            <label class="field-label">City, State ZIP</label>
            <input v-model="recipient.city_state_zip" type="text" placeholder="New York, NY 10001" class="field-input">
          </div>
        </div>

        <label class="save-check">
          <input type="checkbox" v-model="saveRecipient" class="check-input">
          Save this recipient for future invoices
        </label>
      </div>
    </div>

    <!-- Line Items -->
    <div class="card" style="margin-bottom: 12px;">
      <div class="card-body">
        <div class="section-header-row">
          <h2 class="section-label" style="margin-bottom:0;">Line Items</h2>
          <button type="button" class="qty-toggle" :class="{ 'qty-toggle--on': useQty }" @click="toggleQty">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
              <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            {{ useQty ? 'Hide Qty' : 'Add Qty' }}
          </button>
        </div>

        <table class="items-table" style="margin-top: 14px;">
          <thead>
            <tr>
              <th class="col-desc">Description</th>
              <template v-if="useQty">
                <th class="col-qty">Qty</th>
                <th class="col-rate">Rate</th>
                <th class="col-amount">Amount</th>
              </template>
              <template v-else>
                <th class="col-amount">Amount</th>
              </template>
              <th class="col-action"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in lineItems" :key="i" class="item-row">
              <td>
                <input
                  v-model="item.description"
                  type="text"
                  placeholder="Service or product description"
                  class="table-input"
                >
              </td>
              <template v-if="useQty">
                <td class="col-qty">
                  <input
                    v-model="item.qty"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="1"
                    class="table-input"
                    style="text-align: right;"
                  >
                </td>
                <td class="col-rate">
                  <div class="amount-wrap">
                    <span class="amount-prefix">$</span>
                    <input
                      v-model="item.rate"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="table-input amount-input"
                    >
                  </div>
                </td>
                <td class="col-amount">
                  <div class="amount-wrap amount-computed">
                    <span class="amount-prefix">$</span>
                    <span class="computed-value">{{ lineItemAmount(item) }}</span>
                  </div>
                </td>
              </template>
              <template v-else>
                <td class="col-amount">
                  <div class="amount-wrap">
                    <span class="amount-prefix">$</span>
                    <input
                      v-model="item.amount"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="table-input amount-input"
                    >
                  </div>
                </td>
              </template>
              <td class="col-action">
                <button
                  type="button"
                  class="remove-row-btn"
                  :disabled="lineItems.length === 1"
                  @click="removeItem(i)"
                  title="Remove row"
                >
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="items-footer">
          <button type="button" class="add-row-btn" @click="addItem">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add item
          </button>
          <div class="total-row">
            <span class="total-label">Total</span>
            <span class="total-amount">${{ total }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="card" style="margin-bottom: 20px;">
      <div class="card-body">
        <h2 class="section-label">
          Notes <span class="section-label-opt">(optional)</span>
        </h2>
        <textarea
          v-model="notes"
          class="notes-input"
          placeholder="Payment terms, thank you note, bank details…"
          rows="3"
        ></textarea>
      </div>
    </div>

    <!-- Generate -->
    <div class="actions">
      <button class="btn-primary" :disabled="generating" @click="generate">
        <svg v-if="!generating" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
        </svg>
        <svg v-else class="spin" width="15" height="15" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.75s" repeatCount="indefinite"/>
          </circle>
        </svg>
        {{ generating ? 'Generating PDF…' : 'Generate Invoice PDF' }}
      </button>
      <p v-if="error" class="error-msg">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useActivityLog } from '../composables/useActivityLog'

const { addLog } = useActivityLog()

const recipients          = ref([])
const selectedRecipientId = ref('')
const saveRecipient       = ref(false)
const recipient           = ref({ name:'', company:'', address:'', city_state_zip:'', email:'' })
const lineItems           = ref([{ description:'', amount:'' }])
const useQty              = ref(false)
const notes               = ref('')
const generating          = ref(false)
const error               = ref('')

function lineItemAmount(item) {
  return ((parseFloat(item.qty) || 0) * (parseFloat(item.rate) || 0)).toFixed(2)
}

const total = computed(() => {
  const sum = lineItems.value.reduce((acc, item) => {
    const amount = useQty.value
      ? (parseFloat(item.qty) || 0) * (parseFloat(item.rate) || 0)
      : (parseFloat(item.amount) || 0)
    return acc + amount
  }, 0)
  return sum.toFixed(2)
})

function toggleQty() {
  useQty.value = !useQty.value
  lineItems.value = [useQty.value
    ? { description:'', qty:'', rate:'' }
    : { description:'', amount:'' }]
}

onMounted(async () => {
  const { data } = await axios.get('/api/recipients')
  recipients.value = data
})

function onRecipientSelect() {
  if (!selectedRecipientId.value) {
    recipient.value = { name:'', company:'', address:'', city_state_zip:'', email:'' }
    return
  }
  const r = recipients.value.find(r => r.id === selectedRecipientId.value)
  if (r) Object.assign(recipient.value, r)
}

function addItem() {
  lineItems.value.push(useQty.value
    ? { description:'', qty:'', rate:'' }
    : { description:'', amount:'' })
}
function removeItem(i) { lineItems.value.splice(i, 1) }

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

async function generate() {
  error.value = ''
  if (!recipient.value.name) { error.value = 'Recipient name is required.'; return }
  if (lineItems.value.some(i => !i.description)) {
    error.value = 'All line items need a description.'
    return
  }

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
          qty:         parseFloat(i.qty) || 0,
          rate:        parseFloat(i.rate) || 0,
          amount:      (parseFloat(i.qty) || 0) * (parseFloat(i.rate) || 0),
        }))
      : lineItems.value

    const response = await axios.post('/api/invoice/generate', {
      recipient:  recipient.value,
      line_items: payload,
      notes:      notes.value,
    }, { responseType: 'blob' })

    const invoiceNumber  = response.headers['x-invoice-number'] || 'Invoice'
    logEntry.type        = 'success'
    logEntry.message     = `${invoiceNumber} generated`

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
</script>

<style scoped>
.page { max-width: 700px; }

/* Recipient */
.select-input {
  width: 100%;
  padding: 8px 11px;
  border: 1.5px solid #e7e5e4;
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: #1c1917;
  background: #fafaf9;
  cursor: pointer;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.select-input:focus {
  border-color: #d97706;
  box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
}

.save-check {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  font-size: 13px;
  color: #57534e;
  cursor: pointer;
}

.check-input { cursor: pointer; accent-color: #d97706; }

.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0;
}

.qty-toggle {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border: 1.5px solid #e7e5e4;
  border-radius: 5px;
  background: #fafaf9;
  color: #78716c;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.qty-toggle:hover {
  border-color: #d97706;
  color: #d97706;
}

.qty-toggle--on {
  border-color: #d97706;
  background: #fffbeb;
  color: #d97706;
}

/* Line items */
.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table thead th {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #a8a29e;
  padding: 0 0 10px;
  border-bottom: 1.5px solid #f5f4f0;
  text-align: left;
}

.col-desc   { width: auto; }
.col-qty    { width: 80px; }
.col-rate   { width: 130px; padding-left: 12px !important; }
.col-amount { width: 130px; padding-left: 12px !important; }
.col-action { width: 38px; }

.amount-computed { cursor: default; }
.computed-value {
  font-size: 14px;
  color: #57534e;
  padding: 7px 9px 7px 2px;
  font-weight: 500;
}

.item-row td {
  padding: 7px 0;
  border-bottom: 1px solid #f9f8f6;
  vertical-align: middle;
}

.item-row:last-child td { border-bottom: none; }

.table-input {
  width: 100%;
  border: 1.5px solid transparent;
  border-radius: 5px;
  padding: 7px 9px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: #1c1917;
  background: transparent;
  outline: none;
  transition: border-color 0.13s, background 0.13s;
}

.table-input:hover {
  border-color: #e7e5e4;
  background: #fafaf9;
}

.table-input:focus {
  border-color: #d97706;
  box-shadow: 0 0 0 3px rgba(217,119,6,0.1);
  background: #fff;
}

.table-input::placeholder { color: #c4bfbb; }

.amount-wrap {
  display: flex;
  align-items: center;
  padding-left: 12px;
}

.amount-prefix {
  font-size: 14px;
  color: #a8a29e;
  flex-shrink: 0;
  margin-right: 2px;
}

.amount-input { padding-left: 2px; }

.remove-row-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 5px;
  border: none;
  background: none;
  color: #d6d3d1;
  cursor: pointer;
  transition: background 0.12s, color 0.12s;
  margin-left: 8px;
}

.remove-row-btn:hover:not(:disabled) {
  background: #fef2f2;
  color: #dc2626;
}

.remove-row-btn:disabled { opacity: 0.3; cursor: not-allowed; }

.items-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10px;
  padding-top: 12px;
  border-top: 1px solid #f5f4f0;
}

.add-row-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 600;
  color: #d97706;
  cursor: pointer;
  font-family: 'Figtree', sans-serif;
  padding: 4px 0;
  transition: color 0.12s;
}

.add-row-btn:hover { color: #b45309; }

.total-row {
  display: flex;
  align-items: center;
  gap: 14px;
}

.total-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #a8a29e;
}

.total-amount {
  font-size: 20px;
  font-weight: 700;
  color: #1c1917;
  letter-spacing: -0.02em;
}

/* Notes */
.notes-input {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid #e7e5e4;
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: #1c1917;
  background: #fafaf9;
  resize: vertical;
  min-height: 80px;
  outline: none;
  line-height: 1.55;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.notes-input:focus {
  border-color: #d97706;
  box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
  background: #fff;
}

.notes-input::placeholder { color: #c4bfbb; }

/* Actions */
.actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.error-msg {
  font-size: 13px;
  color: #dc2626;
  font-weight: 500;
}
</style>
