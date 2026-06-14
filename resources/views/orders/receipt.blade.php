<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чек замовлення #{{ $order->id }}</title>
    <script src="/vendor/jspdf/jspdf.umd.min.js"></script>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f1f4;
            color: #5f3d48;
        }

        .page {
            max-width: 820px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .toolbar a,
        .toolbar button {
            appearance: none;
            border: 1px solid #d7b0bf;
            border-radius: 999px;
            background: #fff;
            color: #7f485b;
            cursor: pointer;
            font: inherit;
            padding: 12px 18px;
            text-decoration: none;
        }

        .receipt {
            background: #fff;
            border: 1px solid #edd6df;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 18px 40px rgba(180, 109, 109, 0.08);
        }

        .header,
        .summary-row,
        .meta-grid,
        .line-item,
        .footer {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }

        .header {
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1e0e6;
        }

        .eyebrow,
        .muted {
            color: #b27b8f;
            font-size: 12px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        h1 {
            margin-top: 10px;
            font-size: 32px;
        }

        .section {
            margin-top: 24px;
        }

        .meta-grid {
            flex-wrap: wrap;
        }

        .meta-card {
            flex: 1 1 220px;
            border: 1px solid #f1e0e6;
            border-radius: 18px;
            background: #fff8fb;
            padding: 16px;
        }

        .items {
            margin-top: 16px;
            border-top: 1px solid #f1e0e6;
        }

        .line-item {
            padding: 16px 0;
            border-bottom: 1px solid #f6ebef;
            align-items: flex-start;
        }

        .summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1e0e6;
        }

        .summary-row {
            margin-top: 10px;
            font-size: 15px;
        }

        .summary-row.total {
            font-size: 22px;
            font-weight: 700;
        }

        .footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #f1e0e6;
            font-size: 13px;
            color: #8c6774;
        }

        @media print {
            body {
                background: #fff;
            }

            .page {
                max-width: none;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .receipt {
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @if ($showActions)
            <div class="toolbar">
                <a href="{{ route('orders.show', ['order' => $order->id]) }}">Повернутися до замовлення</a>
                <button type="button" onclick="downloadReceiptPdf()">Завантажити PDF</button>
                <button type="button" onclick="window.print()">Друк</button>
            </div>
        @endif

        <article class="receipt">
            <header class="header">
                <div>
                    <p class="eyebrow">DIVA Jewelry</p>
                    <h1>Чек замовлення #{{ $order->id }}</h1>
                    <p style="margin-top: 12px; color: #8c6774;">
                        Створено {{ $order->created_at?->format('d.m.Y H:i') ?? 'невідомо' }}
                    </p>
                </div>

                <div style="text-align: right;">
                    <p class="muted">Сума</p>
                    <p style="margin-top: 10px; font-size: 28px; font-weight: 700;">{{ number_format((float) $order->total, 2, '.', ' ') }} ₴</p>
                </div>
            </header>

            <section class="section meta-grid">
                <div class="meta-card">
                    <p class="muted">Отримувач</p>
                    <p style="margin-top: 10px; font-size: 18px; font-weight: 700;">{{ $order->full_name }}</p>
                    <p style="margin-top: 8px;">{{ $order->email }}</p>
                </div>

                <div class="meta-card">
                    <p class="muted">Оплата</p>
                    <p style="margin-top: 10px;">Спосіб: {{ $order->payment_method }}</p>
                    <p style="margin-top: 8px;">Статус: {{ $order->payment_status }}</p>
                    @if ($order->payment_reference)
                        <p style="margin-top: 8px;">Референс: {{ $order->payment_reference }}</p>
                    @endif
                </div>

                <div class="meta-card">
                    <p class="muted">Статус замовлення</p>
                    <p style="margin-top: 10px; font-size: 18px; font-weight: 700;">{{ $order->status }}</p>
                    <p style="margin-top: 8px;">Позицій: {{ $order->items->count() }}</p>
                    <p style="margin-top: 8px;">Товарів: {{ $order->items->sum('quantity') }}</p>
                </div>
            </section>

            <section class="section">
                <p class="muted">Склад замовлення</p>

                <div class="items">
                    @foreach ($order->items as $item)
                        <div class="line-item">
                            <div>
                                <p style="font-size: 17px; font-weight: 700;">
                                    {{ $item->product?->name ?? 'Товар недоступний' }}
                                </p>
                                @if ($item->product?->category?->name)
                                    <p style="margin-top: 8px; color: #8c6774;">{{ $item->product->category->name }}</p>
                                @endif
                            </div>

                            <div style="text-align: right;">
                                <p>{{ $item->quantity }} x {{ number_format((float) $item->price, 2, '.', ' ') }} ₴</p>
                                <p style="margin-top: 8px; font-weight: 700;">
                                    {{ number_format((float) $item->price * (int) $item->quantity, 2, '.', ' ') }} ₴
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="summary">
                <div class="summary-row">
                    <span>Кількість позицій</span>
                    <strong>{{ $order->items->count() }}</strong>
                </div>
                <div class="summary-row">
                    <span>Кількість товарів</span>
                    <strong>{{ $order->items->sum('quantity') }}</strong>
                </div>
                <div class="summary-row total">
                    <span>Разом</span>
                    <strong>{{ number_format((float) $order->total, 2, '.', ' ') }} ₴</strong>
                </div>
            </section>

            <footer class="footer">
                <p>DIVA Jewelry</p>
                <p>Електронний чек сформовано {{ now()->format('d.m.Y H:i') }}</p>
            </footer>
        </article>
    </div>

    <script>
        const receiptOrder = @json([
            'id' => $order->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'full_name' => $order->full_name,
            'email' => $order->email,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'payment_reference' => $order->payment_reference,
            'status' => $order->status,
            'total' => (float) $order->total,
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product?->name ?? 'Товар недоступний',
                'product_category' => $item->product?->category?->name,
                'product_description' => $item->product?->description,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'line_total' => (float) $item->price * (int) $item->quantity,
            ])->values()->all(),
        ]);

        let fontPromise;

        function formatPrice(value) {
            return Number(value ?? 0).toLocaleString('uk-UA', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            });
        }

        function formatDate(value) {
            if (!value) {
                return 'невідомо';
            }

            return new Date(value).toLocaleString('uk-UA');
        }

        function statusLabel(status) {
            return {
                pending: 'В очікуванні',
                paid: 'Сплачено',
                failed: 'Помилка',
                cancelled: 'Скасовано',
            }[status] ?? status;
        }

        function paymentMethodLabel(paymentMethod) {
            return {
                demo_card: 'Демо-картка',
                cash_on_delivery: 'Післяплата',
            }[paymentMethod] ?? paymentMethod;
        }

        function paymentStatusLabel(status) {
            return {
                paid: 'Оплачено',
                pending: 'Оплата очікується',
                failed: 'Оплата неуспішна',
                cancelled: 'Оплату скасовано',
            }[status] ?? status;
        }

        function arrayBufferToBase64(buffer) {
            const bytes = new Uint8Array(buffer);
            const chunkSize = 0x8000;
            let binary = '';

            for (let i = 0; i < bytes.length; i += chunkSize) {
                const chunk = bytes.subarray(i, i + chunkSize);
                binary += String.fromCharCode(...chunk);
            }

            return btoa(binary);
        }

        async function loadFontBase64() {
            if (fontPromise) {
                return fontPromise;
            }

            fontPromise = fetch('/fonts/Roboto-Regular.ttf')
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Failed to load PDF font');
                    }

                    return response.arrayBuffer();
                })
                .then((buffer) => arrayBufferToBase64(buffer));

            return fontPromise;
        }

        async function createPdf() {
            const doc = new window.jspdf.jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4',
            });

            const fontBase64 = await loadFontBase64();

            if (!doc.getFontList().Roboto) {
                doc.addFileToVFS('Roboto-Regular.ttf', fontBase64);
                doc.addFont('Roboto-Regular.ttf', 'Roboto', 'normal');
            }

            doc.setFont('Roboto', 'normal');
            doc.setTextColor(95, 61, 72);

            return doc;
        }

        function ensureSpace(doc, cursor, heightNeeded) {
            if (cursor.y + heightNeeded <= cursor.pageHeight - cursor.margin) {
                return;
            }

            doc.addPage();
            doc.setFont('Roboto', 'normal');
            doc.setTextColor(95, 61, 72);
            cursor.y = cursor.margin;
        }

        function drawLine(doc, cursor, text, options = {}) {
            const size = options.size ?? 11;
            const x = options.x ?? cursor.margin;
            const color = options.color ?? [95, 61, 72];
            const maxWidth = options.maxWidth ?? cursor.pageWidth - cursor.margin * 2;
            const lineHeight = options.lineHeight ?? size * 0.42 + 2.2;

            doc.setFontSize(size);
            doc.setTextColor(...color);

            const lines = doc.splitTextToSize(String(text ?? ''), maxWidth);
            ensureSpace(doc, cursor, Math.max(lines.length, 1) * lineHeight);
            doc.text(lines, x, cursor.y);
            cursor.y += Math.max(lines.length, 1) * lineHeight;
        }

        async function downloadReceiptPdf() {
            const doc = await createPdf();
            const cursor = {
                margin: 16,
                pageWidth: doc.internal.pageSize.getWidth(),
                pageHeight: doc.internal.pageSize.getHeight(),
                y: 16,
            };

            drawLine(doc, cursor, 'DIVA Jewelry', { size: 12, color: [178, 123, 143] });
            cursor.y += 2;
            drawLine(doc, cursor, `Чек замовлення #${receiptOrder.id}`, { size: 22 });
            drawLine(doc, cursor, `Створено: ${formatDate(receiptOrder.created_at)}`, { size: 11, color: [140, 103, 116] });
            cursor.y += 5;

            drawLine(doc, cursor, `Отримувач: ${receiptOrder.full_name}`, { size: 12 });
            drawLine(doc, cursor, `Email: ${receiptOrder.email}`, { size: 12 });
            drawLine(doc, cursor, `Статус замовлення: ${statusLabel(receiptOrder.status)}`, { size: 12 });
            drawLine(doc, cursor, `Статус оплати: ${paymentStatusLabel(receiptOrder.payment_status)}`, { size: 12 });
            drawLine(doc, cursor, `Спосіб оплати: ${paymentMethodLabel(receiptOrder.payment_method)}`, { size: 12 });

            if (receiptOrder.payment_reference) {
                drawLine(doc, cursor, `Платіжний референс: ${receiptOrder.payment_reference}`, { size: 12 });
            }

            cursor.y += 6;
            drawLine(doc, cursor, 'Склад замовлення', { size: 16 });
            cursor.y += 2;

            receiptOrder.items.forEach((item, index) => {
                drawLine(doc, cursor, `${index + 1}. ${item.product_name}`, { size: 12 });

                if (item.product_category) {
                    drawLine(doc, cursor, item.product_category, { size: 10, color: [178, 123, 143] });
                }

                drawLine(doc, cursor, `${item.quantity} x ${formatPrice(item.price)} грн = ${formatPrice(item.line_total)} грн`, {
                    size: 11,
                    color: [140, 103, 116],
                });

                if (item.product_description) {
                    drawLine(doc, cursor, item.product_description, { size: 10, color: [140, 103, 116] });
                }

                cursor.y += 3;
            });

            drawLine(doc, cursor, `Разом: ${formatPrice(receiptOrder.total)} грн`, { size: 18 });
            doc.save(`receipt-order-${receiptOrder.id}.pdf`);
        }
    </script>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', () => {
                window.print()
            })
        </script>
    @endif

    @if ($autoDownloadPdf)
        <script>
            window.addEventListener('load', () => {
                downloadReceiptPdf()
            })
        </script>
    @endif
</body>
</html>
