@extends('layouts.master') <!-- உங்களது சரியான லேஅவுட் பெயரை இங்கே உறுதி செய்யவும் -->

@section('content')
<div class="container-fluid mt-4">
    
    <!-- Header & Breadcrumb -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1 text-dark" style="font-weight: 600;">Pay Report Management</h3>
        <div class="text-muted small fw-bold" style="font-size: 13px;">
            <span class="text-primary">Home</span> &gt; Payroll &gt; <span class="text-dark">Pay Report</span>
        </div>
    </div>

    <!-- Search Box -->
    <div class="row mb-5 mt-4">
        <div class="col-md-8 mx-auto">
            <div class="input-group shadow-sm" style="border-radius: 5px; overflow: hidden;">
                <input type="text" class="form-control border-primary py-2" placeholder="Search Employee ID or Name to generate Pay Report...">
                <button class="btn btn-primary px-4 py-2" type="button" style="background-color: #5867dd; border: none;">
                    <i class="fas fa-search"></i> Search Employee
                </button>
            </div>
        </div>
    </div>

    <!-- Department wise Header & List -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px;">
        <div class="card-header text-white py-3" style="background-color: #20c997; border-top-left-radius: 10px; border-top-right-radius: 10px;">
            <h5 class="mb-0 text-white" style="font-size: 16px;"><i class="fas fa-chart-bar me-2"></i> Department-wise Pay Summary (August 2026)</h5>
        </div>
        
        <div class="card-body p-0">
            
            <!-- Accordion Header -->
            <div class="border-bottom p-4 d-flex justify-content-between align-items-center bg-white" data-toggle="collapse" data-bs-toggle="collapse" data-target="#dept-admin" data-bs-target="#dept-admin" style="cursor: pointer;">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-briefcase text-primary" style="font-size: 24px; color: #5867dd !important;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 text-dark" style="font-weight: 700; font-size: 17px;">Administrator</h5>
                        <div class="text-muted small"><i class="fas fa-user me-1"></i> 1 Employees</div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="text-end me-4">
                        <div class="fw-bold text-danger mb-1" style="font-size: 14px;">0% Generated</div>
                        <div class="progress" style="height: 5px; width: 120px; background-color: #f8d7da;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-muted" style="font-size: 18px;"></i>
                </div>
            </div>
            
            <!-- Expanded Content -->
            <div id="dept-admin" class="collapse show" style="background-color: #f8f9fa;">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="padding-left: 60px !important;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div style="width: 35px; height: 35px; background-color: #e0e7ff; color: #5867dd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #5867dd; font-size: 15px;">Admin User</h6>
                            <small class="text-muted">ID: #111</small>
                        </div>
                    </div>
                    <div>
                        <!-- Modal ஐ ஓபன் செய்வதற்கான பட்டன் -->
                        <button class="btn btn-sm text-white rounded-pill px-3 py-2 shadow-sm" style="background-color: #5867dd; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#payslipModal" data-toggle="modal" data-target="#payslipModal">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Generate Payslip
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- PAYSLIP GENERATION MODAL (POP-UP FORM)         -->
<!-- ============================================== -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header text-white" style="background-color: #5867dd; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title" id="payslipModalLabel"><i class="fas fa-file-invoice-dollar me-2"></i> Generate Payslip - Admin User (#111)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <form action="#" method="POST">
                    <!-- CSRF Token (Backend இணைக்கும்போது பயன்படும்) -->
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Month & Year</label>
                            <input type="text" class="form-control" name="month_year" value="August-2026" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Total Working Days</label>
                            <input type="number" class="form-control" name="total_working_days" value="26" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Total Present Days</label>
                            <input type="number" class="form-control border-success" name="total_present" value="24" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-danger">Total Absent Days</label>
                            <input type="number" class="form-control border-danger" name="total_absent" value="2" required>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gross Salary (₹)</label>
                            <input type="number" class="form-control" id="gross_salary" name="gross_salary" value="30000" onkeyup="calculateNetSalary()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-danger">Deductions (₹) <small>(Absent/Late)</small></label>
                            <input type="number" class="form-control border-danger" id="deductions" name="deductions" value="2300" onkeyup="calculateNetSalary()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-success">Net Salary (₹)</label>
                            <input type="number" class="form-control bg-success text-white fw-bold" id="net_salary" name="net_salary" readonly>
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary px-4" style="background-color: #5867dd;"><i class="fas fa-save me-1"></i> Save & Generate</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Auto Calculation -->
<script>
    // பேஜ் லோட் ஆனவுடன் ஒரு முறை கால்குலேட் செய்ய
    document.addEventListener("DOMContentLoaded", function() {
        calculateNetSalary();
    });

    // Net Salary கணக்கிடும் லாஜிக்
    function calculateNetSalary() {
        let gross = document.getElementById('gross_salary').value;
        let deductions = document.getElementById('deductions').value;
        
        // காலியாக இருந்தால் 0 என்று எடுத்துக்கொள்ளும்
        gross = gross ? parseFloat(gross) : 0;
        deductions = deductions ? parseFloat(deductions) : 0;
        
        let net = gross - deductions;
        
        // Net Salary பாக்சில் விடையைக் காட்டவும்
        document.getElementById('net_salary').value = net;
    }
</script>
@endsection