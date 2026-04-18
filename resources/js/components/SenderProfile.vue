<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">My Profile</h1>
      <p class="page-sub"><em>"I just need your ID, I just need your data."</em></p>
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
          :class="{
            'logo-dropzone--filled':    logoPreview && !logoUploading,
            'logo-dropzone--uploading': logoUploading,
            'logo-dropzone--error':     logoError && !logoUploading,
          }"
          @click="!logoUploading && fileInput.click()"
          @dragover.prevent
          @drop.prevent="!logoUploading && onDrop($event)"
        >
          <template v-if="logoUploading">
            <svg class="spin" width="22" height="22" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round">
                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.75s" repeatCount="indefinite"/>
              </circle>
            </svg>
            <p class="logo-drop-text">{{ logoPreview ? 'Uploading…' : 'Removing…' }}</p>
          </template>
          <template v-else-if="logoError">
            <div class="logo-drop-icon logo-drop-icon--error">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
            </div>
            <p class="logo-drop-text logo-drop-text--error">{{ logoError }}</p>
            <p class="logo-drop-hint">Click to try again</p>
          </template>
          <template v-else-if="logoPreview">
            <img :src="logoPreview" class="logo-preview">
          </template>
          <template v-else>
            <div class="logo-drop-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <path d="M21 15l-5-5L5 21"/>
              </svg>
            </div>
            <p class="logo-drop-text">Click or drag to upload a logo</p>
            <p class="logo-drop-hint">PNG, JPG, SVG · max 2 MB</p>
          </template>
          <input ref="fileInput" type="file" accept="image/*" @change="onLogoChange" style="display:none">
        </div>
        <button v-if="logoPreview && !logoUploading" type="button" class="remove-logo" @click.stop="removeLogo">
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
        <button v-if="devMode" type="button" class="btn-dev-fill" @click="fillTestData">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/>
          </svg>
          Fill test data
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
import { useActivityLog } from '../composables/useActivityLog'
import { useDevMode } from '../composables/useDevMode'

const { addLog } = useActivityLog()
const { devMode } = useDevMode()

const fields = [
  { key: 'name',           label: 'Full Name',       required: true, placeholder: 'Jane Smith' },
  { key: 'company',        label: 'Company',                         placeholder: 'Acme LLC' },
  { key: 'email',          label: 'Email',           type: 'email',  placeholder: 'jane@example.com' },
  { key: 'phone',          label: 'Phone',                           placeholder: '+1 (555) 000-0000' },
  { key: 'address',        label: 'Street Address',  wide: true,     placeholder: '123 Main St' },
  { key: 'city_state_zip', label: 'City, State ZIP', wide: true,     placeholder: 'New York, NY 10001' },
]

const form          = ref({ name:'', company:'', address:'', city_state_zip:'', email:'', phone:'' })
const logoPreview   = ref(null)
const logoUploading = ref(false)
const logoError     = ref('')
const saving        = ref(false)
const saved         = ref(false)
const fileInput     = ref(null)

onMounted(async () => {
  const { data } = await axios.get('/api/sender')
  Object.assign(form.value, data)
  if (data.logo_path) {
    logoPreview.value = `/api/sender/logo?t=${Date.now()}`
  }
})

async function onLogoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  if (fileInput.value) fileInput.value.value = ''
  logoPreview.value = URL.createObjectURL(file)
  logoError.value   = ''
  await uploadLogo(file)
}

async function onDrop(e) {
  const file = e.dataTransfer.files[0]
  if (!file || !file.type.startsWith('image/')) return
  logoPreview.value = URL.createObjectURL(file)
  logoError.value   = ''
  await uploadLogo(file)
}

async function uploadLogo(file) {
  if (file.size > 2 * 1024 * 1024) {
    logoError.value   = 'Image must be under 2 MB.'
    logoPreview.value = null
    return
  }
  logoUploading.value = true
  const entry = addLog('pending', 'Uploading logo…')
  try {
    const fd = new FormData()
    fd.append('logo', file)
    await axios.post('/api/sender/logo', fd)
    entry.type        = 'success'
    entry.message     = 'Logo uploaded'
    logoPreview.value = `/api/sender/logo?t=${Date.now()}`
  } catch {
    entry.type        = 'error'
    entry.message     = 'Failed to upload logo'
    logoError.value   = 'Upload failed — please try again.'
    logoPreview.value = null
  } finally {
    logoUploading.value = false
  }
}

async function removeLogo() {
  logoPreview.value   = null
  logoError.value     = ''
  logoUploading.value = true
  const entry = addLog('pending', 'Removing logo…')
  try {
    await axios.delete('/api/sender/logo')
    entry.type    = 'success'
    entry.message = 'Logo removed'
  } catch {
    entry.type    = 'error'
    entry.message = 'Failed to remove logo'
  } finally {
    logoUploading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

const TEST_PROFILES = [
  { name: 'Margaret Sullivan',  company: 'Sullivan Creative Co.',    email: 'margaret@sullivancreative.co',  phone: '+1 (415) 882-3047', address: '2847 Larkin Street, Suite 4',   city_state_zip: 'San Francisco, CA 94109' },
  { name: 'James Okonkwo',      company: 'Okonkwo Design Studio',    email: 'james@okonkwodesign.com',       phone: '+1 (312) 554-0192', address: '118 N. Wacker Drive, Floor 22', city_state_zip: 'Chicago, IL 60606'       },
  { name: 'Priya Mehta',        company: 'Mehta Consulting Group',   email: 'priya@mehtacg.io',              phone: '+1 (646) 771-3350', address: '350 Fifth Avenue, Suite 7800',  city_state_zip: 'New York, NY 10118'      },
  { name: 'Carlos Reyes',       company: 'Reyes & Associates LLC',   email: 'carlos@reyesassoc.com',         phone: '+1 (214) 903-6281', address: '1700 Pacific Avenue, Ste 2400', city_state_zip: 'Dallas, TX 75201'        },
  { name: 'Fiona Blackwell',    company: 'Blackwell Studio',         email: 'fiona@blackwellstudio.co',      phone: '+1 (503) 448-7723', address: '1020 SW Taylor Street',         city_state_zip: 'Portland, OR 97205'      },
]

let lastProfileIndex = -1

function fillTestData() {
  let idx
  do { idx = Math.floor(Math.random() * TEST_PROFILES.length) } while (idx === lastProfileIndex && TEST_PROFILES.length > 1)
  lastProfileIndex = idx
  form.value = { ...TEST_PROFILES[idx] }
  addLog('success', `Profile test data filled (${TEST_PROFILES[idx].name})`)
}

async function save() {
  saving.value = true
  saved.value  = false
  const entry = addLog('pending', 'Saving profile…')
  try {
    await axios.post('/api/sender', form.value)
    entry.type    = 'success'
    entry.message = 'Profile saved'
    saved.value   = true
    setTimeout(() => { saved.value = false }, 3000)
  } catch {
    entry.type    = 'error'
    entry.message = 'Failed to save profile'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.page { max-width: 620px; }

.card-divider { height: 1px; background: #f5f4f0; }

.section-label { margin-bottom: 16px; }

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
  transition: border-color 0.15s, background 0.15s, opacity 0.15s;
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

.logo-dropzone--uploading {
  cursor: default;
  opacity: 0.65;
  border-style: solid;
  border-color: #e7e5e4;
}

.logo-dropzone--uploading:hover {
  border-color: #e7e5e4;
  background: #fafaf9;
}

.logo-dropzone--error {
  border-style: solid;
  border-color: #fca5a5;
  background: #fef2f2;
}

.logo-dropzone--error:hover {
  border-color: #dc2626;
  background: #fef2f2;
}

.logo-drop-icon { color: #c4bfbb; margin-bottom: 2px; }
.logo-drop-icon--error { color: #dc2626; margin-bottom: 2px; }
.logo-drop-text { font-size: 13px; font-weight: 500; color: #78716c; }
.logo-drop-text--error { color: #dc2626; }
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
