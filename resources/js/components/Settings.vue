<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Settings</h1>
      <p class="page-sub">Preferences for your Boyas Invoice workspace.</p>
    </div>

    <div class="card settings-card">
      <div class="card-body">
        <div class="setting-row">
          <div class="setting-info">
            <span class="setting-label">Theme</span>
            <span class="setting-sub">Switch between light and dark mode.</span>
          </div>
          <div class="theme-options">
            <label class="theme-option" :class="{ 'theme-option--active': theme === 'light' }">
              <input type="radio" v-model="theme" value="light" class="sr-only">
              <span>Light</span>
            </label>
            <label class="theme-option" :class="{ 'theme-option--active': theme === 'dark' }">
              <input type="radio" v-model="theme" value="dark" class="sr-only">
              <span>Dark</span>
            </label>
          </div>
        </div>

        <div class="setting-divider"></div>

        <div class="setting-row">
          <div class="setting-info">
            <span class="setting-label">Developer Mode</span>
            <span class="setting-sub">Shows the activity log panel and test data fill buttons.</span>
          </div>
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
    </div>
  </div>
</template>

<script setup>
import { useDevMode } from '../composables/useDevMode'
import { useTheme } from '../composables/useTheme'

const { devMode } = useDevMode()
const { theme } = useTheme()
</script>

<style scoped>
.page { max-width: 620px; }

.settings-card { margin-bottom: 12px; }

.setting-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 4px 0;
}

.setting-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.setting-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
}

.setting-sub {
  font-size: 13px;
  color: var(--text-muted);
}

.toggle-switch {
  width: 36px;
  height: 20px;
  background: var(--border-muted);
  border: none;
  border-radius: 999px;
  cursor: pointer;
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
}

.toggle-switch--on { background: var(--accent); }

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 14px;
  height: 14px;
  background: var(--bg-card);
  border-radius: 50%;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.toggle-switch--on .toggle-thumb { transform: translateX(16px); }

.setting-divider {
  height: 1px;
  background: var(--border-subtle);
  margin: 16px 0;
}

.theme-options {
  display: flex;
  gap: 4px;
  background: var(--bg-input);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 3px;
}

.theme-option {
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.theme-option:hover {
  color: var(--text-primary);
}

.theme-option--active {
  background: var(--bg-card);
  color: var(--text-primary);
  box-shadow: 0 1px 2px rgba(0,0,0,0.06);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0,0,0,0);
  border: 0;
}
</style>
