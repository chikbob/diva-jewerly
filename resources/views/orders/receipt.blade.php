<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чек замовлення #{{ $order->id }}</title>
    @vite('resources/js/receipt.js')
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
<body data-auto-print="{{ $autoPrint ? '1' : '0' }}" data-auto-download-pdf="{{ $autoDownloadPdf ? '1' : '0' }}">
    <script id="receipt-order-data" type="application/json">{!! \Illuminate\Support\Js::encode($receiptPayload) !!}</script>

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

</body>
</html>
