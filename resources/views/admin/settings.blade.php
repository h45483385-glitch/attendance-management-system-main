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
        box-shadow: 0 10px 20px rgba(17, 111, 183, 0.15);
        border-color: var(--primary-blue);
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

<!-- Success Alert (Real Backend Response) -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show font-weight-bold" role="alert">
    <i class="ti-check mr-2"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    
    <!-- 1. Shift Timings Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-target="#shiftModal">
            <div class="setting-icon-wrapper" style="background-color: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                <i class="ti-time"></i>
            </div>
            <h4 class="setting-btn-title">Shift Timings</h4>
            <p class="setting-btn-desc">Configure start/end times and arrival grace periods.</p>
        </div>
    </div>

    <!-- 2. Weekend Configuration Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-target="#weekendModal">
            <div class="setting-icon-wrapper" style="background-color: rgba(34, 197, 94, 0.08); color: var(--primary-green);">
                <i class="ti-calendar"></i>
            </div>
            <h4 class="setting-btn-title">Weekend Config</h4>
            <p class="setting-btn-desc">Set default weekly off days (Saturdays/Sundays).</p>
        </div>
    </div>

    <!-- 3. Leave Policy Button -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-target="#leaveModal">
            <div class="setting-icon-wrapper" style="background-color: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                <i class="ti-medall"></i>
            </div>
            <h4 class="setting-btn-title">Leave Policy</h4>
            <p class="setting-btn-desc">Manage annual casual and medical leave allowances.</p>
        </div>
    </div>

    <!-- 4. Payroll & Salary Master -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" onclick="window.location.href='{{ route('salary.master') }}'">
            <div class="setting-icon-wrapper" style="background-color: var(--primary-blue); color: #ffffff;">
                <i class="ti-wallet"></i>
            </div>
            <h4 class="setting-btn-title" style="color: var(--primary-blue);">Salary Master</h4>
            <p class="setting-btn-desc">Configure role-based pay and payroll matrix.</p>
        </div>
    </div>

    <!-- 5. General & Company Settings -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-target="#companyModal">
            <div class="setting-icon-wrapper" style="background-color: rgba(99, 102, 241, 0.08); color: #6366f1;">
                <i class="ti-briefcase"></i>
            </div>
            <h4 class="setting-btn-title">Company & General</h4>
            <p class="setting-btn-desc">Timezone, currency, fiscal year, and enterprise branding.</p>
        </div>
    </div>

    <!-- 6. Biometrics & Hardware Hub -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" onclick="window.location.href='{{ route('finger_device.index') }}'">
            <div class="setting-icon-wrapper" style="background-color: rgba(14, 165, 233, 0.08); color: #0ea5e9;">
                <i class="ti-desktop"></i>
            </div>
            <h4 class="setting-btn-title">Biometric & Terminals</h4>
            <p class="setting-btn-desc">IP addresses, ports, and real-time terminal connectivity.</p>
        </div>
    </div>

    <!-- 7. Security, Audits & RBAC -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" onclick="window.location.href='{{ route('roles.index') }}'">
            <div class="setting-icon-wrapper" style="background-color: rgba(239, 68, 68, 0.08); color: #ef4444;">
                <i class="ti-shield"></i>
            </div>
            <h4 class="setting-btn-title">Security & RBAC</h4>
            <p class="setting-btn-desc">Roles, permission matrices, and system audit trails.</p>
        </div>
    </div>

    <!-- 8. Notification & Alert Gateway -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" data-toggle="modal" data-target="#notificationModal">
            <div class="setting-icon-wrapper" style="background-color: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                <i class="ti-bell"></i>
            </div>
            <h4 class="setting-btn-title">Notification Gateway</h4>
            <p class="setting-btn-desc">Automated payroll notifications, email, and SMS alerts.</p>
        </div>
    </div>

    <!-- 9. Annual Holiday Calendar -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="setting-btn-card" onclick="window.location.href='{{ route('holidays.index') }}'">
            <div class="setting-icon-wrapper" style="background-color: rgba(16, 185, 129, 0.08); color: #10b981;">
                <i class="ti-calendar"></i>
            </div>
            <h4 class="setting-btn-title" style="color: #10b981;">Holiday Calendar</h4>
            <p class="setting-btn-desc">Interactive annual holidays synced directly to payroll.</p>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- REAL MODALS (CONNECTED TO BACKEND)         -->
<!-- ========================================== -->

<!-- 1. Shift Timings Modal -->
<div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.update') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-time text-primary mr-2"></i> Shift Timings & Grace Period</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift Start</label>
                        <input type="time" name="shift_start" class="form-control" value="{{ \Carbon\Carbon::parse($setting->shift_start)->format('H:i') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Standard Shift End</label>
                        <input type="time" name="shift_end" class="form-control" value="{{ \Carbon\Carbon::parse($setting->shift_end)->format('H:i') }}" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-danger">Arrival Grace Period (Minutes)</label>
                        <p class="text-muted font-13 mb-2">Buffer before being marked 'Late'.</p>
                        <input type="number" name="grace_period" class="form-control" value="{{ $setting->grace_period }}" min="0" max="120" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary">Meal / Break Duration (Minutes)</label>
                        <p class="text-muted font-13 mb-2">Standard deducted company break time.</p>
                        <input type="number" name="break_duration" class="form-control" value="{{ $setting->break_duration ?? 60 }}" min="0" max="180" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Timings</button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- 2. Weekend Configuration Modal -->
<div class="modal fade" id="weekendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.update') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="update_weekend" value="1">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-calendar text-success mr-2"></i> Weekend Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted font-14 mb-3">Select the default weekly off days for the company.</p>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" name="is_saturday_off" class="custom-control-input" id="checkSat" {{ $setting->is_saturday_off ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkSat">Saturday (Holiday)</label>
                </div>
                <div class="custom-control custom-checkbox mb-4">
                    <input type="checkbox" name="is_sunday_off" class="custom-control-input" id="checkSun" {{ $setting->is_sunday_off ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkSun">Sunday (Holiday)</label>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <button type="submit" class="btn btn-success font-weight-bold">Update Calendar</button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- 3. Leave Policy Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.update') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-medall text-warning mr-2"></i> Annual Leave Policy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Casual Leaves (CL) per year</label>
                    <input type="number" name="casual_leaves" class="form-control" value="{{ $setting->casual_leaves }}" min="0" max="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Medical Leaves (ML) per year</label>
                    <input type="number" name="medical_leaves" class="form-control" value="{{ $setting->medical_leaves }}" min="0" max="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Minimum Hours for Full-Day Shift</label>
                    <input type="number" name="min_full_day_hours" class="form-control" value="{{ $setting->min_full_day_hours }}" min="1" max="24" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold">Save Leave Rules</button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- 4. Company & General Settings Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.update') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="update_company" value="1">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-briefcase text-primary mr-2"></i> Company & General Settings</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control font-weight-bold" value="{{ $setting->company_name ?? 'Enterprise AMS Corp' }}" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">System Timezone</label>
                        <select name="timezone" class="form-control form-select">
                            <option value="Asia/Kolkata" {{ ($setting->timezone ?? '') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST +5:30)</option>
                            <option value="UTC" {{ ($setting->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ ($setting->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                            <option value="Europe/London" {{ ($setting->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                            <option value="Asia/Dubai" {{ ($setting->timezone ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Financial Year Start</label>
                        <select name="financial_year_start" class="form-control form-select">
                            <option value="04-01" {{ ($setting->financial_year_start ?? '') == '04-01' ? 'selected' : '' }}>April 1st (Standard)</option>
                            <option value="01-01" {{ ($setting->financial_year_start ?? '') == '01-01' ? 'selected' : '' }}>January 1st (Calendar Year)</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control" value="{{ $setting->currency_symbol ?? '₹' }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Currency Code</label>
                        <input type="text" name="currency_code" class="form-control" value="{{ $setting->currency_code ?? 'INR' }}" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Company Profile</button>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- 5. Notification & Alert Gateway Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.update') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="update_notifications" value="1">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold"><i class="ti-bell text-warning mr-2"></i> Notification & Alert Gateway</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted font-14 mb-3">Configure automated notifications dispatched to employees and administrators.</p>
                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" name="notify_payroll_email" class="custom-control-input" id="checkPayEmail" {{ ($setting->notify_payroll_email ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkPayEmail">Email Payslip PDF on Salary Disbursement</label>
                </div>
                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" name="notify_payroll_sms" class="custom-control-input" id="checkPaySms" {{ ($setting->notify_payroll_sms ?? false) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkPaySms">SMS Credit Alerts on Salary Finalization</label>
                </div>
                <div class="custom-control custom-checkbox mb-3">
                    <input type="checkbox" name="notify_late_alerts" class="custom-control-input" id="checkLateAlerts" {{ ($setting->notify_late_alerts ?? true) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="checkLateAlerts">Daily Anomaly & Repeated Late Alerts to HR</label>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">HR / Notification Central Email</label>
                    <input type="email" name="alert_email" class="form-control" value="{{ $setting->alert_email ?? 'hr@company.com' }}" placeholder="hr@company.com">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @if(auth()->user() && auth()->user()->hasRole('admin'))
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold">Update Notification Rules</button>
                @endif
            </div>
        </form>
    </div>
</div>

@endsection