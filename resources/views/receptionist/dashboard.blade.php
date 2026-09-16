@extends('layouts.master')

@section('content')
<div class="page-title-box mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h4 class="page-title text-dark font-weight-bold"><i class="fas fa-concierge-bell mr-2 text-primary"></i>Receptionist Dashboard</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Home</a></li>
                <li class="breadcrumb-item active">Receptionist</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(17,111,183,0.1); margin: 0 auto;">
                    <i class="dripicons-user-group text-primary" style="font-size: 32px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Visitor Check-In</h5>
                <p class="text-muted mb-3 font-13">Register arriving visitors, issue badges, and assign hosts.</p>
                <a href="{{ route('visitor.checkin') }}" class="btn btn-primary btn-block rounded-pill">
                    <i class="ti-plus mr-1"></i> New Visitor Check-In
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-success rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(34,197,94,0.1); margin: 0 auto;">
                    <i class="ti-agenda text-success" style="font-size: 32px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Visitor Logs</h5>
                <p class="text-muted mb-3 font-13">View currently checked-in visitors, check-out status, and history.</p>
                <a href="{{ route('admin.visitor_index') }}" class="btn btn-success btn-block rounded-pill">
                    <i class="ti-list mr-1"></i> View Visitor Logs
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-info rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: rgba(23,162,184,0.1); margin: 0 auto;">
                    <i class="ti-fullscreen text-info" style="font-size: 32px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Attendance Kiosk</h5>
                <p class="text-muted mb-3 font-13">Launch the live facial recognition attendance kiosk display.</p>
                <a href="{{ route('kiosk.view') }}" target="_blank" class="btn btn-info btn-block rounded-pill">
                    <i class="ti-fullscreen mr-1"></i> Open Kiosk
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
