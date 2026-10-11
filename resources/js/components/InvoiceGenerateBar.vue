<template>
  <div class="actions">
    <button class="btn-primary" :disabled="generating" @click="$emit('generate')">
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
    <button v-if="devMode" type="button" class="btn-dev-fill" @click="$emit('fill-test')">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/>
      </svg>
      Fill test data
    </button>
    <p v-if="error" class="error-msg">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  generating: { type: Boolean, default: false },
  error: { type: String, default: '' },
  devMode: { type: Boolean, default: false },
})

defineEmits(['generate', 'fill-test'])
</script>

<style scoped>
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
