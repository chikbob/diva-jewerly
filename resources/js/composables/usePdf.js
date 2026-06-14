let jsPdfLoader = null
let fontBase64Loader = null

function formatPrice(value) {
    return Number(value ?? 0).toLocaleString('uk-UA', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    })
}

function formatDate(value) {
    if (!value) {
        return 'невідомо'
    }

    return new Date(value).toLocaleString('uk-UA')
}

function statusLabel(status) {
    return {
        pending: 'В очікуванні',
        paid: 'Сплачено',
        failed: 'Помилка',
        cancelled: 'Скасовано',
    }[status] ?? status
}

function paymentMethodLabel(paymentMethod) {
    return {
        demo_card: 'Демо-картка',
        cash_on_delivery: 'Післяплата',
    }[paymentMethod] ?? paymentMethod
}

function paymentStatusLabel(status) {
    return {
        paid: 'Оплачено',
        pending: 'Оплата очікується',
        failed: 'Оплата неуспішна',
        cancelled: 'Оплату скасовано',
    }[status] ?? status
}

async function loadJsPdf() {
    if (window.jspdf?.jsPDF) {
        return window.jspdf.jsPDF
    }

    if (jsPdfLoader) {
        return jsPdfLoader
    }

    jsPdfLoader = new Promise((resolve, reject) => {
        const existing = document.querySelector('script[data-jspdf-loader="true"]')

        if (existing) {
            existing.addEventListener('load', () => resolve(window.jspdf.jsPDF), { once: true })
            existing.addEventListener('error', reject, { once: true })
            return
        }

        const script = document.createElement('script')
        script.src = '/vendor/jspdf/jspdf.umd.min.js'
        script.async = true
        script.dataset.jspdfLoader = 'true'
        script.onload = () => resolve(window.jspdf.jsPDF)
        script.onerror = reject
        document.head.appendChild(script)
    })

    return jsPdfLoader
}

async function loadFontBase64() {
    if (fontBase64Loader) {
        return fontBase64Loader
    }

    fontBase64Loader = fetch('/fonts/Roboto-Regular.ttf')
        .then((response) => {
            if (!response.ok) {
                throw new Error('Failed to load PDF font')
            }

            return response.arrayBuffer()
        })
        .then((buffer) => arrayBufferToBase64(buffer))

    return fontBase64Loader
}

function arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer)
    const chunkSize = 0x8000
    let binary = ''

    for (let i = 0; i < bytes.length; i += chunkSize) {
        const chunk = bytes.subarray(i, i + chunkSize)
        binary += String.fromCharCode(...chunk)
    }

    return btoa(binary)
}

async function createPdfDocument() {
    const JsPdf = await loadJsPdf()
    const doc = new JsPdf({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
    })

    const fontBase64 = await loadFontBase64()

    if (!doc.getFontList().Roboto) {
        doc.addFileToVFS('Roboto-Regular.ttf', fontBase64)
        doc.addFont('Roboto-Regular.ttf', 'Roboto', 'normal')
    }

    doc.setFont('Roboto', 'normal')
    doc.setTextColor(95, 61, 72)

    return doc
}

function createCursor(doc) {
    return {
        margin: 16,
        pageWidth: doc.internal.pageSize.getWidth(),
        pageHeight: doc.internal.pageSize.getHeight(),
        y: 16,
    }
}

function ensureSpace(doc, cursor, heightNeeded) {
    if (cursor.y + heightNeeded <= cursor.pageHeight - cursor.margin) {
        return
    }

    doc.addPage()
    doc.setFont('Roboto', 'normal')
    doc.setTextColor(95, 61, 72)
    cursor.y = cursor.margin
}

function drawLine(doc, cursor, text, options = {}) {
    const {
        size = 11,
        x = cursor.margin,
        color = [95, 61, 72],
        maxWidth = cursor.pageWidth - cursor.margin * 2,
        lineHeight = size * 0.42 + 2.2,
    } = options

    doc.setFontSize(size)
    doc.setTextColor(...color)

    const lines = doc.splitTextToSize(String(text ?? ''), maxWidth)
    ensureSpace(doc, cursor, Math.max(lines.length, 1) * lineHeight)
    doc.text(lines, x, cursor.y)
    cursor.y += Math.max(lines.length, 1) * lineHeight
}

function drawGap(cursor, value = 4) {
    cursor.y += value
}

function receiptFilename(order) {
    return `receipt-order-${order.id}.pdf`
}

function reportFilename() {
    const date = new Date().toISOString().slice(0, 10)
    return `orders-report-${date}.pdf`
}

export async function downloadOrderReceiptPdf(order) {
    const doc = await createPdfDocument()
    const cursor = createCursor(doc)

    drawLine(doc, cursor, 'DIVA Jewelry', { size: 12, color: [178, 123, 143] })
    drawGap(cursor, 2)
    drawLine(doc, cursor, `Чек замовлення #${order.id}`, { size: 22 })
    drawLine(doc, cursor, `Створено: ${formatDate(order.created_at)}`, { size: 11, color: [140, 103, 116] })
    drawGap(cursor, 5)

    drawLine(doc, cursor, `Отримувач: ${order.full_name}`, { size: 12 })
    drawLine(doc, cursor, `Email: ${order.email}`, { size: 12 })
    drawLine(doc, cursor, `Статус замовлення: ${statusLabel(order.status)}`, { size: 12 })
    drawLine(doc, cursor, `Статус оплати: ${paymentStatusLabel(order.payment_status)}`, { size: 12 })
    drawLine(doc, cursor, `Спосіб оплати: ${paymentMethodLabel(order.payment_method)}`, { size: 12 })

    if (order.payment_reference) {
        drawLine(doc, cursor, `Платіжний референс: ${order.payment_reference}`, { size: 12 })
    }

    drawGap(cursor, 6)
    drawLine(doc, cursor, 'Склад замовлення', { size: 16 })
    drawGap(cursor, 2)

    order.items.forEach((item, index) => {
        const lineTotal = Number(item.line_total ?? (Number(item.price ?? 0) * Number(item.quantity ?? 0)))

        drawLine(doc, cursor, `${index + 1}. ${item.product_name}`, { size: 12 })

        if (item.product_category) {
            drawLine(doc, cursor, item.product_category, { size: 10, color: [178, 123, 143] })
        }

        drawLine(doc, cursor, `${item.quantity} x ${formatPrice(item.price)} грн = ${formatPrice(lineTotal)} грн`, {
            size: 11,
            color: [140, 103, 116],
        })

        if (item.product_description) {
            drawLine(doc, cursor, item.product_description, { size: 10, color: [140, 103, 116] })
        }

        drawGap(cursor, 3)
    })

    drawGap(cursor, 4)
    drawLine(doc, cursor, `Позицій: ${order.items.length}`, { size: 12 })
    drawLine(doc, cursor, `Товарів: ${order.items.reduce((sum, item) => sum + Number(item.quantity ?? 0), 0)}`, { size: 12 })
    drawLine(doc, cursor, `Разом: ${formatPrice(order.total)} грн`, { size: 18 })
    drawGap(cursor, 8)
    drawLine(doc, cursor, `Чек сформовано: ${formatDate(new Date().toISOString())}`, { size: 10, color: [140, 103, 116] })

    doc.save(receiptFilename(order))
}

export async function downloadOrdersReportPdf(payload) {
    const doc = await createPdfDocument()
    const cursor = createCursor(doc)

    drawLine(doc, cursor, 'DIVA Jewelry', { size: 12, color: [178, 123, 143] })
    drawGap(cursor, 2)
    drawLine(doc, cursor, 'Звіт по замовленнях', { size: 22 })
    drawLine(doc, cursor, `Сформовано: ${formatDate(new Date().toISOString())}`, { size: 11, color: [140, 103, 116] })
    drawGap(cursor, 4)

    const activeFilters = [
        payload.filters?.date_from ? `Дата від: ${payload.filters.date_from}` : null,
        payload.filters?.date_to ? `Дата до: ${payload.filters.date_to}` : null,
        payload.filters?.status ? `Статус замовлення: ${statusLabel(payload.filters.status)}` : null,
        payload.filters?.payment_status ? `Статус оплати: ${paymentStatusLabel(payload.filters.payment_status)}` : null,
    ].filter(Boolean)

    if (activeFilters.length > 0) {
        drawLine(doc, cursor, `Фільтри: ${activeFilters.join(' | ')}`, { size: 11 })
        drawGap(cursor, 4)
    }

    drawLine(doc, cursor, `Замовлень: ${payload.summary.orders_count}`, { size: 12 })
    drawLine(doc, cursor, `Виручка: ${formatPrice(payload.summary.revenue_total)} грн`, { size: 12 })
    drawLine(doc, cursor, `Оплачені: ${payload.summary.paid_count}`, { size: 12 })
    drawLine(doc, cursor, `Середній чек: ${formatPrice(payload.summary.average_total)} грн`, { size: 12 })
    drawGap(cursor, 6)

    drawLine(doc, cursor, 'Замовлення у звіті', { size: 16 })
    drawGap(cursor, 2)

    payload.orders.forEach((order) => {
        drawLine(doc, cursor, `#${order.id} | ${order.created_at} | ${order.full_name}`, { size: 11 })
        drawLine(doc, cursor, `Статус: ${statusLabel(order.status)} | Оплата: ${paymentStatusLabel(order.payment_status)} | К-сть: ${order.quantity_total} | Сума: ${formatPrice(order.total)} грн`, {
            size: 10,
            color: [140, 103, 116],
        })
        drawGap(cursor, 2)
    })

    if (payload.topProducts?.length) {
        drawGap(cursor, 4)
        drawLine(doc, cursor, 'Топ товарів', { size: 16 })
        drawGap(cursor, 2)

        payload.topProducts.forEach((product, index) => {
            drawLine(doc, cursor, `${index + 1}. ${product.product_name} - ${product.quantity_sold} шт. - ${formatPrice(product.revenue_total)} грн`, { size: 11 })
        })
    }

    if (payload.dailyBreakdown?.length) {
        drawGap(cursor, 4)
        drawLine(doc, cursor, 'Динаміка по днях', { size: 16 })
        drawGap(cursor, 2)

        payload.dailyBreakdown.forEach((row) => {
            drawLine(doc, cursor, `${row.date} - ${row.orders_count} замовлень - ${formatPrice(row.revenue_total)} грн`, { size: 11 })
        })
    }

    doc.save(reportFilename())
}
