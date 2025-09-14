<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1, h3 {
            text-align: center;
            margin: 0;
        }
        .subtitle {
            text-align: center;
            font-size: 13px;
            margin-bottom: 20px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #f4f4f4;
            font-weight: bold;
        }
        tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            font-size: 11px;
            text-align: right;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Weekly Orders & Sales Report</h1>
    <div class="subtitle">
        Generated on {{ now()->format('F d, Y h:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Week</th>
                <th>Orders</th>
                <th>Sales Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row[0] }}</td>
                    <td>{{ $row[1] }}</td>
                    <td>{{ $row[2] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total</strong></td>
                <td>{{ collect($data)->sum(fn($r) => $r[1]) }}</td>
                <td>
                    ₱{{ number_format(collect($data)->sum(fn($r) => (float) str_replace(['₱', ','], '', $r[2])), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Report generated automatically by the system.
    </div>
</body>
</html>
