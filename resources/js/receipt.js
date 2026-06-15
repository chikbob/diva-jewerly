import { downloadElementPdf } from './composables/usePdf'

function readReceiptOrder() {
    const payload = document.getElementById('receipt-order-data')

    if (!payload) {
        return null
    }

    return JSON.parse(payload.textContent ?? 'null')
}

async function downloadReceiptPdf() {
    const receiptElement = document.querySelector('.receipt')
    const receiptOrder = readReceiptOrder()

    if (!receiptElement || !receiptOrder) {
        throw new Error('Receipt data is unavailable')
    }

    await downloadElementPdf(receiptElement, `receipt-order-${receiptOrder.id}.pdf`)
}

window.downloadReceiptPdf = downloadReceiptPdf

window.addEventListener('load', async () => {
    const autoDownloadPdf = document.body.dataset.autoDownloadPdf === '1'
    const autoPrint = document.body.dataset.autoPrint === '1'

    if (autoDownloadPdf) {
        await downloadReceiptPdf()
    }

    if (autoPrint) {
        window.print()
    }
})
