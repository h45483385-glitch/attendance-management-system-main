@extends('layouts.master')

@section('content')
<div class="page-title-box mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h4 class="page-title text-dark font-weight-bold"><i class="fas fa-tools mr-2 text-primary"></i>IT Support Dashboard</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('welcome') }}">Home</a></li>
                <li class="breadcrumb-item active">IT Support</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: rgba(17,111,183,0.1); margin: 0 auto;">
                    <i class="ti-server text-primary" style="font-size: 28px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Biometric Devices</h5>
                <p class="text-muted mb-3 font-12">Manage and monitor ZKTeco biometric terminals.</p>
                <a href="{{ route('finger_device.index') }}" class="btn btn-primary btn-sm btn-block rounded-pill">
                    Manage Devices
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-info rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: rgba(23,162,184,0.1); margin: 0 auto;">
                    <i class="ti-video-camera text-info" style="font-size: 28px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">CCTV & Cameras</h5>
                <p class="text-muted mb-3 font-12">Configure live surveillance camera streams.</p>
                <a href="{{ route('cameras.index') }}" class="btn btn-info btn-sm btn-block rounded-pill">
                    Manage Cameras
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: rgba(255,193,7,0.1); margin: 0 auto;">
                    <i class="ti-shield text-warning" style="font-size: 28px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Security Dashboard</h5>
                <p class="text-muted mb-3 font-12">Live threat analysis and security statistics.</p>
                <a href="{{ route('security.dashboard') }}" class="btn btn-warning btn-sm btn-block rounded-pill">
                    Security Center
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-body text-center p-4">
                <div class="avatar-lg mx-auto mb-3 bg-soft-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 65px; height: 65px; background: rgba(108,117,125,0.1); margin: 0 auto;">
                    <i class="ti-receipt text-secondary" style="font-size: 28px;"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">Audit Logs</h5>
                <p class="text-muted mb-3 font-12">Review user activity and system audit trails.</p>
                <a href="{{ route('audit_logs.index') }}" class="btn btn-secondary btn-sm btn-block rounded-pill">
                    View Logs
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
