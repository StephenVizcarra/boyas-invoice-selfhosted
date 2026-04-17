import { ref, watch } from 'vue'

const STORAGE_KEY = 'boyas_dev_mode'

const devMode = ref(localStorage.getItem(STORAGE_KEY) === 'true')

watch(devMode, (val) => {
  localStorage.setItem(STORAGE_KEY, String(val))
})

export function useDevMode() {
  return { devMode }
}
