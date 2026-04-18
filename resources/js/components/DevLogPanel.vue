<template>
  <div class="log-panel" :class="{ 'log-panel--collapsed': collapsed }">
    <div class="log-header">
      <span class="log-title">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
        Activity Log
      </span>
      <span class="log-count">{{ logs.length }} {{ logs.length === 1 ? 'entry' : 'entries' }}</span>
      <button class="log-clear" @click="clearLog">Clear</button>
      <button class="log-collapse" @click="collapsed = !collapsed" :title="collapsed ? 'Expand log' : 'Collapse log'">
        <svg class="collapse-chevron" :class="{ 'collapse-chevron--up': !collapsed }" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
    </div>
    <div v-show="!collapsed" class="log-body">
      <div v-if="logs.length === 0" class="log-empty">No activity yet.</div>
      <div
        v-for="entry in logs"
        :key="entry.id"
        class="log-entry"
        :class="`log-entry--${entry.type}`"
      >
        <span class="log-ts">{{ formatTime(entry.timestamp) }}</span>
        <span class="log-dot"></span>
        <span class="log-msg">{{ entry.message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useActivityLog } from '../composables/useActivityLog'

const { logs, clearLog } = useActivityLog()

const collapsed = ref(false)

function formatTime(ts) {
  return ts.toLocaleTimeString('en-US', { hour12: false })
}
</script>

<style scoped>
.log-panel {
  background: #1c1917;
  border-top: 1px solid #292524;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.log-panel:not(.log-panel--collapsed) {
  height: 200px;
}

.log-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 20px;
  border-bottom: 1px solid #292524;
  flex-shrink: 0;
}

.log-panel--collapsed .log-header {
  border-bottom: none;
}

.log-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #57534e;
}

.log-count {
  flex: 1;
  font-size: 11px;
  color: #44403c;
}

.log-clear {
  background: none;
  border: 1px solid #44403c;
  border-radius: 4px;
  color: #78716c;
  font-size: 11px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  padding: 2px 8px;
  cursor: pointer;
  transition: border-color 0.12s, color 0.12s;
}

.log-clear:hover {
  border-color: #78716c;
  color: #a8a29e;
}

.log-collapse {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  background: none;
  border: 1px solid #44403c;
  border-radius: 4px;
  color: #78716c;
  cursor: pointer;
  flex-shrink: 0;
  transition: border-color 0.12s, color 0.12s;
}

.log-collapse:hover {
  border-color: #78716c;
  color: #a8a29e;
}

.collapse-chevron {
  transition: transform 0.2s ease;
}

.collapse-chevron--up {
  transform: rotate(180deg);
}

.log-body {
  flex: 1;
  overflow-y: auto;
  padding: 6px 20px;
}

.log-empty {
  font-size: 12px;
  color: #44403c;
  padding: 8px 0;
}

.log-entry {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
  font-size: 12px;
  font-family: 'Figtree', monospace;
  border-bottom: 1px solid #292524;
}

.log-entry:last-child { border-bottom: none; }

.log-ts {
  color: #57534e;
  font-variant-numeric: tabular-nums;
  flex-shrink: 0;
  font-size: 11px;
}

.log-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
  background: #57534e;
}

.log-entry--pending .log-dot { background: #d97706; }
.log-entry--success .log-dot { background: #16a34a; }
.log-entry--error   .log-dot { background: #dc2626; }

.log-msg {
  color: #a8a29e;
  font-size: 12px;
}

.log-entry--pending .log-msg { color: #d97706; }
.log-entry--success .log-msg { color: #a8a29e; }
.log-entry--error   .log-msg { color: #fca5a5; }
</style>
