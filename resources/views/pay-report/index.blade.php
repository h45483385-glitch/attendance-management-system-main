@extends('layouts.master') 

@section('content')
<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    
    .auto-field { 
        background-color: #f8f9fa !important; 
        border: 1px dashed transparent !important; 
        font-weight: 700; 
        color: #495057; 
        pointer-events: none; 
        box-shadow: none !important; 
    }
    .auto-field.editable { 
        background-color: #ffffff !important; 
        border: 1px solid #ced4da !important; 
        pointer-events: auto; 
    }
</style>

<div class="container-fluid mt-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1 text-dark" style="font-weight: 600;">Pay Report Management</h3>
        <div class="text-muted small fw-bold" style="font-size: 13px;">
            <span class="text-primary">Home</span> &gt; Payroll &gt; <span class="text-dark">Pay Report</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
        <div class="card-header text-white py-3" style="background-color: #20c997; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="mb-0 text-white" style="font-size: 16px;"><i class="fas fa-chart-bar me-2"></i> Department-wise Pay Summary (August 2026)</h5>
        </div>
        
        <div class="card-body p-0">
            @foreach($departments as $index => $dept)
                @php
                    $designations = isset($salaryMastersByDept[$dept]) ? $salaryMastersByDept[$dept] : collect();
                    
                    $deptEmployees = $employees->filter(function($emp) use ($designations) {
                        return $designations->contains(function($des) use ($emp) {
                            return strcasecmp(trim($des), trim($emp->position)) === 0;
                        });
                    });
                @endphp

                @if($deptEmployees->count() > 0)
                <div class="border-bottom p-4 d-flex justify-content-between align-items-center bg-white" data-toggle="collapse" data-bs-toggle="collapse" data-target="#dept-{{ $index }}" data-bs-target="#dept-{{ $index }}" style="cursor: pointer;">
                    <div class="d-flex align-items-center">
                        <div class="me-3"><i class="fas fa-briefcase text-primary" style="font-size: 24px;"></i></div>
                        <div>
                            <h5 class="mb-1 text-dark" style="font-weight: 700; font-size: 17px;">{{ $dept }}</h5>
                            <div class="text-muted small"><i class="fas fa-user me-1"></i> {{ $deptEmployees->count() }} Employees</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-end me-4">
                            <div class="fw-bold text-danger mb-1" style="font-size: 14px;">0% Generated</div>
                            <div class="progress" style="height: 5px; width: 120px; background-color: #f8d7da;"><div class="progress-bar bg-danger" style="width: 0%"></div></div>
                        </div>
                        <i class="fas fa-chevron-down text-muted" style="font-size: 18px;"></i>
                    </div>
                </div>
                
                <div id="dept-{{ $index }}" class="collapse @if($index == 0) show @endif" style="background-color: #f8f9fa;">
                    @foreach($deptEmployees as $emp)
                    <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="padding-left: 60px !important;">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div style="width: 35px; height: 35px; background-color: #e0e7ff; color: #5867dd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="color: #5867dd; font-size: 15px;">{{ $emp->name }}</h6>
                                <small class="text-muted">ID: #{{ $emp->id }} | Position: {{ $emp->position }}</small>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-sm text-white rounded-pill px-3 py-2 shadow-sm" style="background-color: #5867dd; font-weight: 600;" onclick="fetchPayData('{{ $emp->id }}')" data-toggle="modal" data-bs-toggle="modal" data-target="#payslipModal" data-bs-target="#payslipModal">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Review & Generate Payslip
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- PAYSLIP REVIEW MODAL -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 850px;"> 
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header text-white" style="background-color: #2c3e50; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold" id="modalPayslipTitle"><i class="fas fa-file-invoice me-2"></i> Payroll Summary</h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="badge bg-success text-white px-3 py-2 mr-2" style="font-size: 12px;"><i class="fas fa-robot mr-1"></i> System Auto-Calculated</span>
                        <span id="calendarBreakdownBadge" class="badge bg-light text-dark border px-3 py-2" style="font-size: 12px;">
                            <i class="fas fa-calendar-alt text-primary mr-1"></i> <span id="summaryText">Loading schedule...</span>
                        </span>
                    </div>
                    <button type="button" id="editToggleBtn" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold" onclick="toggleEditMode()">
                        <i class="fas fa-edit me-1"></i> Enable Manual Override
                    </button>
                </div>

                <form id="payslipForm" action="#" method="POST">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Month & Year</label>
                            <input type="text" class="form-control auto-field" id="month_year" name="month_year" value="{{ \Carbon\Carbon::now()->format('F-Y') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold text-uppercase">Total Working Days</label>
                            <input type="number" class="form-control auto-field calc-trigger" name="total_working_days" id="total_working_days" value="26" readonly>
                        </div>
                    </div>

                    <div class="row mb-4 p-3 rounded" style="background-color: #fff; border: 1px solid #e9ecef;">
                        <div class="col-md-4">
                            <label class="small fw-bold text-uppercase text-success"><i class="fas fa-calendar-check me-1"></i> Present Days</label>
                            <input type="number" class="form-control auto-field calc-trigger text-success" name="total_present" id="total_present" value="0" readonly>
                        </div>
                        <div class="col-md-4 border-start border-end">
                            <label class="small fw-bold text-uppercase text-info"><i class="fas fa-umbrella-beach me-1"></i> Paid Leaves</label>
                            <input type="number" class="form-control auto-field calc-trigger text-info" name="total_leaves" id="total_leaves" value="0" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-uppercase text-danger"><i class="fas fa-calendar-times me-1"></i> Unpaid Absent</label>
                            <input type="number" class="form-control auto-field calc-trigger text-danger" name="total_absent" id="total_absent" value="0" readonly>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold text-uppercase">Base Gross (₹)</label>
                            <input type="number" class="form-control auto-field calc-trigger" id="gross_salary" name="gross_salary" value="0" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="text-muted small fw-bold text-uppercase text-info">Per Day (₹)</label>
                            <input type="number" class="form-control auto-field calc-trigger text-info" id="per_day_salary" name="per_day_salary" value="0" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="text-muted small fw-bold text-uppercase text-danger">Unpaid Loss (₹)</label>
                            <input type="number" class="form-control auto-field calc-trigger text-danger" id="deductions" name="deductions" value="0" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="text-muted small fw-bold text-uppercase text-warning" style="color: #d97706 !important;">Late Penalties (₹)</label>
                            <input type="number" class="form-control auto-field calc-trigger text-warning" id="penalties_field" name="penalties_amount" value="0" readonly style="font-weight: 700;">
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold text-uppercase" style="color: #5867dd;">Final Net (₹)</label>
                            <div class="input-group">
                                <input type="number" class="form-control fw-bold" style="font-size: 16px; background-color: #e0e7ff; color: #5867dd; border-color: #5867dd; pointer-events: none;" id="net_salary" name="net_salary" value="0" readonly>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer bg-white border-top-0 pt-0" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-light fw-bold text-muted px-4" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" style="background-color: #5867dd; border: none;">
                    <i class="fas fa-check-circle me-1"></i> Confirm & Generate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let isEditMode = false;
    
    function toggleEditMode() {
        isEditMode = !isEditMode;
        let fields = document.querySelectorAll('.auto-field');
        let btn = document.getElementById('editToggleBtn');
        
        if(isEditMode) {
            fields.forEach(field => {
                if(field.id !== 'net_salary' && field.id !== 'per_day_salary') {
                    field.classList.add('editable');
                    field.removeAttribute('readonly');
                }
            });
            btn.innerHTML = '<i class="fas fa-lock me-1"></i> Lock Fields';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-danger');
        } else {
            fields.forEach(field => {
                field.classList.remove('editable');
                field.setAttribute('readonly', 'true');
            });
            btn.innerHTML = '<i class="fas fa-edit me-1"></i> Enable Manual Override';
            btn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
        }
    }

    function recalculatePayroll() {
        let gross = parseFloat(document.getElementById('gross_salary').value) || 0;
        let workDays = parseFloat(document.getElementById('total_working_days').value) || 0;
        let present = parseFloat(document.getElementById('total_present').value) || 0;
        let leaves = parseFloat(document.getElementById('total_leaves').value) || 0;
        let absent = parseFloat(document.getElementById('total_absent').value) || 0;
        let penalties = parseFloat(document.getElementById('penalties_field').value) || 0;

        if (workDays > 0) {
            let perDay = gross / workDays;
            let unpaidDeduction = Math.round(perDay * absent);
            let finalNet = Math.max(0, Math.round(gross - unpaidDeduction - penalties));
            
            document.getElementById('per_day_salary').value = perDay.toFixed(2);
            document.getElementById('deductions').value = unpaidDeduction;
            document.getElementById('net_salary').value = finalNet;
        } else {
            document.getElementById('per_day_salary').value = '0.00';
            document.getElementById('deductions').value = '0';
            document.getElementById('net_salary').value = gross;
        }
    }

    // Mathematical Calculation Logic including Per Day Wage & Leave Isolation
    document.querySelectorAll('.calc-trigger').forEach(input => {
        input.addEventListener('input', function(e) {
            let workDays = parseFloat(document.getElementById('total_working_days').value) || 0;
            let present = parseFloat(document.getElementById('total_present').value) || 0;
            let leaves = parseFloat(document.getElementById('total_leaves').value) || 0;
            let absent = parseFloat(document.getElementById('total_absent').value) || 0;

            if (e.target.id === 'total_present' || e.target.id === 'total_leaves' || e.target.id === 'total_working_days') {
                absent = workDays - (present + leaves);
                if (absent < 0) absent = 0;
                document.getElementById('total_absent').value = absent;
            } 
            else if (e.target.id === 'total_absent') {
                present = workDays - (absent + leaves);
                if (present < 0) present = 0;
                document.getElementById('total_present').value = present;
            }

            recalculatePayroll();
        });
    });

    // 🚀 DYNAMIC AJAX FETCH LOGIC WITH REAL-TIME ATTENDANCE & PENALTY ISOLATION
    function fetchPayData(employeeId) {
        fetch(`/pay-report/fetch/${employeeId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('modalPayslipTitle').innerHTML = `<i class="fas fa-file-invoice me-2"></i> Payroll Summary - #${employeeId} (${data.employee_name} - ${data.position})`;
                    
                    if (data.billing_month) {
                        document.getElementById('month_year').value = data.billing_month;
                    }

                    if (data.calendar_days !== undefined) {
                        let overlapNote = data.overlapping_holidays > 0 ? ` (${data.overlapping_holidays} overlapped with weekend)` : '';
                        document.getElementById('summaryText').innerText = 
                            `${data.calendar_days} Days Month: ${data.total_working_days} Workdays | ${data.weekend_days} Weekends | ${data.total_holidays} Holidays${overlapNote}`;
                    }

                    document.getElementById('total_working_days').value = data.total_working_days || 26;
                    document.getElementById('total_present').value = data.present_days !== undefined ? data.present_days : 0;
                    document.getElementById('total_leaves').value = data.leave_days !== undefined ? data.leave_days : 0;
                    document.getElementById('total_absent').value = data.absent_days !== undefined ? data.absent_days : 0;
                    
                    document.getElementById('gross_salary').value = data.base_salary || 0;
                    document.getElementById('penalties_field').value = data.penalties || 0;

                    recalculatePayroll();
                } else {
                    console.error('Error:', data.message);
                    document.getElementById('gross_salary').value = 0;
                    document.getElementById('penalties_field').value = 0;
                    recalculatePayroll();
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
            });
    }
</script>
@endsection