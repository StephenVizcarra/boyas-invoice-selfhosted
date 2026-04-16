import { reactive } from 'vue'

const logs = reactive([])

function addLog(type, message) {
  const entry = {
    id:        crypto.randomUUID(),
    type,
    message,
    timestamp: new Date(),
  }
  logs.unshift(entry)
  return entry
}

function clearLog() {
  logs.splice(0)
}

export function useActivityLog() {
  return { logs, addLog, clearLog }
}
