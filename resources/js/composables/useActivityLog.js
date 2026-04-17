import { reactive } from 'vue'

const logs = reactive([])

function addLog(type, message) {
  // Must be reactive() so callers can mutate entry.type/message after the fact
  // and have Vue's proxy pick up the change. Plain object mutations bypass the proxy.
  const entry = reactive({
    id:        crypto.randomUUID(),
    type,
    message,
    timestamp: new Date(),
  })
  logs.unshift(entry)
  return entry
}

function clearLog() {
  logs.splice(0)
}

export function useActivityLog() {
  return { logs, addLog, clearLog }
}
