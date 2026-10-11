<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">New Invoice</h1>
    </div>

    <InvoiceBillTo
      v-model:recipient="recipient"
      v-model:selected-recipient-id="selectedRecipientId"
      :error-fields="errorFields"
      @clear-field-error="clearFieldError"
    />

    <InvoiceLineItems
      v-model:line-items="lineItems"
      v-model:use-qty="useQty"
      :error-fields="errorFields"
      @clear-line-item-error="clearLineItemError"
    />

    <InvoiceNotes v-model="notes" />

    <InvoiceGenerateBar
      :generating="generating"
      :error="error"
      :dev-mode="devMode"
      @generate="generate"
      @fill-test="fillTestData"
    />
  </div>

  <InvoiceEmailModal
    v-model:open="showEmailModal"
    v-model:email-to="emailTo"
    v-model:email-subject="emailSubject"
    v-model:email-body="emailBody"
  />
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useActivityLog } from '../composables/useActivityLog'
import { useDevMode } from '../composables/useDevMode'
import { useDefaultNotes } from '../composables/useDefaultNotes'
import InvoiceBillTo from './InvoiceBillTo.vue'
import InvoiceLineItems from './InvoiceLineItems.vue'
import InvoiceNotes from './InvoiceNotes.vue'
import InvoiceGenerateBar from './InvoiceGenerateBar.vue'
import InvoiceEmailModal from './InvoiceEmailModal.vue'

const props = defineProps({
  prefillSeed: { type: Object, default: null },
})

const emit = defineEmits(['prefill-consumed'])

const { addLog } = useActivityLog()
const { devMode } = useDevMode()
const { defaultNotes } = useDefaultNotes()

const selectedRecipientId = ref('')
const recipient           = ref({ name: '', company: '', address: '', city_state_zip: '', email: '' })
const lineItems           = ref([{ description: '', amount: '' }])
const useQty              = ref(false)
const notes               = ref(defaultNotes.value || '')
const generating          = ref(false)
const error               = ref('')
const errorFields         = ref({})

const showEmailModal = ref(false)
const emailTo        = ref('')
const emailSubject   = ref('')
const emailBody      = ref('')

watch(() => props.prefillSeed, (seed) => {
  if (!seed) return

  const isDirty = recipient.value.name || lineItems.value.some(i => i.description)
  if (isDirty && !confirm('Replace the current draft with this invoice?')) return

  selectedRecipientId.value = ''
  recipient.value           = { ...seed.recipient }
  notes.value               = seed.notes || ''

  const hasQty = seed.line_items.some(i => i.qty != null)
  useQty.value = hasQty
  lineItems.value = seed.line_items.map(item =>
    hasQty
      ? { description: item.description, qty: item.qty ?? 1, rate: item.rate ?? 0 }
      : { description: item.description, amount: item.amount ?? 0 }
  )

  addLog('success', `Duplicated ${seed.number} into new draft`)
  emit('prefill-consumed')
})

const TEST_INVOICES = [
  {
    recipient: { name: 'Daniel Okafor',    company: 'Okafor & Partners LLC',       email: 'daniel@okaforpartners.com',    address: '550 Fifth Avenue, Floor 12',    city_state_zip: 'New York, NY 10036'      },
    items:     [ { description: 'Web Design Services', qty: 1, rate: 2400, amount: 2400 }, { description: 'Consulting Hours', qty: 8, rate: 175, amount: 1400 }, { description: 'Monthly Retainer — April', qty: 1, rate: 1200, amount: 1200 } ],
    notes:     'Payment due within 30 days. Please make checks payable to Okafor & Partners LLC or pay via bank transfer. Thank you for your business!',
  },
  {
    recipient: { name: 'Rachel Goldstein', company: 'Goldstein Media Group',        email: 'rachel@goldsteinmg.com',       address: '220 Bush Street, Suite 1400',   city_state_zip: 'San Francisco, CA 94104' },
    items:     [ { description: 'Brand Identity Package', qty: 1, rate: 3500, amount: 3500 }, { description: 'Social Media Assets', qty: 12, rate: 85, amount: 1020 }, { description: 'Strategy Session', qty: 2, rate: 300, amount: 600 } ],
    notes:     'Net 15. All deliverables provided as agreed in the project brief dated March 3rd. Thank you for the opportunity to work together.',
  },
  {
    recipient: { name: 'Thomas Nguyen',    company: 'Nguyen Construction Inc.',     email: 'thomas@nguyenconstruction.com', address: '9200 Sunset Blvd, Suite 600',   city_state_zip: 'Los Angeles, CA 90069'   },
    items:     [ { description: 'Project Management — Q2', qty: 1, rate: 5000, amount: 5000 }, { description: 'Site Assessment Report', qty: 1, rate: 750, amount: 750 } ],
    notes:     'Invoice covers services rendered April 1–30. Please remit payment by May 15th to avoid a 1.5% monthly late fee.',
  },
  {
    recipient: { name: 'Sofia Alvarez',    company: 'Alvarez Event Planning',       email: 'sofia@alvarezeventco.com',     address: '401 N. Michigan Avenue',        city_state_zip: 'Chicago, IL 60611'       },
    items:     [ { description: 'Event Coordination (full day)', qty: 3, rate: 1800, amount: 5400 }, { description: 'Vendor Sourcing Fee', qty: 1, rate: 400, amount: 400 }, { description: 'Post-Event Summary Report', qty: 1, rate: 250, amount: 250 } ],
    notes:     'A pleasure working with you on the spring series. Reach out anytime for future bookings. Payment via ACH preferred.',
  },
  {
    recipient: { name: 'Marcus Webb',      company: '',                             email: 'marcus.webb@gmail.com',        address: '74 Pinecrest Drive',            city_state_zip: 'Austin, TX 78704'        },
    items:     [ { description: 'Freelance Development — Sprint 4', qty: 40, rate: 120, amount: 4800 }, { description: 'Code Review & Documentation', qty: 5, rate: 120, amount: 600 } ],
    notes:     'Hours logged in Toggl — report attached. Thanks Marcus, always a smooth project.',
  },
]

let lastInvoiceIndex = -1

function fillTestData() {
  let idx
  do { idx = Math.floor(Math.random() * TEST_INVOICES.length) } while (idx === lastInvoiceIndex && TEST_INVOICES.length > 1)
  lastInvoiceIndex = idx
  const preset = TEST_INVOICES[idx]

  selectedRecipientId.value = ''
  recipient.value = { ...preset.recipient }
  lineItems.value = preset.items.map(item =>
    useQty.value
      ? { description: item.description, qty: item.qty,    rate:   item.rate   }
      : { description: item.description, amount: item.amount }
  )
  notes.value = preset.notes
  addLog('success', `Invoice test data filled (${preset.recipient.name})`)
}

function humanizeErrors(errors) {
  return Object.entries(errors).map(([field, messages]) => {
    const match = field.match(/^line_items\.(\d+)\.(.+)$/)
    if (match) return `Row ${parseInt(match[1]) + 1}: ${match[2]} is invalid.`
    return messages[0]
  }).join(' ')
}

async function extractError(e) {
  if (e.response?.data instanceof Blob) {
    try {
      const text = await e.response.data.text()
      const json = JSON.parse(text)
      if (json.errors) return humanizeErrors(json.errors)
      return json.message || 'Server error'
    } catch {
      return 'Server error'
    }
  }
  const data = e.response?.data
  if (data?.errors) return humanizeErrors(data.errors)
  return data?.message || e.message || 'Unknown error'
}

function clearFieldError(field) {
  if (errorFields.value[field]) {
    delete errorFields.value[field]
    errorFields.value = { ...errorFields.value }
  }
  if (error.value) error.value = ''
}

function clearLineItemError(index, field) {
  if (errorFields.value.lineItems?.[index]?.[field]) {
    delete errorFields.value.lineItems[index][field]
    errorFields.value = { ...errorFields.value }
  }
  if (error.value) error.value = ''
}

function isRowEmpty(item) {
  const desc = (item.description || '').trim()
  if (useQty.value) {
    const qty = item.qty === '' || item.qty === null
    const rate = item.rate === '' || item.rate === null
    return !desc && qty && rate
  }
  const amt = item.amount === '' || item.amount === null
  return !desc && amt
}

function scrollToError() {
  setTimeout(() => {
    const el = document.querySelector('.field-error')
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }, 50)
}

async function generate() {
  error.value = ''
  errorFields.value = {}

  let hasError = false
  const errors = { lineItems: {} }

  if (!recipient.value.name) {
    errors.recipientName = true
    error.value = 'Please enter a recipient name.'
    hasError = true
  }

  const nonEmptyItems = lineItems.value.filter(item => !isRowEmpty(item))

  if (nonEmptyItems.length === 0) {
    const firstIndex = 0
    errors.lineItems[firstIndex] = { description: true }
    error.value = 'Please add at least one line item.'
    hasError = true
  } else {
    for (const item of nonEmptyItems) {
      const i = lineItems.value.indexOf(item)
      const desc = (item.description || '').trim()
      const itemErrors = {}

      if (!desc) {
        itemErrors.description = true
        if (!error.value) error.value = 'Please enter a description for each line item.'
        hasError = true
      }

      if (useQty.value) {
        const qtyVal = parseFloat(item.qty)
        const rateVal = parseFloat(item.rate)
        if (item.qty === '' || item.qty === null || isNaN(qtyVal) || qtyVal < 0) {
          itemErrors.qty = true
          if (!error.value) error.value = 'Please enter a valid quantity.'
          hasError = true
        }
        if (item.rate === '' || item.rate === null || isNaN(rateVal) || rateVal < 0) {
          itemErrors.rate = true
          if (!error.value) error.value = 'Please enter a valid rate.'
          hasError = true
        }
      } else {
        const amtVal = parseFloat(item.amount)
        if (item.amount === '' || item.amount === null || isNaN(amtVal) || amtVal < 0) {
          itemErrors.amount = true
          if (!error.value) error.value = 'Please enter a valid amount.'
          hasError = true
        }
      }

      if (Object.keys(itemErrors).length > 0) {
        errors.lineItems[i] = itemErrors
      }
    }
  }

  if (hasError) {
    errorFields.value = errors
    scrollToError()
    return
  }

  generating.value = true
  const logEntry   = addLog('pending', 'Generating PDF…')
  try {
    const validItems = lineItems.value.filter(item => !isRowEmpty(item))
    const payload = useQty.value
      ? validItems.map(i => ({
          description: i.description.trim(),
          qty:         parseFloat(i.qty),
          rate:        parseFloat(i.rate),
          amount:      parseFloat(i.qty) * parseFloat(i.rate),
        }))
      : validItems.map(i => ({
          description: i.description.trim(),
          amount:      parseFloat(i.amount),
        }))

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

    emailTo.value      = recipient.value.email || ''
    emailSubject.value = `Invoice ${invoiceNumber}`
    emailBody.value    = `Hi ${recipient.value.name},\n\nPlease see the attached invoice ${invoiceNumber} below .\n\nThanks again,\n\n<Your name here>`
    showEmailModal.value = true

    lineItems.value = [useQty.value ? { description: '', qty: '', rate: '' } : { description: '', amount: '' }]
    notes.value     = defaultNotes.value || ''
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
</style>
