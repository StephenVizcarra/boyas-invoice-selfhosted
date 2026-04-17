<template>
  <div class="tile" @mouseenter="hovering = true" @mouseleave="hovering = false">
    <div class="tile-thumb" ref="thumbContainer">
      <div v-if="thumbState === 'loading'" class="tile-thumb-loader">
        <svg class="spin" width="20" height="20" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" stroke="#d97706" stroke-width="2.5" fill="none"
            stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12"
              dur="0.75s" repeatCount="indefinite"/>
          </circle>
        </svg>
      </div>

      <div v-else-if="thumbState === 'error'" class="tile-thumb-error">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <span>Preview unavailable</span>
      </div>

      <canvas ref="canvasEl" class="tile-canvas" :class="{ 'tile-canvas--hidden': thumbState !== 'ready' }"></canvas>

      <Transition name="tile-overlay">
        <div v-if="hovering" class="tile-overlay">
          <button class="tile-action" @click.stop="$emit('download')" title="Download PDF">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </button>
          <button class="tile-action" @click.stop="$emit('duplicate')" title="Duplicate into new invoice">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
          </button>
          <button class="tile-action tile-action--danger" @click.stop="onDelete" title="Delete">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
              <path d="M10 11v6"/><path d="M14 11v6"/>
              <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
          </button>
        </div>
      </Transition>
    </div>

    <div class="tile-meta">
      <div class="tile-number">{{ invoice.number }}</div>
      <div class="tile-recipient">
        {{ invoice.recipient.name }}{{ invoice.recipient.company ? ' · ' + invoice.recipient.company : '' }}
      </div>
      <div class="tile-footer">
        <span class="tile-total">{{ formatCurrency(invoice.total) }}</span>
        <span class="tile-date">{{ formatDate(invoice.generated_at) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePdfThumbnail } from '../composables/usePdfThumbnail'

const props = defineProps({
  invoice: { type: Object, required: true },
})

const emit = defineEmits(['download', 'delete', 'duplicate'])

const hovering     = ref(false)
const thumbState   = ref('loading') // loading | ready | error
const thumbContainer = ref(null)
const canvasEl     = ref(null)

const { renderThumbnail, loadCached } = usePdfThumbnail()

let observer = null

onMounted(() => {
  // Load from cache immediately if available; otherwise wait for intersection
  if (canvasEl.value && loadCached(canvasEl.value, props.invoice.number)) {
    thumbState.value = 'ready'
    return
  }

  observer = new IntersectionObserver(async ([entry]) => {
    if (!entry.isIntersecting) return
    observer.disconnect()
    observer = null

    try {
      await renderThumbnail(canvasEl.value, `/api/invoices/${props.invoice.number}`)
      thumbState.value = 'ready'
    } catch {
      thumbState.value = 'error'
    }
  }, { threshold: 0.1 })

  observer.observe(thumbContainer.value)
})

onUnmounted(() => {
  observer?.disconnect()
})

function onDelete() {
  if (confirm(`Delete ${props.invoice.number}? This cannot be undone.`)) {
    emit('delete')
  }
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount)
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<style scoped>
.tile {
  background: #ffffff;
  border: 1px solid #e7e5e4;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  cursor: default;
  transition: box-shadow 0.15s, border-color 0.15s;
}

.tile:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  border-color: #d6d3d1;
}

/* Thumbnail */
.tile-thumb {
  position: relative;
  width: 100%;
  aspect-ratio: 210 / 297;
  background: #f9f8f6;
  border-bottom: 1px solid #e7e5e4;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tile-thumb-loader,
.tile-thumb-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #a8a29e;
  font-size: 11px;
  font-weight: 500;
}

.tile-canvas {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.tile-canvas--hidden {
  display: none;
}

/* Hover overlay */
.tile-overlay {
  position: absolute;
  inset: 0;
  background: rgba(28, 25, 23, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.tile-overlay-enter-active,
.tile-overlay-leave-active {
  transition: opacity 0.15s ease;
}

.tile-overlay-enter-from,
.tile-overlay-leave-to {
  opacity: 0;
}

.tile-action {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  color: #1c1917;
  transition: background 0.12s, color 0.12s, transform 0.1s;
}

.tile-action:hover {
  background: #f5f4f0;
  transform: scale(1.05);
}

.tile-action--danger:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Metadata */
.tile-meta {
  padding: 12px 14px;
}

.tile-number {
  font-size: 12px;
  font-weight: 700;
  color: #d97706;
  letter-spacing: 0.02em;
  margin-bottom: 3px;
}

.tile-recipient {
  font-size: 13px;
  font-weight: 600;
  color: #1c1917;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 8px;
}

.tile-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.tile-total {
  font-size: 13px;
  font-weight: 700;
  color: #1c1917;
}

.tile-date {
  font-size: 11.5px;
  color: #a8a29e;
}
</style>
