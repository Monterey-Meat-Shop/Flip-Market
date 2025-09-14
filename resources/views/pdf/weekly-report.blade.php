<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Report</title>
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
        
        .report-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .report-info h2 {
            margin-top: 0;
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
        
        tr:hover {
            background-color: #e3f2fd;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .summary-item {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        
        .summary-item h3 {
            margin: 0;
            color: #1976d2;
            font-size: 14px;
        }
        
        .summary-item p {
            margin: 5px 0 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        @page {
            margin: 20mm 15mm;
        }
        
        /* Responsive adjustments for smaller content */
        @media print {
            body { font-size: 11px; }
            th { font-size: 10px; }
            td { font-size: 9px; }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <h1>Weekly Report</h1>
        <p>Generated on: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>Report Period: {{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j, Y') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary">
        <div class="summary-item">
            <h3>Total Records</h3>
            <p>{{ count($rows) }}</p>
        </div>
        <div class="summary-item">
            <h3>Report Date</h3>
            <p>{{ now()->format('M j, Y') }}</p>
        </div>
        <div class="summary-item">
            <h3>Status</h3>
            <p>Complete</p>
        </div>
    </div>

    <!-- Report Information -->
    <div class="report-info">
        <h2>Report Details</h2>
        <p><strong>Description:</strong> This weekly report contains a comprehensive overview of all recorded data for the specified period.</p>
        <p><strong>Total Entries:</strong> {{ count($rows) }} records</p>
        <p><strong>Export Format:</strong> PDF Document</p>
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
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell ?? '-' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headings) }}" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                            No data available for this report period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'Application') }}</strong> | 
            Weekly Report | 
            Page <span class="pagenum"></span> | 
            Generated: {{ now()->format('Y-m-d H:i:s') }}
        </p>
        <p style="margin-top: 5px; font-size: 9px;">
            This report was automatically generated. For questions or concerns, please contact the system administrator.
        </p>
    </div>
</body>
</html>