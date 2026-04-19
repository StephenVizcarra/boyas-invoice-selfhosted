import { ref, watch } from 'vue'

const STORAGE_KEY = 'boyas_default_notes'

const defaultNotes = ref(localStorage.getItem(STORAGE_KEY) ?? '')

watch(defaultNotes, (val) => {
  if (val) {
    localStorage.setItem(STORAGE_KEY, val)
  } else {
    localStorage.removeItem(STORAGE_KEY)
  }
})

export function useDefaultNotes() {
  return { defaultNotes }
}
