<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $summary['report_title'] ?? 'Sales & Orders Report' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 25px;
            color: #2c2c2c;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #444;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            color: #222;
            letter-spacing: 1px;
        }

        .header p {
            margin: 4px 0;
            font-size: 11px;
            color: #666;
        }

        /* Summary Cards Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin: 25px 0 20px 0;
        }

        .summary-card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }

        .summary-card h3 {
            margin: 0 0 6px 0;
            font-size: 13px;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            margin: 0;
        }

        .summary-card .subtitle {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }

        /* Section Header */
        .section-header {
            margin: 25px 0 10px 0;
            font-size: 13px;
            font-weight: bold;
            color: #222;
            border-left: 4px solid #0077cc;
            padding-left: 8px;
            letter-spacing: 0.3px;
        }

        /* Summary Box */
        .summary-box {
            background-color: #fcfcfc;
            padding: 14px;
            border-radius: 6px;
            border: 1px solid #ddd;
            line-height: 1.6;
            color: #333;
        }

        .summary-box p {
            margin: 4px 0;
            font-size: 11px;
        }

        .summary-box strong {
            color: #111;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #bbb;
            padding-top: 8px;
        }

        .footer p {
            margin: 3px 0;
        }

        .footer small {
            display: block;
            margin-top: 6px;
            font-size: 9px;
            color: #888;
        }

        @page {
            margin: 15mm 12mm;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FLIP MARKET</h1>
        <p><strong>{{ $summary['report_title'] ?? 'Sales & Orders Report' }}</strong></p>
        <p>Generated on: {{ now()->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A T') }}</p>
        <p>Period: {{ $summary['period_start'] ?? now()->startOfWeek()->format('M j') }} – {{ $summary['period_end'] ?? now()->endOfWeek()->format('M j, Y') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <h3>Total Orders</h3>
            <p class="value">{{ $summary['orders_count'] ?? 0 }}</p>
            <p class="subtitle">₱{{ number_format($summary['orders_total'] ?? 0, 2) }}</p>
        </div>
        {{-- <div class="summary-card">
            <h3>Total Payments</h3>
            <p class="value">{{ $summary['payments_count'] ?? 0 }}</p>
            <p class="subtitle">₱{{ number_format($summary['payments_total'] ?? 0, 2) }}</p>
        </div>
        <div class="summary-card">
            <h3>Pending Payments</h3>
            <p class="value">{{ $summary['payments_pending'] ?? 0 }}</p>
            <p class="subtitle">Awaiting Processing</p>
        </div> --}}
    </div>

    <!-- Summary Section -->
    <div class="section-header">{{ $summary['period_type'] ?? 'Weekly' }} Summary</div>
    <div class="summary-box">
        <p><strong>Total Records:</strong> {{ count($rows) }} transactions</p>
        <p><strong>Orders:</strong> {{ $summary['orders_count'] ?? 0 }} (₱{{ number_format($summary['orders_total'] ?? 0, 2) }})</p>
        <p><strong>Payments:</strong> {{ $summary['payments_count'] ?? 0 }} (₱{{ number_format($summary['payments_total'] ?? 0, 2) }})</p>
        <p><strong>Pending Payments:</strong> {{ $summary['payments_pending'] ?? 0 }}</p>
        <p><strong>Total {{ $summary['period_type'] ?? 'Weekly' }} Revenue:</strong> ₱{{ number_format(($summary['orders_total'] ?? 0) + ($summary['payments_total'] ?? 0), 2) }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>FLIP MARKET</strong> | {{ $summary['report_title'] ?? 'Sales & Orders Report' }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
        <small>This report summarizes {{ strtolower($summary['period_type'] ?? 'weekly') }} transactions and revenue data. For inquiries, please contact the system administrator.</small>
    </div>
</body>
</html>