import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist'

let workerConfigured = false

const CACHE_PREFIX = 'invoice_thumb_'
const CACHE_INDEX_KEY = 'invoice_thumb_index'
const MAX_CACHED_THUMBNAILS = 50

function ensureWorker() {
  if (workerConfigured) return
  GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.mjs',
    import.meta.url
  ).href
  workerConfigured = true
}

function getCacheIndex() {
  try {
    const index = localStorage.getItem(CACHE_INDEX_KEY)
    return index ? JSON.parse(index) : []
  } catch {
    return []
  }
}

function saveCacheIndex(index) {
  try {
    localStorage.setItem(CACHE_INDEX_KEY, JSON.stringify(index))
  } catch {
    // Ignore quota errors
  }
}

function evictOldest(index) {
  while (index.length > MAX_CACHED_THUMBNAILS) {
    const oldest = index.pop()
    if (oldest) {
      localStorage.removeItem(CACHE_PREFIX + oldest)
    }
  }
}

function addToCache(invoiceNumber, dataUrl) {
  const index = getCacheIndex()

  // Remove if already exists (will re-add at front)
  const existingIdx = index.indexOf(invoiceNumber)
  if (existingIdx !== -1) {
    index.splice(existingIdx, 1)
  }

  // Add to front (most recently used)
  index.unshift(invoiceNumber)

  // Evict oldest if over limit
  evictOldest(index)

  // Save thumbnail and index
  try {
    localStorage.setItem(CACHE_PREFIX + invoiceNumber, dataUrl)
    saveCacheIndex(index)
  } catch {
    // localStorage quota exceeded
  }
}

function removeFromCache(invoiceNumber) {
  localStorage.removeItem(CACHE_PREFIX + invoiceNumber)
  const index = getCacheIndex()
  const idx = index.indexOf(invoiceNumber)
  if (idx !== -1) {
    index.splice(idx, 1)
    saveCacheIndex(index)
  }
}

export function usePdfThumbnail() {
  async function renderThumbnail(canvas, pdfUrl) {
    ensureWorker()

    const response = await fetch(pdfUrl)
    const arrayBuffer = await response.arrayBuffer()
    const pdf = await getDocument({ data: arrayBuffer }).promise
    const page = await pdf.getPage(1)

    const containerWidth = canvas.parentElement?.clientWidth || 220
    const viewport = page.getViewport({ scale: 1 })
    const scale = containerWidth / viewport.width
    const scaledViewport = page.getViewport({ scale })

    canvas.width = scaledViewport.width
    canvas.height = scaledViewport.height

    await page.render({
      canvasContext: canvas.getContext('2d'),
      viewport: scaledViewport,
    }).promise

    const invoiceNumber = pdfUrl.split('/').pop()
    addToCache(invoiceNumber, canvas.toDataURL('image/jpeg', 0.85))
  }

  function loadCached(canvas, invoiceNumber) {
    const cached = localStorage.getItem(CACHE_PREFIX + invoiceNumber)
    if (!cached) return false

    const img = new Image()
    img.onload = () => {
      canvas.width = img.naturalWidth
      canvas.height = img.naturalHeight
      canvas.getContext('2d').drawImage(img, 0, 0)
    }
    img.src = cached
    return true
  }

  function clearThumbnailCache(invoiceNumber) {
    removeFromCache(invoiceNumber)
  }

  return { renderThumbnail, loadCached, clearThumbnailCache }
}
