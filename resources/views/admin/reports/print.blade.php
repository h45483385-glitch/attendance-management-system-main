<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - {{ ucfirst($type) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #334155;
            margin: 40px;
            background-color: #fff;
        }
        
        .print-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 20px;
            margin: 0;
        }
        .logo-blue { color: #116fb7; }
        .logo-green { color: #22c55e; }
        
        .report-title {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
        }
        
        .report-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.8px;
            padding: 12px 15px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .badge {
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="print-header">
        <div>
            <h3 class="logo-text">
                <span class="logo-blue">PRAGNAWARE</span> <span class="logo-green">SOLUTIONS</span>
            </h3>
            <p class="report-meta">Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
        <div style="text-align: right;">
            <p class="report-title">{{ ucfirst($type) }} Attendance Report</p>
            <p class="report-meta">Period: {{ $startDate }} @if($startDate !== $endDate) to {{ $endDate }} @endif</p>
        </div>
    </div>

    <!-- REPORT TABLE VIEW -->
    @if($type === 'overtime')
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th style="text-align: center;">Scheduled Mins</th>
                    <th style="text-align: center;">Worked Mins</th>
                    <th style="text-align: center;">Overtime Mins</th>
                    <th style="text-align: right;">Overtime Pay</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>#{{ $row->employee->id }}</td>
                    <td style="font-weight: 600;">{{ $row->employee->name }}</td>
                    <td>{{ $row->employee->department ?? 'General' }}</td>
                    <td style="text-align: center;">{{ $row->scheduled_minutes }}</td>
                    <td style="text-align: center;">{{ $row->worked_minutes }}</td>
                    <td style="text-align: center; font-weight: 600;">{{ $row->overtime_minutes }}</td>
                    <td style="text-align: right; font-weight: 600;">${{ number_format($row->overtime_pay, 2) }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $row->overtime_status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($row->overtime_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #64748b;">No overtime records in range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'absence')
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>#{{ $row->employee->id }}</td>
                    <td style="font-weight: 600;">{{ $row->employee->name }}</td>
                    <td>{{ $row->employee->department ?? 'General' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-danger">Absent</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No absences found in this range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'department')
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th style="text-align: center;">Worked Mins</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $dept => $rows)
                    @foreach($rows as $row)
                    <tr>
                        @if($loop->first)
                            <td rowspan="{{ $rows->count() }}" style="font-weight: 600; vertical-align: middle; background-color: #f8fafc; border-right: 1px solid #cbd5e1;">
                                {{ $dept }}
                            </td>
                        @endif
                        <td>#{{ $row->employee->id }}</td>
                        <td style="font-weight: 600;">{{ $row->employee->name }}</td>
                        <td>{{ $row->attendance_date }}</td>
                        <td>{{ $row->attendance_time }}</td>
                        <td>{{ $row->check_out_time ?? 'N/A' }}</td>
                        <td style="text-align: center;">{{ $row->worked_minutes ?? 0 }}</td>
                        <td style="text-align: center;">
                            <span class="badge {{ $row->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                {{ $row->status == 1 ? 'On Time' : 'Late' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b;">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th style="text-align: center;">Worked Mins</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row->attendance_date }}</td>
                    <td>#{{ $row->employee->id }}</td>
                    <td style="font-weight: 600;">{{ $row->employee->name }}</td>
                    <td>{{ $row->employee->department ?? 'General' }}</td>
                    <td>{{ $row->attendance_time }}</td>
                    <td>{{ $row->check_out_time ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $row->worked_minutes ?? 0 }}</td>
                    <td style="text-align: center;">
                        <span class="badge {{ $row->status == 1 ? 'badge-success' : 'badge-danger' }}">
                            {{ $row->status == 1 ? 'On Time' : 'Late' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b;">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
