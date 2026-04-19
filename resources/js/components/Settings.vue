<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Settings</h1>
      <p class="page-sub">Preferences for your Boyas Invoice workspace.</p>
    </div>

    <div class="card settings-card">
      <div class="card-body">
        <h2 class="section-label">Developer</h2>
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

const { devMode } = useDevMode()
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
  color: #1c1917;
}

.setting-sub {
  font-size: 13px;
  color: #78716c;
}

.toggle-switch {
  width: 36px;
  height: 20px;
  background: #d6d3d1;
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
  top: 3px;
  left: 3px;
  width: 14px;
  height: 14px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.toggle-switch--on .toggle-thumb { transform: translateX(16px); }
</style>
