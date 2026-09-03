@extends('layouts.master')

@section('css')
<style>
    .saas-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
        border: 1px solid #f1f5f9; 
    }
    
    .avatar-circle { 
        width: 44px; 
        height: 44px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 600; 
        font-size: 16px; 
        color: white; 
        background: linear-gradient(135deg, #116fb7, #22C55E); 
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Security Center</h4>
    <p class="text-secondary font-13 mb-0">Overview of administrative access, device states, camera feeds, and brute-force blockouts.</p>
</div>
@endsection

@section('content')
<div class="row">
    <!-- METRICS ROW -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="avatar-circle mr-3" style="background: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                    <i class="ti-user"></i>
                </div>
                <div>
                    <h4 class="m-0 font-weight-bold text-dark">{{ $totalUsers }}</h4>
                    <span class="text-muted font-12">Total Admin Users</span>
                    <span class="text-success font-11 d-block mt-1"><i class="ti-arrow-up mr-1"></i> {{ $activeUsers }} Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="avatar-circle mr-3" style="background: rgba(34, 197, 94, 0.08); color: #22C55E;">
                    <i class="ti-server"></i>
                </div>
                <div>
                    <h4 class="m-0 font-weight-bold text-dark">{{ $totalDevices }}</h4>
                    <span class="text-muted font-12">Registered Terminals</span>
                    <span class="text-danger font-11 d-block mt-1"><i class="ti-lock mr-1"></i> {{ $blockedDevices }} Blocked</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="avatar-circle mr-3" style="background: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                    <i class="ti-video-camera"></i>
                </div>
                <div>
                    <h4 class="m-0 font-weight-bold text-dark">{{ $totalCameras }}</h4>
                    <span class="text-muted font-12">ONVIF/RTSP Feeds</span>
                    <span class="text-warning font-11 d-block mt-1"><i class="ti-alert mr-1"></i> {{ $disconnectedCameras }} Disconnected</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="avatar-circle mr-3" style="background: rgba(239, 68, 68, 0.08); color: #ef4444;">
                    <i class="ti-lock"></i>
                </div>
                <div>
                    <h4 class="m-0 font-weight-bold text-dark">{{ $auditFailedLogins }}</h4>
                    <span class="text-muted font-12">Failed Login Alerts</span>
                    <span class="text-danger font-11 d-block mt-1"><i class="ti-alert mr-1"></i> {{ $lockoutsCount }} Lockouts</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- RECENT SECURITY ALERTS -->
    <div class="col-md-12">
        <div class="card saas-card">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-alert text-danger mr-2"></i> Recent Security Incidents & Policies Changes</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Incident Category</th>
                                <th>Source Host / IP</th>
                                <th>Description Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEvents as $event)
                            <tr style="border-left: 3px solid {{ $event->action === 'LOGIN_LOCKOUT' ? '#ef4444' : '#f59e0b' }};">
                                <td>
                                    <span class="text-dark font-weight-bold font-12">{{ \Carbon\Carbon::parse($event->created_at)->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $event->action === 'LOGIN_LOCKOUT' ? 'badge-danger' : 'badge-warning' }}">{{ $event->action }}</span>
                                </td>
                                <td>
                                    <span class="font-weight-bold font-12">{{ $event->ip_address }}</span>
                                </td>
                                <td>
                                    <span class="text-secondary font-13">{{ $event->description }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No critical security alerts or lockout incidents recorded recently.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
