<template>
  <div class="shell">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
          </svg>
        </div>
        <span class="brand-name">Boyas Invoice</span>
      </div>

      <nav class="nav">
        <p class="nav-group-label">Menu</p>
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="nav-item"
          :class="{ 'nav-item--active': activeTab === tab.key }"
          @click="activeTab = tab.key"
        >
          <span class="nav-icon" v-html="tab.icon"></span>
          <span>{{ tab.label }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="user-chip">
          <div class="user-avatar">B</div>
          <div class="user-info">
            <div class="user-name">Owner</div>
            <div class="user-status">
              <span class="status-dot"></span>Active
            </div>
          </div>
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
        <SenderProfile v-if="activeTab === 'profile'" />
        <NewInvoice v-else />
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
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
  },
  {
    key: 'invoice',
    label: 'New Invoice',
    icon: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>',
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
  width: 220px;
  min-width: 220px;
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

.brand-icon {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  background: #d97706;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
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
  padding: 14px 8px;
}

.nav-group-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #57534e;
  padding: 0 8px 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  width: 100%;
  border: none;
  background: none;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 13.5px;
  font-weight: 500;
  color: #a8a29e;
  cursor: pointer;
  text-align: left;
  font-family: 'Figtree', sans-serif;
  transition: background 0.12s, color 0.12s;
  margin-bottom: 2px;
}

.nav-item:hover {
  background: #292524;
  color: #fafaf9;
}

.nav-item--active {
  background: #292524;
  color: #fafaf9;
  font-weight: 600;
}

.nav-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  color: #57534e;
  transition: color 0.12s;
}

.nav-item:hover .nav-icon,
.nav-item--active .nav-icon {
  color: #d97706;
}

/* Sidebar footer */
.sidebar-footer {
  padding: 12px 10px 14px;
  border-top: 1px solid #292524;
}

.user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 4px;
}

.user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  background: #44403c;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: #d4c5b0;
  flex-shrink: 0;
  border: 1px solid #57534e;
}

.user-info { line-height: 1; }

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: #e7e5e4;
  margin-bottom: 3px;
}

.user-status {
  font-size: 11px;
  color: #57534e;
  display: flex;
  align-items: center;
  gap: 4px;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #4ade80;
  display: inline-block;
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
