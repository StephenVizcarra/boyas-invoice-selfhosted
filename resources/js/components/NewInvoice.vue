<template>
  <div>
    <h2 style="font-size:16px; font-weight:600; margin-bottom:20px;">New Invoice</h2>

    <!-- Recipient -->
    <section style="margin-bottom:28px;">
      <h3 :style="sectionHeading">Recipient</h3>

      <div style="margin-bottom:12px;">
        <label :style="labelStyle">Select saved recipient</label>
        <select v-model="selectedRecipientId" @change="onRecipientSelect" :style="inputStyle">
          <option value="">— New recipient —</option>
          <option v-for="r in recipients" :key="r.id" :value="r.id">
            {{ r.name }}{{ r.company ? ' · ' + r.company : '' }}
          </option>
        </select>
      </div>

      <div style="max-width:480px;">
        <div v-for="field in recipientFields" :key="field.key" style="margin-bottom:12px;">
          <label :style="labelStyle">{{ field.label }}</label>
          <input v-model="recipient[field.key]" :type="field.type||'text'" :required="field.required" :style="inputStyle">
        </div>

        <label :style="{ ...labelStyle, display:'flex', alignItems:'center', gap:'8px', textTransform:'none', letterSpacing:'0', fontWeight:'400', fontSize:'13px', cursor:'pointer' }">
          <input type="checkbox" v-model="saveRecipient"> Save this recipient for future use
        </label>
      </div>
    </section>

    <!-- Line Items -->
    <section style="margin-bottom:28px;">
      <h3 :style="sectionHeading">Line Items</h3>

      <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
        <thead>
          <tr>
            <th :style="thStyle">Description</th>
            <th :style="{ ...thStyle, width:'130px', textAlign:'right' }">Amount ($)</th>
            <th style="width:36px;"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in lineItems" :key="i">
            <td style="padding:6px 6px 6px 0;">
              <input v-model="item.description" placeholder="e.g. Design work" :style="{ ...inputStyle, width:'100%' }" required>
            </td>
            <td style="padding:6px;">
              <input v-model="item.amount" type="number" min="0" step="0.01" placeholder="0.00"
                :style="{ ...inputStyle, textAlign:'right' }" required>
            </td>
            <td style="padding:6px 0 6px 6px; text-align:center;">
              <button @click="removeItem(i)" :style="removeBtnStyle" :disabled="lineItems.length === 1">✕</button>
            </td>
          </tr>
        </tbody>
      </table>

      <button @click="addItem" :style="ghostBtnStyle">+ Add item</button>

      <div style="text-align:right; margin-top:16px; font-size:15px; font-weight:600;">
        Total: ${{ total }}
      </div>
    </section>

    <!-- Notes -->
    <section style="margin-bottom:28px; max-width:600px;">
      <h3 :style="sectionHeading">Notes <span style="color:#999; font-weight:400;">(optional)</span></h3>
      <textarea v-model="notes" rows="3" placeholder="Payment terms, thank you message, etc."
        style="width:100%; padding:8px 10px; border:1.5px solid #ddd; border-radius:4px; font-size:14px; resize:vertical;"></textarea>
    </section>

    <!-- Generate -->
    <div style="display:flex; align-items:center; gap:14px;">
      <button @click="generate" :style="btnStyle" :disabled="generating">
        {{ generating ? 'Generating…' : 'Generate PDF' }}
      </button>
      <span v-if="error" style="color:#c0392b; font-size:13px;">{{ error }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const recipients      = ref([])
const selectedRecipientId = ref('')
const saveRecipient   = ref(false)
const recipient       = ref({ name:'', company:'', address:'', city_state_zip:'', email:'' })
const lineItems       = ref([{ description:'', amount:'' }])
const notes           = ref('')
const generating      = ref(false)
const error           = ref('')

const recipientFields = [
  { key:'name',          label:'Name',         required:true },
  { key:'company',       label:'Company' },
  { key:'address',       label:'Address' },
  { key:'city_state_zip',label:'City, State ZIP' },
  { key:'email',         label:'Email', type:'email' },
]

const total = computed(() => {
  const sum = lineItems.value.reduce((acc, i) => acc + (parseFloat(i.amount) || 0), 0)
  return sum.toFixed(2)
})

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

function addItem()    { lineItems.value.push({ description:'', amount:'' }) }
function removeItem(i){ lineItems.value.splice(i, 1) }

async function generate() {
  error.value = ''
  if (!recipient.value.name) { error.value = 'Recipient name is required.'; return }
  if (lineItems.value.some(i => !i.description || !i.amount)) {
    error.value = 'All line items need a description and amount.'
    return
  }

  generating.value = true
  try {
    if (saveRecipient.value && !selectedRecipientId.value) {
      const { data } = await axios.post('/api/recipients', recipient.value)
      recipients.value.push(data)
      selectedRecipientId.value = data.id
      saveRecipient.value = false
    }

    const response = await axios.post('/api/invoice/generate', {
      recipient:  recipient.value,
      line_items: lineItems.value,
      notes:      notes.value,
    }, { responseType: 'blob' })

    // Trigger browser download
    const url  = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    const link = document.createElement('a')
    link.href  = url
    link.download = response.headers['content-disposition']?.match(/filename="(.+)"/)?.[1] || 'invoice.pdf'
    link.click()
    URL.revokeObjectURL(url)

    // Reset line items and notes
    lineItems.value = [{ description:'', amount:'' }]
    notes.value     = ''
  } catch (e) {
    error.value = 'Failed to generate PDF. Check your sender profile is filled in.'
  } finally {
    generating.value = false
  }
}

const sectionHeading = { fontSize:'12px', fontWeight:'700', textTransform:'uppercase', letterSpacing:'1px', color:'#888', marginBottom:'14px' }
const labelStyle     = { display:'block', fontSize:'11px', fontWeight:'600', textTransform:'uppercase', letterSpacing:'0.5px', color:'#666', marginBottom:'5px' }
const inputStyle     = { display:'block', width:'100%', padding:'8px 10px', border:'1.5px solid #ddd', borderRadius:'4px', fontSize:'14px', outline:'none' }
const thStyle        = { textAlign:'left', fontSize:'10px', textTransform:'uppercase', letterSpacing:'0.5px', color:'#888', paddingBottom:'8px', borderBottom:'1.5px solid #222' }
const btnStyle       = { padding:'10px 26px', background:'#222', color:'#fff', border:'none', borderRadius:'4px', cursor:'pointer', fontWeight:'600', fontSize:'14px' }
const ghostBtnStyle  = { padding:'7px 14px', background:'#fff', color:'#444', border:'1.5px solid #ccc', borderRadius:'4px', cursor:'pointer', fontSize:'13px' }
const removeBtnStyle = { background:'none', border:'none', color:'#bbb', cursor:'pointer', fontSize:'14px', padding:'4px' }
</script>
