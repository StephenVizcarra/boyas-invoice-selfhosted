<template>
  <div>
    <h2 style="font-size:16px; font-weight:600; margin-bottom:20px;">My Details</h2>

    <form @submit.prevent="save" style="max-width:480px;">
      <div v-for="field in fields" :key="field.key" style="margin-bottom:14px;">
        <label :style="labelStyle">{{ field.label }}</label>
        <input
          v-model="form[field.key]"
          :type="field.type || 'text'"
          :required="field.required"
          :style="inputStyle"
        >
      </div>

      <div style="margin-bottom:20px;">
        <label :style="labelStyle">Logo <span style="color:#999;font-weight:400;">(optional)</span></label>
        <input type="file" accept="image/*" @change="onLogoChange" :style="inputStyle">
        <div v-if="logoPreview" style="margin-top:10px;">
          <img :src="logoPreview" style="max-height:60px; max-width:200px; border:1px solid #eee; padding:4px;">
        </div>
      </div>

      <div style="display:flex; align-items:center; gap:12px;">
        <button type="submit" :style="btnStyle" :disabled="saving">
          {{ saving ? 'Saving…' : 'Save Details' }}
        </button>
        <span v-if="saved" style="color:#2a9d4e; font-size:13px;">Saved!</span>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const fields = [
  { key: 'name',          label: 'Name',              required: true },
  { key: 'company',       label: 'Company' },
  { key: 'address',       label: 'Address' },
  { key: 'city_state_zip',label: 'City, State ZIP' },
  { key: 'email',         label: 'Email', type: 'email' },
  { key: 'phone',         label: 'Phone' },
]

const form      = ref({ name:'', company:'', address:'', city_state_zip:'', email:'', phone:'' })
const logoFile  = ref(null)
const logoPreview = ref(null)
const saving    = ref(false)
const saved     = ref(false)

onMounted(async () => {
  const { data } = await axios.get('/api/sender')
  Object.assign(form.value, data)
})

function onLogoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

async function save() {
  saving.value = true
  saved.value  = false
  try {
    await axios.post('/api/sender', form.value)

    if (logoFile.value) {
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

const labelStyle = { display:'block', fontSize:'11px', fontWeight:'600', textTransform:'uppercase', letterSpacing:'0.5px', color:'#666', marginBottom:'5px' }
const inputStyle = { display:'block', width:'100%', padding:'8px 10px', border:'1.5px solid #ddd', borderRadius:'4px', fontSize:'14px', outline:'none' }
const btnStyle   = { padding:'9px 22px', background:'#222', color:'#fff', border:'none', borderRadius:'4px', cursor:'pointer', fontWeight:'600', fontSize:'14px' }
</script>
