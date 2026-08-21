@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">System Settings</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin" class="text-primary font-weight-bold">Home</a></li>
        <li class="breadcrumb-item active text-dark font-weight-bold">Settings</li>
    </ol>
</div>
@endsection

@section('content')
<style>
    /* Uniform Professional Buttons/Cards CSS */
    .setting-btn-card { 
        background: #ffffff;
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 30px 20px;
        text-decoration: none !important;
    }
    .setting-btn-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(88, 103, 221, 0.15);
        border-color: #5867dd;
    }
    .setting-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 15px;
    }
    .setting-btn-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .setting-btn-desc {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }
    
    /* Modal Styling */
    .modal-content { border-radius: 12px; border: none; }
    .modal-header { border-top-left-radius: 12px; border-top-right-radius: 12px; border-bottom: 1px solid #e2e8f0; }
    .form-label { font-weight: 600; color: #475569; }
</style>

<div class="row">
    
    <!-- 1. Shift Timings Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-bs-toggle="modal" data-target="#shiftModal" data-bs-target="#shiftModal">
            <div class="setting-icon-wrapper" style="background-color: #e0e7ff; color: #5867dd;">
                <i class="ti-time"></i>
            </div>
            <h4 class="setting-btn-title">Shift Timings</h4>
            <p class="setting-btn-desc">Configure start/end times and arrival grace periods.</p>
        </div>
    </div>

    <!-- 2. Weekend Configuration Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-bs-toggle="modal" data-target="#weekendModal" data-bs-target="#weekendModal">
            <div class="setting-icon-wrapper" style="background-color: #def7ec; color: #20c997;">
                <i class="ti-calendar"></i>
            </div>
            <h4 class="setting-btn-title">Weekend Config</h4>
            <p class="setting-btn-desc">Set default weekly off days (Saturdays/Sundays).</p>
        </div>
    </div>

    <!-- 3. Leave Policy Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-bs-toggle="modal" data-target="#leaveModal" data-bs-target="#leaveModal">
            <div class="setting-icon-wrapper" style="background-color: #fef3c7; color: #d97706;">
                <i class="ti-medall"></i>
            </div>
            <h4 class="setting-btn-title">Leave Policy</h4>
            <p class="setting-btn-desc">Manage annual casual and medical leave allowances.</p>
        </div>
    </div>

    <!-- 4. Payroll & Salary Master (Direct Link Button) -->
    <div class="col-md-6 col-lg-3 mb-4">
        <!-- Wraps the card in an anchor tag for direct redirection -->
        <a href="{{ route('salary.master') }}" style="text-decoration: none; display: block; height: 100%;">
            <div class="setting-btn-card" style="border: 1px solid #e0e7ff;">
                <div class="setting-icon-wrapper" style="background-color: #5867dd; color: #ffffff;">
                    <i class="ti-wallet"></i>
                </div>
                <h4 class="setting-btn-title" style="color: #5867dd;">Salary Master</h4>
                <p class="setting-btn-desc">Configure role-based pay and payroll matrix.</p>
            </div>
        </a>
    </div>

</div>

<!-- ========================================== -->
<!-- MODALS (POPUPS) FOR THE SETTINGS           -->
<!-- ========================================== -->

<!-- 1. Shift Timings Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-time text-primary mr-2"></i> Shift Timings & Grace Period</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift Start</label>
                        <input type="time" class="form-control" value="09:30">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift End</label>
                        <input type="time" class="form-control" value="18:30">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label text-danger">Arrival Grace Period (Minutes)</label>
                    <p class="text-muted font-13 mb-2">Buffer time before an employee is marked as 'Late'.</p>
                    <input type="number" class="form-control" value="10">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary font-weight-bold" onclick="showToastAndClose('shiftModal')">Save Timings</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Weekend Configuration Modal -->
<div class="modal fade" id="weekendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-calendar text-success mr-2"></i> Weekend Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted font-14 mb-3">Select the default weekly off days for the company.</p>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="checkSat">
                    <label class="custom-control-label font-weight-bold" for="checkSat">Saturday (Holiday)</label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                    <input type="checkbox" class="custom-control-input" id="checkSun" checked>
                    <label class="custom-control-label font-weight-bold" for="checkSun">Sunday (Holiday)</label>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success font-weight-bold" onclick="showToastAndClose('weekendModal')">Update Calendar</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Leave Policy Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-medall text-warning mr-2"></i> Annual Leave Policy</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Casual Leaves (CL) per year</label>
                    <input type="number" class="form-control" value="12">
                </div>
                <div class="mb-3">
                    <label class="form-label">Medical Leaves (ML) per year</label>
                    <input type="number" class="form-control" value="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Minimum Hours for Full-Day</label>
                    <input type="number" class="form-control" value="8" placeholder="e.g., 8 Hours">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning text-dark font-weight-bold" onclick="showToastAndClose('leaveModal')">Save Leave Rules</button>
            </div>
        </div>
    </div>
</div>

<!-- Simple Toast Notification -->
<div id="success-toast" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
    <strong><i class="ti-check mr-1"></i> SUCCESS!</strong> Settings saved successfully.
    <button type="button" class="close" onclick="document.getElementById('success-toast').style.display='none'">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<script>
    // Saves data, hides the specific modal, and shows toast
    function showToastAndClose(modalId) {
        // Hiding the modal using jQuery (Bootstrap standard)
        $('#' + modalId).modal('hide');
        
        // Showing Toast
        const toast = document.getElementById('success-toast');
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }
</script>
@endsection