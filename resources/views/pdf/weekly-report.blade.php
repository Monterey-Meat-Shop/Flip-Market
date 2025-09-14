<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Sales & Orders Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            text-align: center;
        }
        
        .summary-card.orders {
            border-left-color: #28a745;
        }
        
        .summary-card.payments {
            border-left-color: #17a2b8;
        }
        
        .summary-card.pending {
            border-left-color: #ffc107;
        }
        
        .summary-card h3 {
            margin: 0 0 5px 0;
            color: #495057;
            font-size: 14px;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .summary-card .subtitle {
            font-size: 11px;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .section-header {
            background-color: #e9ecef;
            padding: 10px 15px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #007bff;
        }
        
        .section-header h2 {
            margin: 0;
            color: #495057;
            font-size: 16px;
        }
        
        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
        }
        
        th {
            background-color: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #dee2e6;
        }
        
        td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .order-row {
            background-color: #e8f5e8 !important;
        }
        
        .payment-row {
            background-color: #e8f4f8 !important;
        }
        
        .type-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-order {
            background-color: #28a745;
            color: white;
        }
        
        .type-payment {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-pending {
            color: #856404;
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .status-completed {
            color: #155724;
            background-color: #d4edda;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .status-failed {
            color: #721c24;
            background-color: #f8d7da;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        @page {
            margin: 20mm 15mm;
        }
        
        @media print {
            body { font-size: 11px; }
            th { font-size: 10px; }
            td { font-size: 9px; }
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <h1>FLIP MARKET Weekly Sales & Orders Report</h1>
        <p>Generated on: {{ now()->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A T') }}</p>
        <p>Report Period: {{ $summary['period_start'] ?? now()->setTimezone('Asia/Manila')->startOfWeek()->format('M j') }} - {{ $summary['period_end'] ?? now()->setTimezone('Asia/Manila')->endOfWeek()->format('M j, Y') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card orders">
            <h3>Total Orders</h3>
            <p class="value">{{ $summary['orders_count'] ?? 0 }}</p>
            <p class="subtitle">₱{{ number_format($summary['orders_total'] ?? 0, 2) }}</p>
        </div>
        
        <div class="summary-card payments">
            <h3>Total Payments</h3>
            <p class="value">{{ $summary['payments_count'] ?? 0 }}</p>
            <p class="subtitle">₱{{ number_format($summary['payments_total'] ?? 0, 2) }}</p>
        </div>
        
        <div class="summary-card pending">
            <h3>Pending Payments</h3>
            <p class="value">{{ $summary['payments_pending'] ?? 0 }}</p>
            <p class="subtitle">Awaiting Processing</p>
        </div>
    </div>

    <!-- Section Header -->
    <div class="section-header">
        <h2>Weekly Transaction Details</h2>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    @foreach($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="{{ strtolower($row[0]) === 'order' ? 'order-row' : 'payment-row' }}">
                        @foreach($row as $index => $cell)
                            <td>
                                @if($index === 0)
                                    {{-- Type column with badge --}}
                                    <span class="type-badge type-{{ strtolower($cell) }}">{{ $cell }}</span>
                                @elseif($index === 4)
                                    {{-- Status column with styling --}}
                                    <span class="status-{{ strtolower($cell) }}">{{ $cell }}</span>
                                @else
                                    {{ $cell ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headings) }}" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                            No transactions found for this week.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary Statistics -->
    <div class="section-header">
        <h2>Week Summary</h2>
    </div>
    
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 5px 0;"><strong>Total Records:</strong> {{ count($rows) }} transactions</p>
        <p style="margin: 5px 0;"><strong>Orders:</strong> {{ $summary['orders_count'] ?? 0 }} (₱{{ number_format($summary['orders_total'] ?? 0, 2) }})</p>
        <p style="margin: 5px 0;"><strong>Payments:</strong> {{ $summary['payments_count'] ?? 0 }} (₱{{ number_format($summary['payments_total'] ?? 0, 2) }})</p>
        <p style="margin: 5px 0;"><strong>Pending Payments:</strong> {{ $summary['payments_pending'] ?? 0 }}</p>
        <p style="margin: 5px 0;"><strong>Week Total Revenue:</strong> ₱{{ number_format(($summary['orders_total'] ?? 0) + ($summary['payments_total'] ?? 0), 2) }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            Weekly Sales & Orders Report | 
            Page <span class="pagenum"></span> | 
            Generated: {{ now()->format('Y-m-d H:i:s') }}
        </p>
        <p style="margin-top: 5px; font-size: 9px;">
            This report contains orders and payments data for the current week. For questions, contact the system administrator.
        </p>
    </div>
</body>
</html>