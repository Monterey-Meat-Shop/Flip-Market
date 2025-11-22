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

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }

        .data-table thead {
            background-color: #f5f5f5;
        }

        .data-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            color: #222;
            border-bottom: 2px solid #ddd;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .data-table tbody tr:hover {
            background-color: #fafafa;
        }

        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .rank-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 10px;
        }

        .rank-1 { background-color: #ffd700; color: #000; }
        .rank-2 { background-color: #c0c0c0; color: #000; }
        .rank-3 { background-color: #cd7f32; color: #fff; }
        .rank-default { background-color: #e0e0e0; color: #666; }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .highlight {
            background-color: #fff9e6;
            font-weight: bold;
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

        .page-break {
            page-break-after: always;
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
        <div class="summary-card">
            <h3>Unique Customers</h3>
            <p class="value">{{ $summary['unique_customers'] ?? 0 }}</p>
            <p class="subtitle">Active Buyers</p>
        </div>
        <div class="summary-card">
            <h3>Products Sold</h3>
            <p class="value">{{ $summary['total_products_sold'] ?? 0 }}</p>
            <p class="subtitle">{{ $summary['unique_products'] ?? 0 }} Unique Items</p>
        </div>
    </div>

    <!-- Overall Summary Section -->
    <div class="section-header">{{ $summary['period_type'] ?? 'Weekly' }} Summary</div>
    <div class="summary-box">
        <p><strong>Total Records:</strong> {{ count($rows) }} transactions</p>
        <p><strong>Orders:</strong> {{ $summary['orders_count'] ?? 0 }} (₱{{ number_format($summary['orders_total'] ?? 0, 2) }})</p>
        <p><strong>Payments:</strong> {{ $summary['payments_count'] ?? 0 }} (₱{{ number_format($summary['payments_total'] ?? 0, 2) }})</p>
        <p><strong>Pending Payments:</strong> {{ $summary['payments_pending'] ?? 0 }}</p>
        <p><strong>Average Order Value:</strong> ₱{{ number_format($summary['avg_order_value'] ?? 0, 2) }}</p>
        <p><strong>Total {{ $summary['period_type'] ?? 'Weekly' }} Revenue:</strong> ₱{{ number_format(($summary['orders_total'] ?? 0) + ($summary['payments_total'] ?? 0), 2) }}</p>
    </div>

    <!-- Fast Moving Products Section -->
    <div class="section-header">Top 10 Fast-Moving Products</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">Rank</th>
                <th>Product Name</th>
                <th class="text-center" style="width: 80px;">Qty Sold</th>
                <th class="text-right" style="width: 100px;">Total Sales</th>
                <th class="text-right" style="width: 100px;">Avg Price</th>
                <th class="text-center" style="width: 80px;">Orders</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fastMovingProducts ?? [] as $index => $product)
            <tr class="{{ $index < 3 ? 'highlight' : '' }}">
                <td class="text-center">
                    <span class="rank-badge rank-{{ $index < 3 ? ($index + 1) : 'default' }}">
                        {{ $index + 1 }}
                    </span>
                </td>
                <td>{{ $product['name'] ?? 'N/A' }}</td>
                <td class="text-center"><strong>{{ $product['quantity'] ?? 0 }}</strong></td>
                <td class="text-right">₱{{ number_format($product['total_sales'] ?? 0, 2) }}</td>
                <td class="text-right">₱{{ number_format($product['avg_price'] ?? 0, 2) }}</td>
                <td class="text-center">{{ $product['order_count'] ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #888;">
                    No product data available for this period
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Customer Purchase Analysis -->
    <div class="section-header">Top 15 Customer Orders</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50px;">No.</th>
                <th>Customer Name</th>
                <th class="text-center" style="width: 80px;">Orders</th>
                <th class="text-right" style="width: 120px;">Total Spent</th>
                <th class="text-center" style="width: 80px;">Items</th>
                <th class="text-right" style="width: 100px;">Avg Order</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topCustomers ?? [] as $index => $customer)
            <tr class="{{ $index < 5 ? 'highlight' : '' }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $customer['name'] ?? 'Guest Customer' }}</td>
                <td class="text-center"><strong>{{ $customer['order_count'] ?? 0 }}</strong></td>
                <td class="text-right">₱{{ number_format($customer['total_spent'] ?? 0, 2) }}</td>
                <td class="text-center">{{ $customer['total_items'] ?? 0 }}</td>
                <td class="text-right">₱{{ number_format($customer['avg_order_value'] ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #888;">
                    No customer data available for this period
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Detailed Customer Product Orders -->
    <div class="page-break"></div>
    <div class="section-header">Customer Product Purchase Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 180px;">Customer</th>
                <th>Product</th>
                <th class="text-center" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 100px;">Unit Price</th>
                <th class="text-right" style="width: 100px;">Total</th>
                <th style="width: 100px;">Order Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customerProductOrders ?? [] as $order)
            <tr>
                <td>{{ $order['customer_name'] ?? 'Guest' }}</td>
                <td>{{ $order['product_name'] ?? 'N/A' }}</td>
                <td class="text-center">{{ $order['quantity'] ?? 0 }}</td>
                <td class="text-right">₱{{ number_format($order['unit_price'] ?? 0, 2) }}</td>
                <td class="text-right">₱{{ number_format($order['total'] ?? 0, 2) }}</td>
                <td>{{ $order['order_date'] ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #888;">
                    No detailed order data available
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Product Category Performance (Optional) -->
    @if(isset($categoryPerformance) && count($categoryPerformance) > 0)
    <div class="section-header">Product Category Performance</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-center" style="width: 100px;">Products Sold</th>
                <th class="text-right" style="width: 120px;">Total Sales</th>
                <th class="text-right" style="width: 100px;">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryPerformance as $category)
            <tr>
                <td>{{ $category['name'] ?? 'Uncategorized' }}</td>
                <td class="text-center">{{ $category['quantity'] ?? 0 }}</td>
                <td class="text-right">₱{{ number_format($category['total_sales'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($category['percentage'] ?? 0, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>FLIP MARKET</strong> | {{ $summary['report_title'] ?? 'Sales & Orders Report' }}</p>
        <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
        <small>This report summarizes {{ strtolower($summary['period_type'] ?? 'weekly') }} transactions, customer behavior, and product performance data. For inquiries, please contact the system administrator.</small>
    </div>
</body>
</html>