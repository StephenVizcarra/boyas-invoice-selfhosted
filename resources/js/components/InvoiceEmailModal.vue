<template>
  <Teleport to="body">
    <div v-if="open" class="email-modal-overlay" @click.self="close">
      <div class="email-modal">
        <div class="email-modal-header">
          <h2 class="email-modal-title">Send Invoice</h2>
          <button type="button" class="email-modal-close" @click="close" title="Close">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="email-modal-body">
          <div class="email-field">
            <label class="email-field-label">To</label>
            <input v-model="emailTo" type="email" class="email-field-input" placeholder="recipient@example.com">
          </div>

          <div class="email-field">
            <label class="email-field-label">Subject</label>
            <input v-model="emailSubject" type="text" class="email-field-input">
          </div>

          <div class="email-field">
            <label class="email-field-label">Message</label>
            <textarea v-model="emailBody" class="email-field-textarea" rows="6"></textarea>
          </div>
        </div>

        <div class="email-modal-footer">
          <button type="button" class="btn-secondary" @click="close">Close</button>
          <button type="button" class="btn-primary" @click="sendEmail">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            Send via Email
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const open = defineModel('open', { type: Boolean, default: false })
const emailTo = defineModel('emailTo', { type: String, default: '' })
const emailSubject = defineModel('emailSubject', { type: String, default: '' })
const emailBody = defineModel('emailBody', { type: String, default: '' })

function close() {
  open.value = false
}

function sendEmail() {
  const mailto = `mailto:${encodeURIComponent(emailTo.value)}?subject=${encodeURIComponent(emailSubject.value)}&body=${encodeURIComponent(emailBody.value)}`
  window.open(mailto, '_blank')
  close()
}
</script>

<style scoped>
.email-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.email-modal {
  background: var(--bg-card);
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.email-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-subtle);
}

.email-modal-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.email-modal-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  background: none;
  border-radius: 6px;
  color: var(--text-subtle);
  cursor: pointer;
  transition: background 0.12s, color 0.12s;
}

.email-modal-close:hover {
  background: var(--bg-subtle);
  color: var(--text-primary);
}

.email-modal-body {
  padding: 20px;
  overflow-y: auto;
}

.email-field {
  margin-bottom: 16px;
}

.email-field:last-child {
  margin-bottom: 0;
}

.email-field-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 6px;
}

.email-field-input {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: var(--text-primary);
  background: var(--bg-input);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.email-field-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
  background: var(--bg-card);
}

.email-field-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: var(--text-primary);
  background: var(--bg-input);
  outline: none;
  resize: vertical;
  min-height: 120px;
  line-height: 1.5;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.email-field-textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
  background: var(--bg-card);
}

.email-modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 20px;
  border-top: 1px solid var(--border-subtle);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  background: var(--bg-card);
  font-size: 14px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  color: var(--text-secondary);
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
}

.btn-secondary:hover {
  border-color: var(--border-muted);
  background: var(--bg-input);
}
</style>
