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
          <component :is="activeTab === 'profile' ? SenderProfile : NewInvoice" />
        </KeepAlive>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SenderProfile from './components/SenderProfile.vue'
import NewInvoice from './components/NewInvoice.vue'

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
]

const activeTab = ref('profile')
const activeTabLabel = computed(() => tabs.find(t => t.key === activeTab.value)?.label)
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
</style>
