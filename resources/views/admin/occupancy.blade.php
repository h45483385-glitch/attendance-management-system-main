@extends('layouts.master')

@section('css')
<style>
    .occupancy-badge-inside {
        background-color: rgba(34, 197, 94, 0.15);
        color: #16a34a;
        font-weight: 600;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    .occupancy-badge-break {
        background-color: rgba(234, 179, 8, 0.15);
        color: #ca8a04;
        font-weight: 600;
        border: 1px solid rgba(234, 179, 8, 0.3);
    }
    .occupancy-badge-left {
        background-color: rgba(100, 116, 139, 0.12);
        color: #475569;
        font-weight: 600;
        border: 1px solid rgba(100, 116, 139, 0.25);
    }
    .pulse-green {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #22c55e;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulse-green-anim 2s infinite;
    }
    @keyframes pulse-green-anim {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .occupancy-card {
        transition: all 0.2s ease-in-out;
        border-radius: 12px;
    }
    .occupancy-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
</style>
@endsection

@section('content')
<div class="page-title-box mb-4">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <h4 class="page-title text-dark font-weight-bold">
                <i class="ti-direction-alt mr-2 text-primary"></i>Live Office Occupancy Tracker
            </h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Office Occupancy</li>
            </ol>
        </div>
        <div class="col-sm-6 text-right">
            <button class="btn btn-outline-primary btn-sm rounded-pill shadow-sm" onclick="window.location.reload();">
                <i class="ti-reload mr-1"></i> Refresh Status
            </button>
            <a href="{{ route('kiosk.view') }}" target="_blank" class="btn btn-primary btn-sm rounded-pill shadow-sm ml-2">
                <i class="ti-fullscreen mr-1"></i> Open Kiosk
            </a>
        </div>
    </div>
</div>

<!-- Metrics Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 occupancy-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-12 text-uppercase font-weight-bold">Total Staff</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-dark">{{ $metrics['total'] }}</h2>
                </div>
                <div class="avatar-md bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti-user" style="font-size: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 occupancy-card bg-white p-3 border-left-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-success font-12 text-uppercase font-weight-bold d-flex align-items-center gap-1">
                        <span class="pulse-green mr-1"></span> Inside Office
                    </span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-success">{{ $metrics['inside'] }}</h2>
                </div>
                <div class="avatar-md bg-soft-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(34,197,94,0.12);">
                    <i class="ti-home" style="font-size: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 occupancy-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-warning font-12 text-uppercase font-weight-bold">On Break</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-warning">{{ $metrics['on_break'] }}</h2>
                </div>
                <div class="avatar-md bg-soft-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(234,179,8,0.12);">
                    <i class="ti-cup" style="font-size: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0 occupancy-card bg-white p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-12 text-uppercase font-weight-bold">Left / Outside</span>
                    <h2 class="mb-0 mt-1 font-weight-bold text-secondary">{{ $metrics['left'] + $metrics['not_present'] }}</h2>
                </div>
                <div class="avatar-md bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="ti-shift-right" style="font-size: 22px;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs & Lists -->
<div class="row">
    <!-- Currently Inside Office Column -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100 rounded-lg">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 font-weight-bold text-success d-flex align-items-center">
                    <span class="pulse-green mr-2"></span> Currently Inside Office ({{ count($inside) }})
                </h5>
                <span class="badge badge-success px-2 py-1">Active Presences</span>
            </div>
            <div class="card-body p-0">
                @if(count($inside) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light font-12 text-uppercase text-muted">
                            <tr>
                                <th class="pl-4">Employee</th>
                                <th>Department</th>
                                <th>In-Time</th>
                                <th class="text-right pr-4">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inside as $item)
                            <tr>
                                <td class="pl-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs mr-2 font-weight-bold text-primary bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                            {{ strtoupper(substr($item['employee']->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $item['employee']->name }}</div>
                                            <div class="font-11 text-muted">ID: #{{ $item['employee']->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-light border">{{ $item['employee']->department ?: 'General' }}</span></td>
                                <td class="font-weight-bold text-dark">{{ date('h:i A', strtotime($item['last_seen'])) }}</td>
                                <td class="text-right pr-4"><span class="badge badge-soft-success font-12">{{ $item['duration'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="ti-face-smile text-muted" style="font-size: 38px;"></i>
                    <p class="mt-2 mb-0 font-13">No employees currently clocked inside.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Currently Left Office Column -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100 rounded-lg">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 font-weight-bold text-secondary d-flex align-items-center">
                    <i class="ti-arrow-circle-right text-secondary mr-2"></i> Checked-Out / Outside ({{ count($left) }})
                </h5>
                <span class="badge badge-secondary px-2 py-1">Departed / Off-Premises</span>
            </div>
            <div class="card-body p-0">
                @if(count($left) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="bg-light font-12 text-uppercase text-muted">
                            <tr>
                                <th class="pl-4">Employee</th>
                                <th>Department</th>
                                <th>Out-Time</th>
                                <th class="text-right pr-4">Since</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($left as $item)
                            <tr>
                                <td class="pl-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs mr-2 font-weight-bold text-secondary bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                            {{ strtoupper(substr($item['employee']->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $item['employee']->name }}</div>
                                            <div class="font-11 text-muted">ID: #{{ $item['employee']->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-light border">{{ $item['employee']->department ?: 'General' }}</span></td>
                                <td class="text-muted">{{ $item['last_seen'] }}</td>
                                <td class="text-right pr-4"><span class="badge badge-light font-12">{{ $item['duration'] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="ti-info-alt text-muted" style="font-size: 38px;"></i>
                    <p class="mt-2 mb-0 font-13">No employee checkout records yet today.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
