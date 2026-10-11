<template>
  <div class="card" style="margin-bottom: 12px;">
    <div class="card-body">
      <h2 class="section-label">Bill To</h2>

      <div class="recipient-select-wrap">
        <select v-model="selectedRecipientId" class="select-input select-input--recipient" @change="onRecipientSelect">
          <option value="">   New recipient</option>
          <optgroup v-if="recipients.length > 0" label="──────────────────">
            <option v-for="r in recipients" :key="r.id" :value="r.id">
              {{ r.name }}{{ r.company ? ' · ' + r.company : '' }}
            </option>
          </optgroup>
        </select>
      </div>

      <div class="field-grid">
        <div class="field">
          <label class="field-label">Name <span class="required">*</span></label>
          <input
            v-model="recipient.name"
            type="text"
            required
            placeholder="Recipient name"
            class="field-input"
            :class="{ 'field-error': errorFields.recipientName }"
            @input="$emit('clear-field-error', 'recipientName')"
          >
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

      <div class="recipient-footer">
        <span v-if="selectedRecipientId" class="saved-contact-pill">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Saved contact
          <button type="button" class="delete-contact-btn" :disabled="deletingContact" @click="deleteContact" title="Delete this contact">
            <svg v-if="deletingContact" class="spin" width="10" height="10" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.75s" repeatCount="indefinite"/>
              </circle>
            </svg>
            <svg v-else width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </span>
        <button
          v-else-if="recipient.name"
          type="button"
          class="save-contact-btn"
          :disabled="savingContact"
          @click="saveToContacts"
        >
          <svg v-if="savingContact" class="spin" width="12" height="12" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
              <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.75s" repeatCount="indefinite"/>
            </circle>
          </svg>
          <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
          {{ savingContact ? 'Saving…' : 'Save to contacts' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useActivityLog } from '../composables/useActivityLog'

const recipient = defineModel('recipient', { type: Object, required: true })
const selectedRecipientId = defineModel('selectedRecipientId', { type: String, default: '' })

defineProps({
  errorFields: { type: Object, default: () => ({}) },
})

defineEmits(['clear-field-error'])

const { addLog } = useActivityLog()

const recipients      = ref([])
const savingContact   = ref(false)
const deletingContact = ref(false)

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/recipients')
    recipients.value = data
  } catch {
    // Saved recipients unavailable - user can still enter manually
  }
})

function onRecipientSelect() {
  if (!selectedRecipientId.value) {
    recipient.value = { name: '', company: '', address: '', city_state_zip: '', email: '' }
    return
  }
  const r = recipients.value.find(r => r.id === selectedRecipientId.value)
  if (r) Object.assign(recipient.value, r)
}

async function deleteContact() {
  const r = recipients.value.find(r => r.id === selectedRecipientId.value)
  if (!r || !confirm(`Delete ${r.name} from your contacts? This can't be undone.`)) return

  deletingContact.value = true
  const entry = addLog('pending', `Deleting ${r.name}…`)
  try {
    await axios.delete(`/api/recipients/${r.id}`)
    recipients.value = recipients.value.filter(x => x.id !== r.id)
    selectedRecipientId.value = ''
    recipient.value = { name: '', company: '', address: '', city_state_zip: '', email: '' }
    entry.type    = 'success'
    entry.message = `${r.name} deleted from contacts`
  } catch {
    entry.type    = 'error'
    entry.message = `Failed to delete ${r.name}`
  } finally {
    deletingContact.value = false
  }
}

async function saveToContacts() {
  savingContact.value = true
  const entry = addLog('pending', 'Saving contact…')
  try {
    const { data } = await axios.post('/api/recipients', recipient.value)
    recipients.value.push(data)
    selectedRecipientId.value = data.id
    entry.type    = 'success'
    entry.message = `${data.name} saved to contacts`
  } catch {
    entry.type    = 'error'
    entry.message = 'Failed to save contact'
  } finally {
    savingContact.value = false
  }
}
</script>

<style scoped>
.recipient-select-wrap {
  margin-bottom: 16px;
}

.select-input {
  width: 100%;
  padding: 8px 11px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: var(--text-primary);
  background: var(--bg-input);
  cursor: pointer;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.select-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
}

.select-input--recipient {
  border-left: 3px solid var(--accent);
  padding-left: 10px;
  background: var(--accent-bg-soft);
}

.recipient-footer {
  margin-top: 16px;
  min-height: 28px;
}

.saved-contact-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 6px 4px 10px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: #16a34a;
}

.delete-contact-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  margin-left: 2px;
  background: none;
  border: none;
  border-radius: 50%;
  color: #16a34a;
  cursor: pointer;
  padding: 0;
  opacity: 0.6;
  transition: opacity 0.12s, background 0.12s, color 0.12s;
}

.delete-contact-btn:hover:not(:disabled) {
  opacity: 1;
  background: #dcfce7;
  color: #dc2626;
}

.delete-contact-btn:disabled { cursor: not-allowed; }

.save-contact-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  background: none;
  border: 1.5px solid #e7e5e4;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  color: #78716c;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.save-contact-btn:hover:not(:disabled) {
  border-color: #d97706;
  color: #d97706;
  background: #fffbeb;
}

.save-contact-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
