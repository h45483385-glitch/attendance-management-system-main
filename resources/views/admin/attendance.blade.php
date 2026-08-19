@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Employee Profile & Calendar</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item"><a href="/sheet-report" class="text-primary font-weight-bold">Master Attendance</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Calendar View</li>
    </ol>
</div>
@endsection

@section('content')

<style>
    .profile-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px; text-align: center; border: none; }
    .profile-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #e0e7ff; margin-bottom: 15px; }
    .attendance-badge { font-size: 15px; font-weight: 700; padding: 10px 20px; border-radius: 30px; background: #d1fae5; color: #059669; display: inline-block; margin-top: 15px; }
    .ot-card { background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 16px; padding: 25px; margin-top: 20px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    
    /* --- CLEAN CALENDAR GRID CSS --- */
    .calendar-container { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .calendar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(115px, 1fr)); gap: 15px; margin-top: 20px; }
    
    .day-card { 
        background: #ffffff; 
        border: 1px solid #e2e8f0; 
        border-radius: 12px; 
        padding: 15px 10px; 
        text-align: center; 
        transition: transform 0.2s, box-shadow 0.2s; 
        color: #1e293b; 
    }
    .day-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
    
    .day-card .day-name { font-size: 12px; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 5px; }
    .day-card .date-num { font-size: 28px; font-weight: 800; line-height: 1; margin-bottom: 12px; color: #0f172a; }
    .day-card .status-text { font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 5px 10px; border-radius: 20px; display: inline-block; }
    .day-card .hours-logged { font-size: 13px; font-weight: 600; margin-top: 12px; display: block; border-top: 1px solid #f1f5f9; padding-top: 10px; color: #475569; }

    /* PROFESSIONAL BADGE COLORS & TOP BORDER (UPDATED WITH NEW LOGIC) */
    .status-completed { border-top: 4px solid #10b981; }
    .status-completed .status-text { background: #d1fae5; color: #059669; }

    .status-progress { border-top: 4px solid #f59e0b; }
    .status-progress .status-text { background: #fef3c7; color: #d97706; }

    .status-absent { border-top: 4px solid #ef4444; }
    .status-absent .status-text { background: #fee2e2; color: #dc2626; }

    .status-break { border-top: 4px solid #3b82f6; }
    .status-break .status-text { background: #dbeafe; color: #2563eb; }

    .status-holiday { border-top: 4px solid #94a3b8; }
    .status-holiday .status-text { background: #f1f5f9; color: #475569; }

    /* ACTION REQUIRED BADGE */
    .action-badge {
        background: #fff1f2; 
        color: #e11d48; 
        border: 1px dashed #fda4af; 
        padding: 4px 10px; 
        border-radius: 6px; 
        font-size: 11px; 
        font-weight: 700; 
        letter-spacing: 0.3px;
        display: inline-block;
    }
</style>

<div class="row mt-3">
    
    @if(request('employee_query') && !isset($employee))
        <div class="col-12 text-center mt-5">
            <h3 class="text-danger">Result Not Found!</h3>
            <a href="/sheet-report" class="btn btn-primary mt-3">Back to Master Attendance</a>
        </div>
    @elseif(!isset($employee))
        <div class="col-12 text-center mt-5">
            <h3 class="text-muted">Search an employee or select from Master Attendance</h3>
            <form action="/attendance" method="GET" class="mt-3">
                <input type="text" name="employee_query" class="form-control d-inline w-25" placeholder="Employee ID...">
                <button type="submit" class="btn btn-primary">Go</button>
            </form>
        </div>
        
    @elseif(isset($employee))
        <!-- Left Sidebar: Profile Details -->
        <div class="col-lg-4 col-md-5">
            <div class="profile-card">
                <img src="{{ $employee->image_path ? asset($employee->image_path) : 'https://ui-avatars.com/api/?name='.urlencode($employee->name).'&background=5867dd&color=fff&size=128' }}" alt="Profile" class="profile-avatar">
                <h3 class="font-weight-bold text-dark mb-1">{{ $employee->name }}</h3>
                <p class="text-muted mb-3">{{ $employee->position }} | ID: #{{ $employee->id }}</p>
                
                <div class="attendance-badge">
                    <i class="ti-stats-up mr-1"></i> {{ $attendancePercentage ?? '0' }}% Present This Year
                </div>
                
                <hr class="my-4">
                
                <div class="text-left">
                    <!-- EMAIL SECTION -->
                    <div class="mb-3 d-flex align-items-center">
                        <i class="ti-email text-primary mr-3" style="font-size: 18px;"></i> 
                        @if(!empty($employee->email))
                            <span class="font-weight-bold">{{ $employee->email }}</span>
                        @else
                            <span class="action-badge" title="Please contact your Manager or HR to update your email.">
                                <i class="ti-info-alt mr-1"></i> Update via TL/Manager
                            </span>
                        @endif
                    </div>
                    
                    <!-- MOBILE SECTION -->
                    <div class="mb-2 d-flex align-items-center">
                        <i class="ti-mobile text-primary mr-3" style="font-size: 18px;"></i> 
                        @if(!empty($employee->mobile))
                            <span class="font-weight-bold">{{ $employee->mobile }}</span>
                        @else
                            <span class="action-badge" title="Please contact your Manager or HR to update your mobile number.">
                                <i class="ti-info-alt mr-1"></i> Update via TL/Manager
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ot-card">
                <div>
                    <h5 class="font-weight-bold mb-1 text-white">Overtime Wallet</h5>
                    <p class="text-light mb-0" style="opacity: 0.8; font-size: 13px;">View extra hours</p>
                </div>
                <a href="/overtime?employee_id={{ $employee->id }}" class="btn btn-light text-dark font-weight-bold rounded-pill px-4">View</a>
            </div>
        </div>

        <!-- Right Side: VISUAL CALENDAR -->
        <div class="col-lg-8 col-md-7">
            <div class="calendar-container">
                <h4 class="font-weight-bold text-dark mb-4">Attendance Calendar Tracker</h4>
                
                @if(isset($monthlyLogs) && count($monthlyLogs) > 0)
                    @foreach($monthlyLogs as $month => $logs)
                        <h5 class="text-primary mt-4 mb-3 border-bottom pb-2">{{ $month }}</h5>
                        
                        <div class="calendar-grid">
                            @foreach($logs as $log)
                                @php
                                    $bgClass = 'status-holiday'; 
                                    
                                    if($log->status == 'Present') {
                                        $bgClass = 'status-completed'; 
                                    } elseif($log->status == 'In Progress' || $log->status == 'Partial Shift') {
                                        $bgClass = 'status-progress'; 
                                    } elseif($log->status == 'Break Time') {
                                        $bgClass = 'status-break'; 
                                    } elseif($log->status == 'Absent' || $log->status == 'Missing Punch') {
                                        $bgClass = 'status-absent'; 
                                    }
                                @endphp
                                
                                <div class="day-card {{ $bgClass }}">
                                    <div class="day-name">{{ \Carbon\Carbon::parse($log->date)->format('D') }}</div>
                                    <div class="date-num">{{ \Carbon\Carbon::parse($log->date)->format('d') }}</div>
                                    <div class="status-text">{{ $log->status }}</div>
                                    <div class="hours-logged"><i class="ti-time"></i> {{ $log->net_hours ?? '0h' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="ti-calendar text-muted" style="font-size: 50px;"></i>
                        <p class="text-muted mt-3">No logs found for this employee yet.</p>
                    </div>
                @endif
                
            </div>
        </div>
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Target the input fields by their IDs
    const grossSalaryInput = document.getElementById('gross_salary');
    const totalWorkingDaysInput = document.getElementById('total_working_days');
    const totalAbsentDaysInput = document.getElementById('total_absent_days');
    const deductionsInput = document.getElementById('deductions');
    const netSalaryInput = document.getElementById('net_salary');

    // 2. Create the calculation function
    function calculatePayslip() {
        // Parse the values, default to 0 if empty
        const grossSalary = parseFloat(grossSalaryInput.value) || 0;
        const totalWorkingDays = parseFloat(totalWorkingDaysInput.value) || 0;
        const totalAbsentDays = parseFloat(totalAbsentDaysInput.value) || 0;

        if (totalWorkingDays > 0) {
            // Formula: (Gross / Total Working Days) * Absent Days
            const perDaySalary = grossSalary / totalWorkingDays;
            const deductions = perDaySalary * totalAbsentDays;
            const netSalary = grossSalary - deductions;

            // Update the UI and round to the nearest whole number
            deductionsInput.value = Math.round(deductions);
            netSalaryInput.value = Math.round(netSalary);
        } else {
            // Reset if working days are 0 or empty to avoid Infinity/NaN errors
            deductionsInput.value = 0;
            netSalaryInput.value = grossSalary;
        }
    }

    // 3. Listen for any typing or clicking changes on the input fields
    grossSalaryInput.addEventListener('input', calculatePayslip);
    totalWorkingDaysInput.addEventListener('input', calculatePayslip);
    totalAbsentDaysInput.addEventListener('input', calculatePayslip);
});
</script>
@endsection