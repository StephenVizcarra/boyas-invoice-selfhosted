<template>
  <div style="max-width:860px; margin:0 auto; padding:24px; font-family:system-ui,sans-serif; color:#222;">
    <header style="border-bottom:2px solid #222; padding-bottom:16px; margin-bottom:28px; display:flex; align-items:baseline; gap:16px;">
      <h1 style="font-size:20px; font-weight:700; letter-spacing:0.5px;">Boyas Invoice</h1>
      <nav style="display:flex; gap:4px;">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="active = tab.id"
          :style="tabStyle(tab.id)"
        >{{ tab.label }}</button>
      </nav>
    </header>

    <SenderProfile v-if="active === 'profile'" />
    <NewInvoice    v-if="active === 'invoice'" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SenderProfile from './components/SenderProfile.vue'
import NewInvoice    from './components/NewInvoice.vue'

const active = ref('profile')
const tabs = [
  { id: 'profile', label: 'My Profile' },
  { id: 'invoice', label: 'New Invoice' },
]

function tabStyle(id) {
  const on = active.value === id
  return {
    padding: '6px 16px',
    border: '1.5px solid ' + (on ? '#222' : '#ccc'),
    borderRadius: '4px',
    background: on ? '#222' : '#fff',
    color: on ? '#fff' : '#555',
    cursor: 'pointer',
    fontWeight: on ? '600' : '400',
    fontSize: '13px',
  }
}
</script>
