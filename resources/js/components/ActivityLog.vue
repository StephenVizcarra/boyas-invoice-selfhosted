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
