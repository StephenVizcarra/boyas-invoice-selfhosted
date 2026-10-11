<template>
  <div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
      <div class="section-header-row">
        <h2 class="section-label" style="margin-bottom:0;">
          Notes <span class="section-label-opt">(optional)</span>
        </h2>
        <span v-if="defaultNotes" class="notes-default-indicator">
          Default note
          <button type="button" class="notes-default-clear" @click="defaultNotes = ''" title="Clear default">Clear</button>
        </span>
        <button v-else-if="notes" type="button" class="notes-set-default" @click="defaultNotes = notes">
          Set as default
        </button>
      </div>
      <textarea
        v-model="notes"
        class="notes-input"
        placeholder="Payment terms, thank you note, bank details…"
        rows="3"
        style="margin-top: 10px;"
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { useDefaultNotes } from '../composables/useDefaultNotes'

const notes = defineModel({ type: String, default: '' })
const { defaultNotes } = useDefaultNotes()
</script>

<style scoped>
.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0;
}

.notes-default-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #d97706;
}

.notes-default-clear {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  color: #a8a29e;
  cursor: pointer;
  padding: 0;
  transition: color 0.12s;
}

.notes-default-clear:hover { color: #dc2626; }

.notes-set-default {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  color: #a8a29e;
  cursor: pointer;
  padding: 0;
  transition: color 0.12s;
}

.notes-set-default:hover { color: #d97706; }

.notes-input {
  width: 100%;
  padding: 10px 12px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: var(--text-primary);
  background: var(--bg-input);
  resize: vertical;
  min-height: 80px;
  outline: none;
  line-height: 1.55;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.notes-input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-ring);
  background: var(--bg-card);
}

.notes-input::placeholder { color: var(--text-faint); }
</style>
