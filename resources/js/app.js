import { createApp } from 'vue'
import App from './App.vue'
import '../css/shared.css'
import { useTheme } from './composables/useTheme'

useTheme()

createApp(App).mount('#app')
