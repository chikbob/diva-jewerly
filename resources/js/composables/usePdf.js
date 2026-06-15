import html2canvas from 'html2canvas'
import { jsPDF } from 'jspdf'

async function renderCanvas(element) {
    if (!(element instanceof HTMLElement)) {
        throw new Error('PDF source element is missing')
    }

    return html2canvas(element, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        logging: false,
        windowWidth: Math.max(element.scrollWidth, element.clientWidth, window.innerWidth),
        windowHeight: Math.max(element.scrollHeight, element.clientHeight, window.innerHeight),
    })
}

function saveCanvasAsPdf(canvas, filename) {
    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
    })

    const pageWidth = pdf.internal.pageSize.getWidth()
    const pageHeight = pdf.internal.pageSize.getHeight()
    const margin = 10
    const printableWidth = pageWidth - margin * 2
    const printableHeight = pageHeight - margin * 2
    const imageWidth = printableWidth
    const imageHeight = (canvas.height * imageWidth) / canvas.width
    const imageData = canvas.toDataURL('image/png')

    let heightLeft = imageHeight
    let positionY = margin

    pdf.addImage(imageData, 'PNG', margin, positionY, imageWidth, imageHeight, undefined, 'FAST')
    heightLeft -= printableHeight

    while (heightLeft > 0) {
        positionY = margin - (imageHeight - heightLeft)
        pdf.addPage()
        pdf.addImage(imageData, 'PNG', margin, positionY, imageWidth, imageHeight, undefined, 'FAST')
        heightLeft -= printableHeight
    }

    pdf.save(filename)
}

export async function downloadElementPdf(element, filename) {
    const canvas = await renderCanvas(element)
    saveCanvasAsPdf(canvas, filename)
}

export async function downloadOrdersReportPdf(element) {
    const date = new Date().toISOString().slice(0, 10)

    await downloadElementPdf(element, `orders-report-${date}.pdf`)
}
