@extends('layouts.master')

@section('css')
<style>
    /* Compact statistics and charts styling */
    .metric-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
    }
    
    .activity-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 12px;
    }
    
    .employee-row-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--primary-blue);
        font-size: 12px;
    }

    /* Live Hardware Indicator Dot */
    .status-dot-pulse {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .status-dot-online {
        background-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
    }
    .status-dot-offline {
        background-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
    }
    .device-table td {
        vertical-align: middle;
        padding: 12px 16px !important;
        font-size: 13px;
    }
    .device-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 12px;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title text-dark font-weight-bold">Dashboard</h4>
    <p class="text-secondary font-13 mb-0">Welcome back, Admin! Here's what's happening today.</p>
</div>
@endsection

@section('content')


<!-- 1. COMPREHENSIVE LIVE ATTENDANCE & BIOMETRIC METRICS ROW -->
<div class="row mt-3">
    <!-- Metric 1: Total Staff -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Total Staff</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($totalEmployees) }}</h3>
                    <span class="badge badge-primary mt-2"><i class="ti-id-badge mr-1"></i> Total Registered</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(17, 111, 183, 0.08); color: var(--primary-blue);">
                    <i class="ti-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 2: Active Staff -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Active Staff</span>
                    <h3 class="m-0 font-weight-bold text-success">{{ number_format($activeEmployees) }}</h3>
                    <span class="badge badge-success mt-2"><i class="ti-check mr-1"></i> Operational</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(34, 197, 94, 0.08); color: var(--primary-green);">
                    <i class="ti-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 3: Deactivated Staff -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Deactivated Staff</span>
                    <h3 class="m-0 font-weight-bold text-secondary">{{ number_format($deactivatedEmployees) }}</h3>
                    <span class="badge badge-secondary mt-2"><i class="ti-na mr-1"></i> Inactive Roster</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(100, 116, 139, 0.08); color: #64748b;">
                    <i class="ti-lock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric 4: Face Enrolled Staff -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Face Enrolled</span>
                    <h3 class="m-0 font-weight-bold text-dark">{{ number_format($faceEnrolledCount) }}</h3>
                    <span class="badge badge-info mt-2"><i class="ti-camera mr-1"></i> Biometric Facial</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(14, 165, 233, 0.08); color: #0ea5e9;">
                    <i class="ti-eye"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. DAILY ATTENDANCE & LIVE BIOMETRIC SUMMARY -->
<div class="row">
    <!-- Present Today -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Present Today</span>
                    <h3 class="m-0 font-weight-bold text-success">{{ number_format($presentToday) }}</h3>
                    <span class="badge badge-success mt-2"><i class="ti-arrow-up mr-1"></i> Checked In</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(34, 197, 94, 0.08); color: var(--primary-green);">
                    <i class="ti-check-box"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Absent Today -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Absent Today</span>
                    <h3 class="m-0 font-weight-bold text-danger">{{ number_format($absentToday) }}</h3>
                    <span class="badge badge-danger mt-2"><i class="ti-arrow-down mr-1"></i> Not Synced</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(239, 68, 68, 0.08); color: #ef4444;">
                    <i class="ti-close"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Late Arrivals -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Late Arrivals</span>
                    <h3 class="m-0 font-weight-bold text-warning">{{ number_format($lateArrivals) }}</h3>
                    <span class="badge badge-warning text-dark mt-2"><i class="ti-time mr-1"></i> Late Grace</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                    <i class="ti-alarm-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Fingerprint Enrolled Staff -->
    <div class="col-md-3 mb-4">
        <div class="card saas-card">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted font-weight-bold font-12 text-uppercase d-block mb-1">Fingerprint Enrolled</span>
                    <h3 class="m-0 font-weight-bold text-primary">{{ number_format($fingerprintEnrolledCount) }}</h3>
                    <span class="badge badge-primary mt-2"><i class="ti-hand-point-up mr-1"></i> Scanner Ready</span>
                </div>
                <div class="metric-icon-wrap" style="background-color: rgba(99, 102, 241, 0.08); color: #6366f1;">
                    <i class="ti-hand-stop"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. DEDICATED HARDWARE STATUS PANEL FOR FINGERPRINT SCANNERS -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card saas-card">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="ti-server text-primary mr-2"></i> Fingerprint Hardware Status Panel
                    </h6>
                    <small class="text-muted">Real-time status of biometric terminals, connectivity, and fingerprint enrollment coverage</small>
                </div>
                <div>
                    <span class="badge badge-success px-3 py-2 mr-2">
                        <span class="status-dot-pulse status-dot-online"></span> {{ $connectedFingerDevices }} Online
                    </span>
                    <span class="badge badge-secondary px-3 py-2 mr-2">
                        <span class="status-dot-pulse status-dot-offline"></span> {{ $offlineFingerDevices }} Offline
                    </span>
                    <a href="{{ route('finger_device.index') }}" class="btn btn-sm btn-saas btn-saas-secondary py-1 px-3">Manage Scanners</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row m-0 border-bottom bg-light py-3 px-4 align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0 border-right">
                        <span class="text-muted font-12 d-block">Registered Terminals</span>
                        <h4 class="m-0 font-weight-bold text-dark">{{ $totalFingerDevices }} <small class="text-muted font-12">Scanners</small></h4>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0 border-right">
                        <span class="text-muted font-12 d-block">Device Connectivity</span>
                        <div class="d-flex align-items-center mt-1">
                            @if($totalFingerDevices > 0 && $connectedFingerDevices > 0)
                                <span class="badge badge-success font-12 py-1 px-2"><i class="ti-check mr-1"></i> Active Link ({{ $connectedFingerDevices }}/{{ $totalFingerDevices }})</span>
                            @elseif($totalFingerDevices > 0)
                                <span class="badge badge-danger font-12 py-1 px-2"><i class="ti-alert mr-1"></i> All Devices Offline (0/{{ $totalFingerDevices }})</span>
                            @else
                                <span class="badge badge-light text-muted font-12 py-1 px-2"><i class="ti-info-alt mr-1"></i> No Terminals Configured</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted font-12 d-block">Fingerprint Enrollment Coverage</span>
                        <h4 class="m-0 font-weight-bold text-primary">{{ $fingerprintEnrolledCount }} / {{ $totalEmployees }} <small class="text-muted font-12">Staff Enrolled ({{ $totalEmployees > 0 ? round(($fingerprintEnrolledCount / $totalEmployees) * 100) : 0 }}%)</small></h4>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover device-table mb-0">
                        <thead class="bg-light font-11 text-uppercase text-muted">
                            <tr>
                                <th>Device Name</th>
                                <th>IP Address / Port</th>
                                <th>Serial Number</th>
                                <th>Location</th>
                                <th>Hardware Status</th>
                                <th>Enrollment Sync</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fingerDevices as $dev)
                            <tr>
                                <td class="font-weight-bold text-dark">
                                    <i class="ti-server mr-2 text-primary"></i> {{ $dev->name }}
                                </td>
                                <td>
                                    <code>{{ $dev->ip }}:4370</code>
                                </td>
                                <td class="text-muted">{{ $dev->serialNumber ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-light border text-dark">{{ $dev->location ?? 'Main Entrance' }}</span>
                                </td>
                                <td>
                                    @if($dev->is_online)
                                        <span class="badge badge-success device-badge">
                                            <span class="status-dot-pulse status-dot-online"></span> Online
                                        </span>
                                    @else
                                        <span class="badge badge-danger device-badge">
                                            <span class="status-dot-pulse status-dot-offline"></span> Offline
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info device-badge"><i class="ti-check mr-1"></i> {{ $fingerprintEnrolledCount }} Enrolled</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('finger_device.show', $dev->id) }}" class="btn btn-sm btn-light border py-1 px-2 text-dark font-12" title="Inspect Terminal">
                                        <i class="ti-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="ti-server font-20 d-block mb-1 text-secondary"></i>
                                    No fingerprint devices registered yet. <a href="{{ route('finger_device.create') }}" class="font-weight-bold text-primary ml-1">Add Biometric Terminal</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. ANALYTICS ROW -->
<div class="row">
    <!-- Attendance Overview Line Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-stats-up text-primary mr-2"></i> Attendance Overview (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Status Donut Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-pie-chart text-primary mr-2"></i> Biometric Setup Status</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted font-13"><span class="activity-indicator" style="background-color: var(--primary-blue);"></span> Enrolled Staff</span>
                        <span class="font-weight-bold text-dark">{{ $statusActive }} ({{ $totalEmployees > 0 ? round(($statusActive / $totalEmployees)*100) : 0 }}%)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted font-13"><span class="activity-indicator" style="background-color: #cbd5e1;"></span> Pending Setup</span>
                        <span class="font-weight-bold text-dark">{{ $statusPending }} ({{ $totalEmployees > 0 ? round(($statusPending / $totalEmployees)*100) : 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. LISTS ROW -->
<div class="row">
    <!-- Recent Employees List -->
    <div class="col-lg-6 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-user text-primary mr-2"></i> Recent Additions</h6>
                <a href="/employees" class="btn btn-sm btn-saas btn-saas-secondary py-1 px-3">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="border: none;">
                        <tbody>
                            @forelse($recentEmployees as $emp)
                            <tr>
                                <td style="border: none; padding: 12px 20px !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="employee-row-img mr-3">
                                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold text-dark font-14">{{ $emp->name }}</h6>
                                            <span class="text-muted font-11">#{{ $emp->id }} | {{ $emp->position }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-right text-muted font-12" style="border: none; padding: 12px 20px !important;">
                                    {{ \Carbon\Carbon::parse($emp->created_at)->format('M d, Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center py-4 text-muted" style="border: none;">No employees registered yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Log -->
    <div class="col-lg-6 mb-4">
        <div class="card saas-card h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark"><i class="ti-receipt text-primary mr-2"></i> Recent Security Activities</h6>
                <a href="/audit-logs" class="btn btn-sm btn-saas btn-saas-secondary py-1 px-3">View Logs</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="border: none;">
                        <tbody>
                            @forelse($recentActivities as $act)
                            <tr class="activity-row" data-category="{{ str_contains($act->action, 'LATE') ? 'late' : (str_contains($act->action, 'BREAK') ? 'break' : 'ontime') }}" style="border: none;">
                                <td style="border: none; padding: 12px 20px !important;">
                                    <span class="activity-indicator" style="background-color: {{ str_contains($act->action, 'LOGIN_FAILED') || str_contains($act->action, 'LOCKOUT') ? '#ef4444' : (str_contains($act->action, 'REGISTERED') ? 'var(--primary-green)' : 'var(--primary-blue)') }};"></span>
                                    <span class="text-dark font-weight-bold font-13 mr-1">{{ $act->actor_name }}</span>
                                    <span class="text-secondary font-12">{{ $act->description }}</span>
                                </td>
                                <td class="text-right text-muted font-12" style="border: none; padding: 12px 20px !important;">
                                    {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center py-4 text-muted" style="border: none;">No system logs available.</td>
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

@section('script')
<!-- ChartJS CDN for premium data visualization charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Line Chart: Attendance Overview
    const ctxAttendance = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctxAttendance, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartDays) !!},
            datasets: [
                {
                    label: 'Present Today',
                    data: {!! json_encode($chartPresent) !!},
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.04)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Absent Today',
                    data: {!! json_encode($chartAbsent) !!},
                    borderColor: '#116fb7',
                    backgroundColor: 'rgba(17, 111, 183, 0.04)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { family: 'Poppins', size: 12 }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 2. Donut Chart: Biometric Setup
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Enrolled', 'Pending'],
            datasets: [{
                data: [{{ $statusActive }}, {{ $statusPending }}],
                backgroundColor: ['#116fb7', '#cbd5e1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endsection