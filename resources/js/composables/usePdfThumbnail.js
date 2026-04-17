import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist'

let workerConfigured = false

function ensureWorker() {
  if (workerConfigured) return
  GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.mjs',
    import.meta.url
  ).href
  workerConfigured = true
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

    try {
      localStorage.setItem(
        `invoice_thumb_${pdfUrl.split('/').pop()}`,
        canvas.toDataURL('image/jpeg', 0.85)
      )
    } catch {
      // localStorage quota exceeded — skip caching
    }
  }

  function loadCached(canvas, invoiceNumber) {
    const cached = localStorage.getItem(`invoice_thumb_${invoiceNumber}`)
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
    localStorage.removeItem(`invoice_thumb_${invoiceNumber}`)
  }

  return { renderThumbnail, loadCached, clearThumbnailCache }
}
