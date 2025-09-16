<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Sales & Orders Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 25px;
            color: #333;
            background-color: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #aaa;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #111;
        }

        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #555;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
        }

        .summary-card {
            background-color: #fdfdfd;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .summary-card h3 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #444;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin: 0;
        }

        .summary-card .subtitle {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .section-header {
            margin: 15px 0 8px 0;
            font-size: 13px;
            font-weight: bold;
            color: #222;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .summary-box {
            background-color: #fafafa;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            line-height: 1.5;
            color: #333;
        }

        .summary-box p {
            margin: 4px 0;
            font-size: 11px;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
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
        <p><strong>Weekly Sales & Orders Report</strong></p>
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
        <div class="summary-card">
            <h3>Total Payments</h3>
            <p class="value">{{ $summary['payments_count'] ?? 0 }}</p>
            <p class="subtitle">₱{{ number_format($summary['payments_total'] ?? 0, 2) }}</p>
        </div>
        <div class="summary-card">
            <h3>Pending Payments</h3>
            <p class="value">{{ $summary['payments_pending'] ?? 0 }}</p>
            <p class="subtitle">Awaiting Processing</p>
        </div>
    </div>

    <!-- Weekly Summary -->
    <div class="section-header">Weekly Summary</div>
    <div class="summary-box">
        <p><strong>Total Records:</strong> {{ count($rows) }} transactions</p>
        <p><strong>Orders:</strong> {{ $summary['orders_count'] ?? 0 }} (₱{{ number_format($summary['orders_total'] ?? 0, 2) }})</p>
        <p><strong>Payments:</strong> {{ $summary['payments_count'] ?? 0 }} (₱{{ number_format($summary['payments_total'] ?? 0, 2) }})</p>
        <p><strong>Pending Payments:</strong> {{ $summary['payments_pending'] ?? 0 }}</p>
        <p><strong>Total Weekly Revenue:</strong> ₱{{ number_format(($summary['orders_total'] ?? 0) + ($summary['payments_total'] ?? 0), 2) }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>FLIP MARKET | Weekly Sales & Orders Report</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
        <p style="margin-top: 5px; font-size: 9px;">
            This is a formal report containing summarized data for the week. For inquiries, please contact the system administrator.
        </p>
    </div>
</body>
</html>
