@extends('layouts.master')

@section('content')
<style>
    /* Tab Buttons Design */
    .nav-pills .nav-link {
        border-radius: 5px;
        padding: 10px 20px;
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s;
    }
    .nav-pills .nav-link:hover {
        background-color: #e2e8f0;
    }
    .nav-pills .nav-link.active {
        background-color: #e0e7ff !important;
        color: #5867dd !important;
        border-color: #e0e7ff !important;
    }
    .nav-pills .nav-link.text-danger.active {
        background-color: #fde8e8 !important;
        color: #e3342f !important;
        border-color: #fde8e8 !important;
    }
    .nav-pills .nav-link.text-success.active {
        background-color: #def7ec !important;
        color: #38c172 !important;
        border-color: #def7ec !important;
    }
</style>

<div class="container-fluid mt-4">
    
    <div class="mb-4">
        <h3 class="fw-bold mb-1 text-dark">Time Exceptions & Resolutions</h3>
        <div class="text-muted small fw-bold">
            <span class="text-primary">Home</span> &gt; <span class="text-dark">Time Exceptions</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 10px;">
        <div class="card-body p-4">
            
            <!-- TABS HEADER -->
            <ul class="nav nav-pills mb-4 pb-3 border-bottom" id="timeExceptionsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="late-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#late" data-bs-target="#late" type="button" role="tab">
                        <i class="fas fa-clock me-1"></i> Late Arrivals
                    </button>
                </li>
                <li class="nav-item ms-3" role="presentation">
                    <button class="nav-link text-danger fw-bold" id="missed-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#missed" data-bs-target="#missed" type="button" role="tab">
                        <i class="fas fa-exclamation-triangle me-1"></i> Missed Punches / Defenses
                    </button>
                </li>
                <li class="nav-item ms-3" role="presentation">
                    <button class="nav-link text-success fw-bold" id="overtime-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#overtime" data-bs-target="#overtime" type="button" role="tab">
                        <i class="fas fa-business-time me-1"></i> Overtime Approvals
                    </button>
                </li>
            </ul>

            <!-- TABS CONTENT -->
            <div class="tab-content" id="timeExceptionsTabContent">
                
                <!-- 1. LATE ARRIVALS TAB -->
                <div class="tab-pane fade show active" id="late" role="tabpanel">
                    <div class="table-responsive border rounded">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Date</th><th>Employee ID</th><th>Name</th><th>Time In</th><th>Late By</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center p-5 text-muted">
                                        <i class="far fa-smile" style="font-size: 30px;"></i>
                                        <p class="mt-2 mb-0">No late arrivals logged! Everyone is on time.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. MISSED PUNCHES TAB -->
                <div class="tab-pane fade" id="missed" role="tabpanel">
                    <div class="alert alert-warning mb-3" style="background-color: #fff9e6; border-color: #ffecb5; color: #a97e00;">
                        <i class="fas fa-info-circle me-1"></i> Employees type their defense reasons in their portal. You can review and approve them here to update their attendance logs.
                    </div>
                    <div class="table-responsive border rounded">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th>Date</th><th>Employee Name</th><th>Recorded Time-In</th><th>Employee Defense / Reason</th><th>Approval Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="align-middle">Aug 14, 2026</td>
                                    <td class="align-middle fw-bold text-primary">Hariram (ID: #112)</td>
                                    <td class="align-middle">09:15 AM</td>
                                    <td class="align-middle">
                                        <div class="fst-italic text-muted">"I was in the server room deployment meeting from 5 PM to 7 PM and forgot to punch out."</div>
                                        <span class="badge bg-warning text-dark mt-1">Requested Time Out: 07:00 PM</span>
                                    </td>
                                    <td class="align-middle">
                                        <button class="btn btn-sm btn-success px-3 me-1"><i class="fas fa-check"></i> Approve</button>
                                        <button class="btn btn-sm btn-danger px-3"><i class="fas fa-times"></i> Reject</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. OVERTIME APPROVALS TAB -->
                <div class="tab-pane fade" id="overtime" role="tabpanel">
                    <div class="card border-0 shadow-none">
                        <div class="card-header text-white py-3 rounded-top" style="background-color: #5867dd;">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-business-time me-2"></i> Pending Overtime Requests</h6>
                        </div>
                        <div class="card-body p-0 border border-top-0 rounded-bottom">
                            
                            <!-- Dynamic Loop for Overtime Data -->
                            @if(isset($pendingOvertimes) && count($pendingOvertimes) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light text-muted">
                                            <tr>
                                                <th>Date</th>
                                                <th>Employee ID</th>
                                                <th>Overtime Mins</th>
                                                <th>OT Amount (₹)</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingOvertimes as $log)
                                            <tr>
                                                <td class="align-middle fw-bold">{{ \Carbon\Carbon::parse($log->date)->format('d M, Y') }}</td>
                                                <td class="align-middle">#{{ $log->employee_id }}</td>
                                                <td class="align-middle text-danger fw-bold">+{{ $log->overtime_minutes }} Mins</td>
                                                <td class="align-middle text-success fw-bold">₹{{ $log->overtime_pay }}</td>
                                                <td class="align-middle">
                                                    <button class="btn btn-sm btn-primary px-3 rounded-pill" data-toggle="modal" data-bs-toggle="modal" data-target="#reviewModal{{ $log->id }}" data-bs-target="#reviewModal{{ $log->id }}">
                                                        Review & Act
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- REVIEW MODAL -->
                                            <div class="modal fade" id="reviewModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="border-radius: 12px;">
                                                        <div class="modal-header bg-light">
                                                            <h5 class="modal-title fw-bold text-dark">Review Overtime</h5>
                                                            <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                                <span>Employee ID: <strong>#{{ $log->employee_id }}</strong></span>
                                                                <span>Date: <strong>{{ \Carbon\Carbon::parse($log->date)->format('d M, Y') }}</strong></span>
                                                            </div>
                                                            <div class="alert alert-warning">
                                                                This employee worked <strong>{{ $log->overtime_minutes }} mins</strong> extra. 
                                                                If approved, <strong>₹{{ $log->overtime_pay }}</strong> will be added to their daily pay.
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Admin Remarks / Chat Note (Required)</label>
                                                                <textarea class="form-control" id="remarks_{{ $log->id }}" rows="3" placeholder="Type reason for approval or rejection here..." required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer justify-content-between bg-light">
                                                            <form action="{{ route('overtime.reject', $log->id) }}" method="POST" onsubmit="document.getElementById('reject_remarks_{{ $log->id }}').value = document.getElementById('remarks_{{ $log->id }}').value;">
                                                                @csrf
                                                                <input type="hidden" name="admin_remarks" id="reject_remarks_{{ $log->id }}">
                                                                <button type="submit" class="btn btn-danger px-4 rounded-pill"><i class="fas fa-times me-1"></i> Reject</button>
                                                            </form>
                                                            <form action="{{ route('overtime.approve', $log->id) }}" method="POST" onsubmit="document.getElementById('approve_remarks_{{ $log->id }}').value = document.getElementById('remarks_{{ $log->id }}').value;">
                                                                @csrf
                                                                <input type="hidden" name="admin_remarks" id="approve_remarks_{{ $log->id }}">
                                                                <button type="submit" class="btn btn-success px-4 rounded-pill"><i class="fas fa-check me-1"></i> Approve</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-check-circle" style="font-size: 40px; color: #38c172;"></i>
                                    <h5 class="mt-3">All Caught Up!</h5>
                                    <p class="mb-0">No pending overtime requests for today.</p>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection