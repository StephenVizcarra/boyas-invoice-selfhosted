<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">My Profile</h1>
      <p class="page-sub">"I just need your ID, I just need your data."</p>
    </div>

    <form @submit.prevent="save" class="card">
      <div class="card-body">
        <h2 class="section-label">Personal Information</h2>
        <div class="field-grid">
          <div
            v-for="field in fields"
            :key="field.key"
            class="field"
            :class="{ 'field--wide': field.wide }"
          >
            <label class="field-label">
              {{ field.label }}
              <span v-if="field.required" class="required">*</span>
            </label>
            <input
              v-model="form[field.key]"
              :type="field.type || 'text'"
              :required="field.required"
              :placeholder="field.placeholder || ''"
              class="field-input"
            >
          </div>
        </div>
      </div>

      <div class="card-divider"></div>

      <div class="card-body">
        <h2 class="section-label">
          Logo
          <span class="section-label-opt">(optional)</span>
        </h2>
        <div
          class="logo-dropzone"
          :class="{ 'logo-dropzone--filled': logoPreview }"
          @click="fileInput.click()"
          @dragover.prevent
          @drop.prevent="onDrop"
        >
          <template v-if="!logoPreview">
            <div class="logo-drop-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="M21 15l-5-5L5 21"/>
              </svg>
            </div>
            <p class="logo-drop-text">Click or drag to upload a logo</p>
            <p class="logo-drop-hint">PNG, JPG, SVG</p>
          </template>
          <img v-else :src="logoPreview" class="logo-preview">
          <input ref="fileInput" type="file" accept="image/*" @change="onLogoChange" style="display:none">
        </div>
        <button v-if="logoPreview" type="button" class="remove-logo" @click.stop="removeLogo">
          Remove logo
        </button>
      </div>

      <div class="card-footer">
        <button type="submit" class="btn-primary" :disabled="saving">
          <svg v-if="saving" class="spin" width="14" height="14" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
              <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.75s" repeatCount="indefinite"/>
            </circle>
          </svg>
          {{ saving ? 'Saving…' : 'Save Profile' }}
        </button>
        <transition name="fade">
          <span v-if="saved" class="saved-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Saved
          </span>
        </transition>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const fields = [
  { key: 'name',           label: 'Full Name',       required: true, placeholder: 'Jane Smith' },
  { key: 'company',        label: 'Company',                         placeholder: 'Acme LLC' },
  { key: 'email',          label: 'Email',           type: 'email',  placeholder: 'jane@example.com' },
  { key: 'phone',          label: 'Phone',                           placeholder: '+1 (555) 000-0000' },
  { key: 'address',        label: 'Street Address',  wide: true,     placeholder: '123 Main St' },
  { key: 'city_state_zip', label: 'City, State ZIP', wide: true,     placeholder: 'New York, NY 10001' },
]

const form        = ref({ name:'', company:'', address:'', city_state_zip:'', email:'', phone:'' })
const logoFile    = ref(null)
const logoPreview = ref(null)
const logoRemoved = ref(false)
const saving      = ref(false)
const saved       = ref(false)
const fileInput   = ref(null)

onMounted(async () => {
  const { data } = await axios.get('/api/sender')
  Object.assign(form.value, data)
  if (data.logo_path) {
    logoPreview.value = `/api/sender/logo?t=${Date.now()}`
  }
})

function onLogoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

function onDrop(e) {
  const file = e.dataTransfer.files[0]
  if (!file || !file.type.startsWith('image/')) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

function removeLogo() {
  logoFile.value   = null
  logoPreview.value = null
  logoRemoved.value = true
  if (fileInput.value) fileInput.value.value = ''
}

async function save() {
  saving.value = true
  saved.value  = false
  try {
    await axios.post('/api/sender', form.value)
    if (logoRemoved.value) {
      await axios.delete('/api/sender/logo')
      logoRemoved.value = false
    } else if (logoFile.value) {
      const fd = new FormData()
      fd.append('logo', logoFile.value)
      await axios.post('/api/sender/logo', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      logoFile.value = null
    }
    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.page { max-width: 620px; }

.page-header { margin-bottom: 22px; }
.page-title { font-size: 22px; font-weight: 700; color: #1c1917; margin-bottom: 4px; letter-spacing: -0.02em; }
.page-sub { font-size: 13.5px; color: #78716c; }

.card {
  background: #ffffff;
  border: 1px solid #e7e5e4;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.card-body { padding: 22px 24px; }
.card-divider { height: 1px; background: #f5f4f0; }

.section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #a8a29e;
  margin-bottom: 16px;
}

.section-label-opt {
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
  color: #c4bfbb;
  font-size: 11px;
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.field { display: flex; flex-direction: column; gap: 5px; }
.field--wide { grid-column: span 2; }

.field-label {
  font-size: 12px;
  font-weight: 600;
  color: #44403c;
  letter-spacing: 0.01em;
}

.required { color: #dc2626; margin-left: 2px; }

.field-input {
  padding: 8px 11px;
  border: 1.5px solid #e7e5e4;
  border-radius: 6px;
  font-size: 14px;
  font-family: 'Figtree', sans-serif;
  color: #1c1917;
  background: #fafaf9;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.field-input:focus {
  border-color: #d97706;
  box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
  background: #fff;
}

.field-input::placeholder { color: #c4bfbb; }

/* Logo */
.logo-dropzone {
  border: 1.5px dashed #d6d3d1;
  border-radius: 8px;
  padding: 30px 20px;
  text-align: center;
  cursor: pointer;
  background: #fafaf9;
  min-height: 110px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: border-color 0.15s, background 0.15s;
}

.logo-dropzone:hover {
  border-color: #d97706;
  background: #fffbeb;
}

.logo-dropzone--filled {
  border-style: solid;
  border-color: #e7e5e4;
  background: #fff;
  padding: 16px;
}

.logo-drop-icon { color: #c4bfbb; margin-bottom: 2px; }
.logo-drop-text { font-size: 13px; font-weight: 500; color: #78716c; }
.logo-drop-hint { font-size: 11.5px; color: #c4bfbb; }

.logo-preview { max-height: 72px; max-width: 220px; object-fit: contain; }

.remove-logo {
  margin-top: 8px;
  background: none;
  border: none;
  font-size: 12px;
  color: #dc2626;
  cursor: pointer;
  font-family: 'Figtree', sans-serif;
  padding: 2px 0;
}
.remove-logo:hover { text-decoration: underline; }

/* Footer */
.card-footer {
  padding: 16px 24px;
  background: #fafaf9;
  border-top: 1px solid #f5f4f0;
  display: flex;
  align-items: center;
  gap: 14px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 20px;
  background: #1c1917;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  font-family: 'Figtree', sans-serif;
  cursor: pointer;
  transition: background 0.15s;
}

.btn-primary:hover:not(:disabled) { background: #292524; }
.btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }

.spin { animation: spin 0.75s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.saved-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  font-weight: 600;
  color: #16a34a;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
