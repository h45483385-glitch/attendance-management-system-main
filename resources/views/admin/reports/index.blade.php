@extends('layouts.master')

@section('css')
<style>
    /* Styling overrides for premium Reports design */
    .reports-card {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid #f1f5f9;
        background: #ffffff;
    }
    
    .report-type-btn {
        text-align: left;
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--text-normal);
        background: transparent;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .report-type-btn:hover {
        background-color: #f1f5f9;
        color: var(--primary-blue);
    }
    .report-type-btn.active {
        background-color: rgba(17, 111, 183, 0.08);
        color: var(--primary-blue);
        border-color: rgba(17, 111, 183, 0.15);
    }
    
    .table-modern td, .table-modern th {
        vertical-align: middle;
        padding: 14px 20px;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Reports Hub</h4>
    <p class="text-secondary font-13 mb-0">Generate, view, and export custom daily, weekly, monthly, department-wise, and exception reports.</p>
</div>
@endsection

@section('content')
<div class="row mt-3">
    <!-- 1. LEFT SIDEBAR: REPORT TYPES -->
    <div class="col-lg-3 mb-4">
        <div class="card reports-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-files text-primary mr-2"></i> Report Categories</h6>
            </div>
            <div class="card-body p-2">
                <div class="d-flex flex-column">
                    <a href="{{ route('reports.index', ['type' => 'daily']) }}" class="report-type-btn {{ $type === 'daily' ? 'active' : '' }}">
                        <span><i class="ti-calendar mr-2"></i> Daily Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'weekly']) }}" class="report-type-btn {{ $type === 'weekly' ? 'active' : '' }}">
                        <span><i class="ti-view-grid mr-2"></i> Weekly Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'monthly']) }}" class="report-type-btn {{ $type === 'monthly' ? 'active' : '' }}">
                        <span><i class="ti-calendar-list mr-2"></i> Monthly Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'employee']) }}" class="report-type-btn {{ $type === 'employee' ? 'active' : '' }}">
                        <span><i class="ti-user mr-2"></i> Employee-wise Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'department']) }}" class="report-type-btn {{ $type === 'department' ? 'active' : '' }}">
                        <span><i class="ti-briefcase mr-2"></i> Department-wise</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'late']) }}" class="report-type-btn {{ $type === 'late' ? 'active' : '' }}">
                        <span><i class="ti-alert mr-2"></i> Late Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'absence']) }}" class="report-type-btn {{ $type === 'absence' ? 'active' : '' }}">
                        <span><i class="ti-face-sad mr-2"></i> Absence Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                    <a href="{{ route('reports.index', ['type' => 'overtime']) }}" class="report-type-btn {{ $type === 'overtime' ? 'active' : '' }}">
                        <span><i class="ti-alarm-clock mr-2"></i> Overtime Report</span>
                        <i class="ti-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. RIGHT SIDEBAR: FILTERS AND DATA -->
    <div class="col-lg-9 mb-4">
        <!-- FILTER FORM -->
        <div class="card reports-card mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-filter text-primary mr-2"></i> Filter Parameters</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}" id="reportForm">
                    <input type="hidden" name="type" value="{{ $type }}">
                    
                    <div class="row align-items-end">
                        <!-- Date range (Not shown for "daily" category as it only uses start_date) -->
                        <div class="{{ $type === 'daily' ? 'col-md-4' : 'col-md-3' }} mb-3 mb-md-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-saas" value="{{ $startDate }}" required>
                        </div>
                        
                        @if($type !== 'daily')
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-saas" value="{{ $endDate }}" required>
                        </div>
                        @endif

                        <!-- Optional Filters based on report type -->
                        @if($type === 'employee-wise' || $type === 'employee' || $type === 'absence')
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Select Employee</label>
                            <select name="employee_id" class="form-control form-control-saas">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }} (#{{ $emp->id }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($type === 'department' || $type === 'absence')
                        <div class="col-md-3 mb-3 mb-md-0">
                            <label class="font-weight-bold text-muted font-12 mb-1">Select Department</label>
                            <select name="department" class="form-control form-control-saas">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ $department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md text-right">
                            <button type="submit" class="btn btn-saas btn-saas-primary w-100" style="padding: 10px;"><i class="ti-reload mr-1"></i> Generate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- GENERATED REPORT DATA VIEW -->
        <div class="card reports-card">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="ti-receipt text-primary mr-2"></i> Report Results: {{ ucfirst($type) }} Log
                </h6>
                <div class="d-flex gap-2">
                    <button onclick="exportCSV()" class="btn btn-sm btn-saas btn-saas-secondary"><i class="ti-download mr-1"></i> Excel / CSV</button>
                    <button onclick="exportPDF()" class="btn btn-sm btn-saas btn-saas-primary"><i class="ti-printer mr-1"></i> Export PDF</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    
                    @if($type === 'overtime')
                        <!-- Overtime Report Table -->
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee Details</th>
                                    <th>Department</th>
                                    <th class="text-center">Scheduled Mins</th>
                                    <th class="text-center">Worked Mins</th>
                                    <th class="text-center">Overtime Mins</th>
                                    <th class="text-right">Overtime Pay</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row->date }}</td>
                                    <td>
                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $row->employee->name }}</h6>
                                        <span class="text-muted font-11">#{{ $row->employee->id }}</span>
                                    </td>
                                    <td>{{ $row->employee->department ?? 'General' }}</td>
                                    <td class="text-center">{{ $row->scheduled_minutes }}</td>
                                    <td class="text-center">{{ $row->worked_minutes }}</td>
                                    <td class="text-center font-weight-bold text-primary">{{ $row->overtime_minutes }}</td>
                                    <td class="text-right font-weight-bold text-success">${{ number_format($row->overtime_pay, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $row->overtime_status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($row->overtime_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">No overtime records found in the specified range.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    @elseif($type === 'absence')
                        <!-- Absence Report Table -->
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row->date }}</td>
                                    <td class="font-weight-bold text-primary">#{{ $row->employee->id }}</td>
                                    <td>{{ $row->employee->name }}</td>
                                    <td>{{ $row->employee->department ?? 'General' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">Absent</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No absences found in this range. 100% Attendance!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    @elseif($type === 'department')
                        <!-- Department-wise Table -->
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th class="text-center font-12">Worked Mins</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $dept => $rows)
                                    @foreach($rows as $row)
                                    <tr>
                                        @if($loop->first)
                                            <td rowspan="{{ $rows->count() }}" class="font-weight-bold align-middle border-right" style="background-color: #f8fafc;">
                                                {{ $dept }}
                                            </td>
                                        @endif
                                        <td>
                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $row->employee->name }}</h6>
                                            <span class="text-muted font-11">#{{ $row->employee->id }}</span>
                                        </td>
                                        <td>{{ $row->attendance_date }}</td>
                                        <td>{{ $row->attendance_time }}</td>
                                        <td>{{ $row->check_out_time ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $row->worked_minutes ?? 0 }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $row->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $row->status == 1 ? 'On Time' : 'Late' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No department records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                    @else
                        <!-- General Attendance Table (Daily, Weekly, Monthly, Employee, Late) -->
                        <table class="table table-modern table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Department</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th class="text-center">Worked Mins</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row->attendance_date }}</td>
                                    <td class="font-weight-bold text-primary">#{{ $row->employee->id }}</td>
                                    <td>{{ $row->employee->name }}</td>
                                    <td>{{ $row->employee->department ?? 'General' }}</td>
                                    <td>{{ $row->attendance_time }}</td>
                                    <td>{{ $row->check_out_time ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $row->worked_minutes ?? 0 }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $row->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                            {{ $row->status == 1 ? 'On Time' : 'Late' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">No logs match the criteria in the selected date range.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function exportCSV() {
        let form = document.getElementById('reportForm');
        let currentAction = form.action;
        
        // Append export field temporarily
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'export';
        input.value = 'csv';
        form.appendChild(input);
        
        form.submit();
        
        // Cleanup export field so subsequent clicks generate normally
        form.removeChild(input);
    }

    function exportPDF() {
        let form = document.getElementById('reportForm');
        let params = new URLSearchParams(new FormData(form)).toString();
        window.open("{{ route('reports.index') }}?" + params + "&export=pdf", '_blank');
    }
</script>
@endsection
