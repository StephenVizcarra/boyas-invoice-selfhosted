<template>
  <div class="shell">
    <aside class="sidebar">
      <div class="brand">
        <img :src="'/logo.png'" alt="Logo" class="brand-logo">
        <span class="brand-name">Boyas Invoice</span>
      </div>

      <nav class="nav">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="nav-card"
          :class="{ 'nav-card--active': activeTab === tab.key }"
          @click="activeTab = tab.key"
        >
          <span class="nav-card-icon" v-html="tab.icon"></span>
          <span class="nav-card-text">
            <span class="nav-card-label">{{ tab.label }}</span>
            <span class="nav-card-sub">{{ tab.sub }}</span>
          </span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="dev-toggle-row">
          <span class="dev-toggle-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
          </span>
          <span class="dev-toggle-label">Developer Mode</span>
          <button
            class="toggle-switch"
            :class="{ 'toggle-switch--on': devMode }"
            @click="devMode = !devMode"
            :aria-pressed="devMode"
            aria-label="Toggle developer mode"
          >
            <span class="toggle-thumb"></span>
          </button>
        </div>
      </div>

    </aside>

    <div class="main">
      <header class="topbar">
        <div class="topbar-breadcrumb">
          <span class="topbar-app">Boyas Invoice</span>
          <span class="topbar-sep">/</span>
          <span class="topbar-page">{{ activeTabLabel }}</span>
        </div>
      </header>
      <main class="content">
        <KeepAlive>
          <component
            :is="tabComponents[activeTab]"
            :prefill-seed="prefillSeed"
            @prefill-consumed="prefillSeed = null"
            @duplicate="handleDuplicate"
          />
        </KeepAlive>
      </main>
      <Transition name="log-panel">
        <DevLogPanel v-if="devMode" />
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SenderProfile from './components/SenderProfile.vue'
import NewInvoice from './components/NewInvoice.vue'
import InvoiceHistory from './components/InvoiceHistory.vue'
import DevLogPanel from './components/DevLogPanel.vue'
import { useDevMode } from './composables/useDevMode'

const { devMode } = useDevMode()

const tabs = [
  {
    key: 'profile',
    label: 'My Profile',
    sub: 'Your sender info',
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
  },
  {
    key: 'invoice',
    label: 'New Invoice',
    sub: 'Generate a PDF',
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>',
  },
  {
    key: 'history',
    label: 'History',
    sub: 'Previously generated',
    icon: '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 3h6l2 3h10a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>',
  },
]

const tabComponents = { profile: SenderProfile, invoice: NewInvoice, history: InvoiceHistory }

const activeTab      = ref('profile')
const prefillSeed    = ref(null)
const activeTabLabel = computed(() => tabs.find(t => t.key === activeTab.value)?.label)

function handleDuplicate(invoice) {
  prefillSeed.value = { ...invoice }
  activeTab.value   = 'invoice'
}
</script>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body, #app { height: 100%; }
body { font-family: 'Figtree', sans-serif; }
</style>

<style scoped>
.shell {
  display: flex;
  height: 100vh;
}

/* ── Sidebar ── */
.sidebar {
  width: 240px;
  min-width: 240px;
  background: #1c1917;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #292524;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 16px 16px;
  border-bottom: 1px solid #292524;
}

.brand-logo {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  object-fit: cover;
  flex-shrink: 0;
}

.brand-name {
  font-size: 14px;
  font-weight: 700;
  color: #fafaf9;
  letter-spacing: -0.01em;
}

.nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-card {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  border: 1px solid #292524;
  background: #231f1d;
  padding: 14px 16px;
  border-radius: 10px;
  cursor: pointer;
  text-align: left;
  font-family: 'Figtree', sans-serif;
  transition: background 0.15s, border-color 0.15s;
}

.nav-card:hover {
  background: #2c2826;
  border-color: #3d3734;
}

.nav-card--active {
  background: #292524;
  border-color: #d97706;
}

.nav-card-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  color: #57534e;
  transition: color 0.15s;
}

.nav-card:hover .nav-card-icon,
.nav-card--active .nav-card-icon {
  color: #d97706;
}

.nav-card-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-card-label {
  font-size: 14px;
  font-weight: 600;
  color: #a8a29e;
  transition: color 0.15s;
  line-height: 1;
}

.nav-card:hover .nav-card-label,
.nav-card--active .nav-card-label {
  color: #fafaf9;
}

.nav-card-sub {
  font-size: 11.5px;
  color: #57534e;
  font-weight: 400;
  transition: color 0.15s;
  line-height: 1;
}

.nav-card:hover .nav-card-sub,
.nav-card--active .nav-card-sub {
  color: #a8a29e;
}

/* ── Main area ── */
.main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: #f5f4f0;
}

.topbar {
  height: 48px;
  display: flex;
  align-items: center;
  padding: 0 28px;
  background: #ffffff;
  border-bottom: 1px solid #e7e5e4;
  flex-shrink: 0;
}

.topbar-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.topbar-app { color: #a8a29e; font-weight: 500; }
.topbar-sep { color: #d6d3d1; }
.topbar-page { color: #1c1917; font-weight: 700; }

.content {
  flex: 1;
  overflow-y: auto;
  padding: 32px 36px;
}

/* Sidebar footer / dev toggle */
.sidebar-footer {
  padding: 12px;
  border-top: 1px solid #292524;
  flex-shrink: 0;
}

.dev-toggle-row {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 8px;
}

.dev-toggle-icon {
  display: flex;
  color: #57534e;
}

.dev-toggle-label {
  flex: 1;
  font-size: 12px;
  font-weight: 600;
  color: #78716c;
}

.toggle-switch {
  width: 30px;
  height: 17px;
  background: #44403c;
  border: none;
  border-radius: 999px;
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
}

.toggle-switch--on { background: #d97706; }

.toggle-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 13px;
  height: 13px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
}

.toggle-switch--on .toggle-thumb { transform: translateX(13px); }

/* Log panel slide transition */
.log-panel-enter-active,
.log-panel-leave-active {
  transition: max-height 0.25s ease, opacity 0.2s ease;
  overflow: hidden;
}

.log-panel-enter-from,
.log-panel-leave-to {
  max-height: 0;
  opacity: 0;
}

.log-panel-enter-to,
.log-panel-leave-from {
  max-height: 200px;
  opacity: 1;
}
</style>
