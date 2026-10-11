<template>
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
            <th class="col-handle"></th>
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
          <tr
            v-for="(item, i) in lineItems"
            :key="i"
            class="item-row"
            :class="{ 'item-row--drag-over': dragOverIndex === i }"
            draggable="true"
            @dragstart="onDragStart(i)"
            @dragover.prevent="onDragOver(i)"
            @drop.prevent="onDrop(i)"
            @dragend="onDragEnd"
          >
            <td class="col-handle">
              <span class="drag-handle" title="Drag to reorder">
                <svg width="10" height="14" viewBox="0 0 10 14" fill="currentColor">
                  <circle cx="2.5" cy="2"  r="1.3"/>
                  <circle cx="7.5" cy="2"  r="1.3"/>
                  <circle cx="2.5" cy="7"  r="1.3"/>
                  <circle cx="7.5" cy="7"  r="1.3"/>
                  <circle cx="2.5" cy="12" r="1.3"/>
                  <circle cx="7.5" cy="12" r="1.3"/>
                </svg>
              </span>
            </td>
            <td>
              <input
                v-model="item.description"
                type="text"
                placeholder="Service or product description"
                class="table-input"
                :class="{ 'field-error': errorFields.lineItems?.[i]?.description }"
                @input="$emit('clear-line-item-error', i, 'description')"
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
                  :class="{ 'field-error': errorFields.lineItems?.[i]?.qty }"
                  @input="$emit('clear-line-item-error', i, 'qty')"
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
                    :class="{ 'field-error': errorFields.lineItems?.[i]?.rate }"
                    @input="$emit('clear-line-item-error', i, 'rate')"
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
                    :class="{ 'field-error': errorFields.lineItems?.[i]?.amount }"
                    @input="$emit('clear-line-item-error', i, 'amount')"
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
</template>

<script setup>
import { ref, computed } from 'vue'

const lineItems = defineModel('lineItems', { type: Array, required: true })
const useQty = defineModel('useQty', { type: Boolean, default: false })

defineProps({
  errorFields: { type: Object, default: () => ({}) },
})

defineEmits(['clear-line-item-error'])

const dragIndex     = ref(null)
const dragOverIndex = ref(null)

function onDragStart(i) { dragIndex.value = i }
function onDragOver(i)  { dragOverIndex.value = i }
function onDragEnd()    { dragIndex.value = null; dragOverIndex.value = null }
function onDrop(i) {
  const from = dragIndex.value
  if (from === null || from === i) return
  const items = [...lineItems.value]
  items.splice(i, 0, items.splice(from, 1)[0])
  lineItems.value = items
  dragIndex.value = null
  dragOverIndex.value = null
}

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
  if (useQty.value) {
    lineItems.value = lineItems.value.map(item => {
      const hasValue = item.qty !== '' && item.qty !== null && item.rate !== '' && item.rate !== null
      return {
        description: item.description,
        amount:      hasValue ? ((parseFloat(item.qty) || 0) * (parseFloat(item.rate) || 0)).toFixed(2) : '',
      }
    })
  } else {
    lineItems.value = lineItems.value.map(item => {
      const hasValue = item.amount !== '' && item.amount !== null
      return {
        description: item.description,
        qty:         hasValue ? 1 : '',
        rate:        hasValue ? parseFloat(item.amount) || 0 : '',
      }
    })
  }
  useQty.value = !useQty.value
}

function addItem() {
  lineItems.value.push(useQty.value
    ? { description: '', qty: '', rate: '' }
    : { description: '', amount: '' })
}

function removeItem(i) { lineItems.value.splice(i, 1) }
</script>

<style scoped>
.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0;
}

.qty-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border: 1.5px solid var(--border);
  border-radius: 5px;
  background: var(--bg-input);
  color: var(--text-muted);
  font-size: 13px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.qty-toggle:hover {
  border-color: var(--accent);
  color: var(--accent);
}

.qty-toggle--on {
  border-color: var(--accent);
  background: var(--accent-bg);
  color: var(--accent);
}

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

.col-handle { width: 28px; }
.col-desc   { width: auto; }
.col-qty    { width: 80px; }
.col-rate   { width: 130px; padding-left: 12px !important; }
.col-amount { width: 130px; padding-left: 12px !important; }
.col-action { width: 38px; }

.drag-handle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 26px;
  color: #d6d3d1;
  cursor: grab;
  border-radius: 4px;
  transition: color 0.12s, background 0.12s;
}

.drag-handle:hover {
  color: #a8a29e;
  background: #f5f4f0;
}

.item-row--drag-over td {
  border-top: 2px solid #d97706;
}

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
  color: var(--text-primary);
  background: transparent;
  outline: none;
  transition: border-color 0.13s, background 0.13s;
}

.table-input:hover {
  border-color: var(--border);
  background: var(--bg-input);
}

.table-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
  background: var(--bg-card);
}

.table-input::placeholder { color: var(--text-faint); }

.amount-wrap {
  display: flex;
  align-items: center;
  padding-left: 12px;
}

.amount-prefix {
  font-size: 14px;
  color: var(--text-subtle);
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
</style>
