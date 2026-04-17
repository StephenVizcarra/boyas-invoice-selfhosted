<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">History</h1>
      <p class="page-sub">Previously generated invoices</p>
    </div>

    <div v-if="loading" class="state-card card">
      <div class="card-body state-body">
        <svg class="spin" width="20" height="20" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="#d97706" stroke-width="2.5" fill="none"
            stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12"
              dur="0.75s" repeatCount="indefinite"/>
          </circle>
        </svg>
        <span>Loading invoices…</span>
      </div>
    </div>

    <div v-else-if="invoices.length === 0" class="state-card card">
      <div class="card-body state-body">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d6d3d1" stroke-width="1.5">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <p class="empty-title">No invoices yet</p>
        <p class="empty-sub">Generate one under New Invoice to see it here.</p>
      </div>
    </div>

    <div v-else class="tile-grid">
      <InvoiceHistoryTile
        v-for="inv in invoices"
        :key="inv.number"
        :invoice="inv"
        @download="downloadInvoice(inv)"
        @delete="deleteInvoice(inv)"
        @duplicate="$emit('duplicate', inv)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onActivated } from 'vue'
import axios from 'axios'
import InvoiceHistoryTile from './InvoiceHistoryTile.vue'
import { useActivityLog } from '../composables/useActivityLog'
import { usePdfThumbnail } from '../composables/usePdfThumbnail'

const emit = defineEmits(['duplicate'])

const { addLog } = useActivityLog()
const { clearThumbnailCache } = usePdfThumbnail()

const invoices = ref([])
const loading  = ref(true)

async function fetchInvoices() {
  loading.value = true
  const logEntry = addLog('pending', 'Loading invoice history…')
  try {
    const { data } = await axios.get('/api/invoices')
    invoices.value = data
    logEntry.type    = 'success'
    logEntry.message = `Loaded ${data.length} invoice${data.length !== 1 ? 's' : ''}`
  } catch {
    logEntry.type    = 'error'
    logEntry.message = 'Failed to load invoice history'
  } finally {
    loading.value = false
  }
}

onMounted(fetchInvoices)
onActivated(fetchInvoices)

async function downloadInvoice(invoice) {
  try {
    const response = await axios.get(`/api/invoices/${invoice.number}`, { responseType: 'blob' })
    const url  = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href     = url
    link.download = invoice.number + '.pdf'
    link.click()
    URL.revokeObjectURL(url)
  } catch {
    // silent — download failures are visible to the user via the browser
  }
}

async function deleteInvoice(invoice) {
  const logEntry = addLog('pending', `Deleting ${invoice.number}…`)
  try {
    await axios.delete(`/api/invoices/${invoice.number}`)
    clearThumbnailCache(invoice.number)
    invoices.value = invoices.value.filter(i => i.number !== invoice.number)
    logEntry.type    = 'success'
    logEntry.message = `Deleted ${invoice.number}`
  } catch {
    logEntry.type    = 'error'
    logEntry.message = `Failed to delete ${invoice.number}`
  }
}
</script>

<style scoped>
.page { max-width: 900px; }

.state-card {
  margin-top: 40px;
}

.state-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 48px 24px;
  color: #a8a29e;
  font-size: 14px;
}

.empty-title {
  font-size: 15px;
  font-weight: 600;
  color: #78716c;
}

.empty-sub {
  font-size: 13px;
  color: #a8a29e;
}

.tile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}
</style>
