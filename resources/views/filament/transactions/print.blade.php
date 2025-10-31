<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Receipt - {{ $order->orderID }}</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    /* Page size for 80mm thermal printer */
    @page {
      size: 80mm auto;
      margin: 5mm;
    }

    html, body {
      margin: 0;
      padding: 0;
      -webkit-print-color-adjust: exact;
      font-family: "Arial", "Helvetica", sans-serif;
      color: #111;
      background: #fff;
    }

    .receipt {
      width: 80mm;                   /* fixed receipt width for thermal printers */
      max-width: 80mm;
      margin: 0 auto;
      padding: 6px 6px 10px 6px;
      box-sizing: border-box;
      font-size: 12px;
      line-height: 1.25;
      color: #111;
    }

    .center { text-align: center; }
    .right { text-align: right; }
    .muted { color: #666; font-size: 11px; }
    .brand { font-weight: 700; font-size: 14px; letter-spacing: 0.5px; }
    .small { font-size: 11px; }
    hr.dashed { border: none; border-top: 1px dashed #bbb; margin: 6px 0; }

    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    th, td { padding: 4px 0; vertical-align: top; }
    .item-name { font-size: 12px; display:block; }
    .item-meta { font-size: 11px; color:#666; display:block; margin-top:2px; }
    .qty { width: 18%; }
    .unit { width: 28%; }
    .subtotal { width: 34%; text-align: right; }

    .totals { margin-top: 6px; }
    .totals .label { font-size: 12px; color:#333; }
    .totals .value { font-weight:700; font-size:13px; text-align: right; }

    .footer { margin-top:8px; font-size:11px; text-align:center; color:#444; }
    .barcode, .qr { margin-top:8px; text-align:center; }

    /* Hide controls on print */
    .no-print { display: block; margin-top: 8px; }
    @media print {
      .no-print { display:none !important; }
      body, .receipt { background: #fff; }
    }
  </style>
</head>
<body>
  <div class="receipt" role="document" aria-label="Receipt">
    <div class="center">
      <div class="brand">FLIP MARKET</div>
      <div class="muted small">Official Receipt</div>
      <div class="muted small">{{ config('app.address') ?? '' }}</div>
      <div class="muted small">{{ config('app.phone') ?? '' }}</div>
    </div>

    <hr class="dashed" />

    <div>
      <div><strong>Receipt #</strong> {{ $order->orderID }}</div>
      <div class="muted small">
        {{ $order->created_at?->format('M d, Y') ?? '' }}
        &nbsp; {{ $order->created_at?->format('H:i') ?? '' }}
      </div>
      <div class="muted small">Customer: {{ $order->customer?->first_name ?? 'Guest' }} {{ $order->customer?->last_name ?? '' }}</div>
      <div class="muted small">Payment: {{ $order->payment?->paymentMethod?->method_name ?? 'N/A' }}</div>
      @if($order->payment?->reference_number)
        <div class="muted small">Ref: {{ $order->payment->reference_number }}</div>
      @endif
    </div>

    <hr class="dashed" />

    <table aria-describedby="items">
      <thead>
        <tr>
          <th class="qty small">QTY</th>
          <th class="unit small">UNIT</th>
          <th class="subtotal small right">AMOUNT</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->orderItems as $item)
          <tr>
            <td class="qty">{{ $item->quantity }}</td>
            <td class="unit">
              <span class="item-name">{{ $item->product?->name ?? ($item->product_name ?? 'Item') }}</span>
              <span class="item-meta">
                {{ $item->size ?? '' }} {{ $item->colorway ?? '' }}
                &nbsp;·&nbsp; ₱{{ number_format($item->unit_price, 2) }}
              </span>
            </td>
            <td class="subtotal">₱{{ number_format($item->sub_total, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <hr class="dashed" />

    <div class="totals">
      <table>
        <tr>
          <td class="label small">Subtotal</td>
          <td class="value">₱{{ number_format(collect($order->orderItems)->sum('sub_total'), 2) }}</td>
        </tr>
        @if($order->payment?->discount_amount ?? false)
          <tr>
            <td class="label small">Discount</td>
            <td class="value">-₱{{ number_format($order->payment->discount_amount, 2) }}</td>
          </tr>
        @endif
        <tr>
          <td class="label small">Total</td>
          <td class="value">₱{{ number_format($order->final_amount, 2) }}</td>
        </tr>
        <tr>
          <td class="label small">Paid</td>
          <td class="value">₱{{ number_format($order->payment?->amount ?? $order->final_amount, 2) }}</td>
        </tr>
      </table>
    </div>

    <div class="footer">
      <div>Order Status: <strong>{{ $order->order_status }}</strong></div>
      <div class="muted small">Thank you for your purchase!</div>
      <div class="muted small">Powered by Flip Market</div>
    </div>

    {{-- optional auto-print and controls --}}
    <div class="no-print center">
      <button onclick="window.print()" style="padding:8px 12px;border:0;background:#0b74de;color:#fff;border-radius:6px;cursor:pointer">Print</button>
      <a href="{{ url()->previous() }}" style="padding:8px 12px;border:1px solid #ccc;background:#fff;color:#111;border-radius:6px;text-decoration:none;margin-left:8px">Back</a>
    </div>
  </div>

  <script>
    // Auto open print dialog for convenience; remove if undesired.
    window.addEventListener('DOMContentLoaded', function () {
      setTimeout(function () {
        try { window.print(); } catch(e) {}
      }, 250);
    });
  </script>
</body>
</html>