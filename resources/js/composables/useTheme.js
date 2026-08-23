import { ref, watch } from 'vue'

const STORAGE_KEY = 'boyas-theme'
const stored = localStorage.getItem(STORAGE_KEY)
const theme = ref(stored === 'dark' ? 'dark' : 'light')

function applyTheme() {
  document.documentElement.setAttribute('data-theme', theme.value)
}

watch(theme, (val) => {
  localStorage.setItem(STORAGE_KEY, val)
  applyTheme()
})

applyTheme()

export function useTheme() {
  return { theme }
}
